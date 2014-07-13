<?php
/**
 * @package Joomlashack Wright Framework
 * @copyright Joomlashack 2010-2012. All Rights Reserved.
 *
 * @description Wright template base, for configurations specific to the template.  It has to be inherited from within the template itself
 *
 * It would be inadvisable to alter the contents of anything inside of this folder
 *
 */
defined('_JEXEC') or die('You are not allowed to directly access this file');

class WrightTemplateBase {
	public $suffixes = false;  // checks if template allows stacked suffixes
	public $fullHeightSidebars = false;  // checks if this template uses full height sidebars

	public $specialClasses = Array();  // special stacked suffixes classes

	public $forcedSidebar = "";  // optional forced sidebar position, starts with nothing to be decided by fixed position (parameter) or auto setting
	public $forcedSidebarPositions = Array();  // positions that cause the forced sidebar, must be set here

	public $JDocumentHTML = null;  // if using forced sidebar has to be set with the local JDocumentHTML ($this from inside the template itself)

	private $_isThereALogo = null;  // local variable to know if there is a logo for the site

	public static function getInstance() {
		static $instance = null;
		if ($instance === null) {
			// prefers to use the inherited WrightTemplate class for customized settings on the template itself
			if (class_exists("WrightTemplate"))
				$instance = new WrightTemplate();
			else
				$intance = new WrightTemplateBase();
		}

		return $instance;
	}

	// function to determine if sidebar has to be forced (sets the forcedSidebar property)
	// $forcedSidebar refers to the value of the parameter read, to know what's the sidebar to be forced, or empty for automatic config
	function defineForcedSidebar($forcedSidebar) {
		$isSidebarForced = false;

		// checks if any of the positions has modules on it
		foreach($this->forcedSidebarPositions as $pos) {
			if ($pos == 'logo') {
				$isSidebarForced = $this->isThereALogo();
			}
			else
				if ($this->JDocumentHTML->countModules($pos))
					$isSidebarForced = true;
		}

		// checks, deppending on the logoPosition parameter, which is the sidebar to be forced
		if ($isSidebarForced) {
			if ($forcedSidebar == 'sidebar1' ||   // if sidebar1 is defined by config
			  ($forcedSidebar == '' && $this->JDocumentHTML->countModules('sidebar1') ||  // if config is auto and there's modules on sidebar1 already
			  ($forcedSidebar == '' && !$this->JDocumentHTML->countModules('sidebar1') && !$this->JDocumentHTML->countModules('sidebar2'))))  // if config is auto and there are no modules on any sidebar
			{
			  	$this->forcedSidebar = 'sidebar1';
			}
			if ($forcedSidebar == 'sidebar2' ||   // if sidebar2 is defined by config
				($forcedSidebar == '' && !$this->JDocumentHTML->countModules('sidebar1') && $this->JDocumentHTML->countModules('sidebar2')))  // if there are modules on sidebar2 and not on sidebar1
			{
				$this->forcedSidebar = 'sidebar2';
			}
		}
	}

	// check if there is a logo (based on Wright's logo.php file or on the local variable if set)
	function isThereALogo() {
		if (!is_null($this->_isThereALogo)) {
			return $this->_isThereALogo;
		}

		require_once(dirname(__FILE__) . '/../adapters/joomla/logo.php');
		$this->_isThereALogo = WrightAdapterJoomlaLogo::isThereALogo();
		return $this->_isThereALogo;
	}
}
