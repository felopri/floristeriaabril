<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

function _angifw_autoloader($class_name)
{
	static $angifwPath = null;
	
	// Make sure the class has an A prefix
	if(strpos($class_name, 'A') !== 0)
	{
		return false;
	}
	
	// Set up the path to the framework
	if(is_null($angifwPath)) {
		$angifwPath = __DIR__;
	}
	
	// Remove the prefix
	$class = substr($class_name, 1);
	
	// Change from camel cased (e.g. ViewHtml) into a lowercase array (e.g. 'view','html')
	$class = preg_replace('/(\s)+/', '_', $class);
	$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
	$class = explode('_', $class);
	
	// First try finding in structured directory format (preferred)
	$path = $angifwPath . '/' . implode('/', $class) . '.php';
	if(@file_exists($path)) {
		include_once $path;
	}
	
	// Then try the duplicate last name structured directory format (not recommended)
	if(!class_exists($class_name, false)) {
		$lastPart = array_pop($class);
		array_push($class, $lastPart);
		$path = $angifwPath . '/' . implode('/', $class) . '/' . $lastPart . '.php';
		if(@file_exists($path)) {
			include_once $path;
		}
	}
}

// Register the autoloader
if( function_exists('spl_autoload_register') ) {
	// Joomla! is using its own autoloader function which has to be registered first...
	if(function_exists('__autoload')) spl_autoload_register('__autoload');
	// ...and then register ourselves.
	spl_autoload_register('_angifw_autoloader');
}  else {
	throw new Exception('Akeeba Next Generation Installer Framework requires the SPL extension to be loaded and activated', 500);
}
