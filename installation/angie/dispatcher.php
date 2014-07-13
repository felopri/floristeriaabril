<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieDispatcher extends ADispatcher
{
	public function onBeforeDispatch()
	{
		if(!$this->checkSession()) {
			return false;
		}
		if(!$this->passwordProtection()) {
			return false;
		}
		
		$view = $this->input->getCmd('view');
		$this->input->set('step', $view);
		
		return true;
	}
	
	private function checkSession()
	{
		if(!ASession::getInstance()->isStorageWorking())
		{
			$view = $this->input->getCmd('view', $this->defaultView);
			if (!in_array($view, array('session', 'ftpbrowser')))
			{
				AApplication::getInstance()->redirect('index.php?view=session');
			}
		}
		return true;
	}
	
	/**
	 * Check if the installer is password protected. If it is and the user has
	 * not yet entered a password forward him to the password entry page.
	 * 
	 * @return  boolean
	 */
	private function passwordProtection()
	{
		$filePath = APATH_INSTALLATION . '/password.php';
		if (file_exists($filePath))
		{
			include_once $filePath;
		}
		
		$view = $this->input->get('view', $this->defaultView);
		
		if (defined('AKEEBA_PASSHASH'))
		{
			$savedHash = ASession::getInstance()->get('angie.passhash', null);
			$parts = explode(':', AKEEBA_PASSHASH);
			$correctHash = $parts[0];
			$allowedViews = array('password', 'session', 'ftpbrowser');
			if (defined('AKEEBA_PASSHASH') && !in_array($view, $allowedViews) && ($savedHash != $correctHash))
			{
				AApplication::getInstance()->redirect('index.php?view=password');
				return true;
			}
		}
		elseif (!defined('AKEEBA_PASSHASH') && ($this->input->get('view', $this->defaultView) == 'password'))
		{
			return false;
		}
		
		return true;
	}
}