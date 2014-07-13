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

if (!defined('SIGPLUS_VERSION_MODULE')) {
	define('SIGPLUS_VERSION_MODULE', '1.4.2');
}

if (!defined('SIGPLUS_DEBUG')) {
	// Triggers debug mode. Debug uses uncompressed version of scripts rather than the bandwidth-saving minified versions.
	define('SIGPLUS_DEBUG', false);
}
if (!defined('SIGPLUS_LOGGING')) {
	// Triggers logging mode. Verbose status messages are printed to the output.
	define('SIGPLUS_LOGGING', false);
}

// include the helper file
require_once 'helper.php';

try {
	// import dependencies
	if (SIGPlusModuleHelper::import()) {
		// get parameters from the module's configuration
		$configuration = new SIGPlusConfiguration();
		$configuration->setParameters($params);
		if (preg_match('#^https?://#', $configuration->services->imagesfolder)) {  // remote image sources
			$body = $configuration->services->imagesfolder;  // artificial body
			$configuration->services->imagesfolder = 'images';  // folder never used
		} else {
			$body = '';
		}

		$core = new SIGPlusCore($configuration);
		$galleryhtml = $core->getGalleryHtml($body);  // use images directly from folder specified as image folder
		$core->addGalleryEngines();
	} else {
		$galleryhtml = false;  // an error message already printed by another module instance
	}
} catch (Exception $e) {
	$app = JFactory::getApplication();
	$app->enqueueMessage( $e->getMessage(), 'error' );
	$galleryhtml = $e->getMessage();
}

// include the template for display
require JModuleHelper::getLayoutPath('mod_sigplus');