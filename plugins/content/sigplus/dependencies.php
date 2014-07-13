<?php
/**
* @file
* @brief    sigplus Image Gallery Plus dependencies
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* True if the server has GD library enabled with JPEG, PNG and GIF read support.
*/
function is_gd_supported() {
	static $supported = null;
	if (isset($supported)) {
		return $supported;
	}

	$supported = extension_loaded('gd');
	if (!$supported) {
		return false;
	}
	
	$supported = function_exists('gd_info');  // might fail in rare cases even if GD is available
	if (!$supported) {
		return false;
	}
	$gd = gd_info();
	$supported = isset($gd['GIF Read Support']) && $gd['GIF Read Support']
			&& isset($gd['GIF Create Support']) && $gd['GIF Create Support']
			&& (isset($gd['JPG Support']) && $gd['JPG Support'] || isset($gd['JPEG Support']) && $gd['JPEG Support'])
			&& isset($gd['PNG Support']) && $gd['PNG Support'];
	return $supported;
}

function is_imagick_supported() {
	static $supported = null;
	if (isset($supported)) {
		return $supported;
	}

	$supported = extension_loaded('imagick');
	if (!$supported) {
		return false;
	}

	$supported = class_exists('Imagick');
	return $supported;
}