<?php
/*------------------------------------------------------------------------
# mod_sp_simple_map - Google Map module for Joomla by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2012 JoomShaper.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
//Parameters
$uniqid 				= $module->id;
$lat					= $params->get ('lat');
$lng					= $params->get ('lng');
$height					= $params->get ('height',300);
$map_type				= $params->get ('map_type','ROADMAP');
$zoom					= $params->get ('zoom',8);

$doc = JFactory::getDocument();
$doc->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false');//Add map api script
$doc->addStyledeclaration("#sp_simple_map_canvas {margin:0;padding:0;height:" . $height . "px}");//Add inline stlesheet
require(JModuleHelper::getLayoutPath('mod_sp_simple_map'));//Load layout