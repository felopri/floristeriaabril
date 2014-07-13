<?php
/**
* @file
* @brief    sigplus Image Gallery Plus module for Joomla
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2010 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus module for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class SIGPlusModuleException extends Exception {
	/** The text of a critical error message. */
	public function __construct($errormsg) {
		$this->message = '<p><strong>[sigplus] Critical error:</strong> '.$errormsg.'</p>';
	}
}

class SIGPlusModuleHelper {
	private static $imported = null;

	/**
	* Imports module dependencies.
	*/
	public static function import() {
		if (!is_null(self::$imported)) {
			return self::$imported;
		}

		$import = JPATH_PLUGINS.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'sigplus'.DIRECTORY_SEPARATOR.'core.php';
		if (!is_file($import)) {
			$errormsg = '<kbd>mod_sigplus</kbd> (sigplus module) requires <kbd>plg_sigplus</kbd> (sigplus plug-in) to be installed. The latest version of <kbd>plg_sigplus</kbd> is available from <a href="http://joomlacode.org/gf/project/sigplus/frs/">JoomlaCode</a>.';
			self::$imported = false;
			throw new SIGPlusModuleException($errormsg);
		}
		require_once $import;

		if (!defined('SIGPLUS_VERSION') || !defined('SIGPLUS_VERSION_MODULE') || SIGPLUS_VERSION !== SIGPLUS_VERSION_MODULE) {
			$errormsg = '<kbd>mod_sigplus</kbd> (sigplus module) requires a matching version of <kbd>plg_sigplus</kbd> (sigplus plug-in) to be installed. Currently you have <kbd>mod_sigplus</kbd> version '.SIGPLUS_VERSION_MODULE.' but your version of <kbd>plg_sigplus</kbd> is '.SIGPLUS_VERSION.'. The latest version of <kbd>plg_sigplus</kbd> and <kbd>mod_sigplus</kbd> is available from <a href="http://joomlacode.org/gf/project/sigplus/frs/">JoomlaCode</a>.';
			self::$imported = false;
			throw new SIGPlusModuleException($errormsg);
		}

		self::$imported = true;
		return true;
	}
}
