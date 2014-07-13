<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerPassword extends AController
{
	public function unlock()
	{
		$parts = explode(':', AKEEBA_PASSHASH);
		$password = $this->input->get('password', '', 'raw');
		$passHash = md5($password . $parts[1]);
		
		ASession::getInstance()->set('angie.passhash', $passHash);
		ASession::getInstance()->saveData();
		
		if($passHash == $parts[0])
		{
			$this->setRedirect('index.php?view=main');
		}
		else
		{
			$msg = AText::_('PASSWORD_ERR_INVALIDPASSWORD');
			$this->setRedirect('index.php?view=password', $msg, 'error');
		}
	}
}