<?php
/**
 * @package Sj Module Tabs
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;

//defined('_YTOOLS') or include_once 'core/sjimport.php';

// set current module for working
// YTools::setModule($module);

// // import jQuery
// if (!defined('SMART_JQUERY') && (int)$params->get('include_jquery', '1')){
// 	YTools::script('jquery-1.5.min.js');
// 	define('SMART_JQUERY', 1);
// }
// if (!defined('SMART_NOCONFLICT')){
// 	YTools::script('jsmart.noconflict.js');
// 	define('SMART_NOCONFLICT', 1);
// }

// YTools::script('jsmart.moduletabs.js');
// YTools::stylesheet('moduletabs.css');

$position = $params->get('position', '');
$listmodules = array();
if ( !empty($position) ){
	$position_modules = JModuleHelper::getModules($position);
	$nb_module_allow  = $params->get('nb_module', 0);
	foreach ($position_modules as $i => $_module){
		if ($_module->id != $module->id){
			$listmodules[$_module->id] = &$position_modules[$i];
			if ($nb_module_allow==count($listmodules)){
				break;
			}
		} else {
			// do not recursive load
		}
	}
}

// load Renderer
$document	= &JFactory::getDocument();
$renderer	= $document->loadRenderer('module');

// if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
// 	header('Content-Type: text/xml');
// 	$sj_module_2load	= JRequest::getVar('sj_module_2load', null);
// 	$sj_module_id		= JRequest::getVar('sj_module_id', null);
// 	$sj_module			= JRequest::getVar('sj_module', null);
// 	if ($sj_module==$module->module && $sj_module_id==$module->id){
// 		// it's me. he he
// 		if (isset($listmodules[$sj_module_2load])){
// 			$_module = &$listmodules[$sj_module_2load];
// 			if ($_module->content==''){
// 				$_module->content = $renderer->render($_module, array());
// 			}
// 			die($_module->content);
// 		}
// 	}
// }

if ( count($listmodules) > 0 ){
	//$load_by_ajax = (int)$params->get('load_by_ajax', 0);
	foreach ($listmodules as $i => $_module) {
		if ($_module->content==''){
			$_module->content = $renderer->render($_module, array());
		}
// 		if ($load_by_ajax){
// 			// only render first module.
// 			break;
// 		}
	}
	include JModuleHelper::getLayoutPath($module->module);
	//require JModuleHelper::getLayoutPath('mod_sj_moduletabs', $params->get('layout', 'default'));
}?>


