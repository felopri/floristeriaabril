<?php
/**
 * @version		$Id: jquery_update.php 
 * @copyright	Copyright (C) 2005 - 2013 RuposTel.com All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgSystemJQuery_update extends JPlugin
{
    public function onAfterRoute() {
		if (!self::check()) return; 
		$doc =& JFactory::getDocument();
		$doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'); 
		$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js'); 
		$doc->addScript('//code.jquery.com/jquery-migrate-1.2.1.js'); 
		$doc->addScript('/components/com_virtuemart/assets/js/jquery.noConflict.js'); 
		$doc->addScriptDeclaration('
jQuery.migrateMute = true; 
'); 
		JFactory::getApplication ()->set ('jquery', true);
	}
	
	
		private function check()
	{
	  	$app = JFactory::getApplication();
		if ($app->getName() != 'site') {
			return false;
		}
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'helpers'.DS.'opctracking.php')) return false;
		
		$format = JRequest::getVar('format', 'html'); 
		if ($format != 'html') return false;

		$doc = JFactory::getDocument(); 
		$class = strtoupper(get_class($doc)); 
		if ($class != 'JDOCUMENTHTML') return false; 
		
		require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'helpers'.DS.'opctracking.php'); 
		
		return true; 

	}

	
	
	
	
}
