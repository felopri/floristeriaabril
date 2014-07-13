<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewMain extends AView
{
	public function onBeforeMain()
	{
		if ($this->input->get('layout') != 'init')
		{
			return true;
		}
		
		// Assign the results of the various checks
		$this->reqSettings = $this->getModel()->getRequired();
		$this->reqMet = $this->getModel()->isRequiredMet();
		$this->recommendedSettings = $this->getModel()->getRecommended();
		$this->extraInfo = $this->getModel()->getExtraInfo();
		$this->joomlaVersion = ASession::getInstance()->get('jversion');
		
		// Am I restoring to a different site?
		$this->restoringToDifferentHost = false;
		if (isset($this->extraInfo['host']))
		{
			$uri = AUri::getInstance();
			$this->restoringToDifferentHost = $this->extraInfo['host']['current'] != $uri->getHost();
		}
		
		// Am I restoring to a different PHP version?
		$this->restoringToDifferentPHP = false;
		if (isset($this->extraInfo['php_version']))
		{
			$parts = explode('.', $this->extraInfo['php_version']['current']);
			$sourceVersion = $parts[0] . '.' . $parts[1];
			$parts = explode('.', PHP_VERSION);
			$targetVersion = $parts[0] . '.' . $parts[1];
			
			$this->restoringToDifferentPHP = $sourceVersion != $targetVersion;
		}

		// If I am restoring to a different host blank out the database
		// connection information to prevent unpleasant situations, like a user
		// "accidentally" overwriting his original site's database...
		if ($this->restoringToDifferentHost && !ASession::getInstance()->get('main.resetdbinfo', false))
		{
			$this->getModel()->resetDatabaseConnectionInformation();
		}
		
		return true;
	}
}