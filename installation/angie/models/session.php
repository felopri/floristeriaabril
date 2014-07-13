<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelSession extends AModel
{
	public function getStateVariables()
	{
		static $statevars = null;
		
		if (is_null($statevars))
		{
			$statevars = new stdClass();
			$vars = array('hostname', 'port', 'username', 'password', 'directory');
			
			foreach ($vars as $v)
			{
				$value = $this->getState($v, null, 'raw');
				
				$statevars->$v = $value;
				
				switch ($v)
				{
					case 'hostname':
						if (empty($statevars->$v))
						{
							$uri = AUri::getInstance();
							$statevars->$v = $uri->getHost();
						}
						break;
					
					case 'port':
						$statevars->$v = (int)$statevars->$v;
						if (($statevars->$v <= 0) || ($statevars->$v >= 65536))
						{
							$statevars->$v = 21;
						}
						break;
				}
			}
		}
		
		return $statevars;
	}
	
	public function fix()
	{
		// Connect to FTP
		$vars = $this->getStateVariables();
		$ftp = AFtp::getInstance($vars->hostname, $vars->port, array('type' => FTP_AUTOASCII), $vars->username, $vars->password);
		
		$root = rtrim($vars->directory,'/');
		
		// Can we find ourself?
		try
		{
			$ftp->chdir($root . '/installation');
			$ftp->read('defines.php', $buffer);
			if (!strlen($buffer))
			{
				throw new Exception('Cannot read defines.php');
			}
		}
		catch (Exception $exc)
		{
			throw new Exception(AText::_('SESSION_ERR_INVALIDDIRECTORY'));
		}
		
		// Let's try to chmod the directory
		$success = true;
		try
		{
			$ftp->chmod($root . '/installation/tmp', 0777);
		}
		catch (Exception $exc)
		{
			$success = false;
		}
		if ($success) return true;
		
		try
		{
			// That didn't work. Let's try creating an empty file in there.
			$ftp->write($root . '/installation/tmp/storagedata.dat', '');

			// ...and let's try giving it some 0777 permissions
			$ftp->chmod($root . '/installation/tmp/storagedata.dat', 0777);
		}
		catch (Exception $exc)
		{
			throw new Exception(AText::_('SESSION_ERR_CANNOTFIX'));
		}

		return true;
	}
}