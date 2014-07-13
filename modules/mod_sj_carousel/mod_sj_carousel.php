<?php
/**
 * @package Sj Carousel 
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;

// Include the helper functions only once
//require_once __DIR__ . '/helper.php';
require_once dirname(__FILE__).'/core/helper.php';

$idbase = $params->get('catid');
$cacheid = md5(serialize(array ($idbase, $module->module)));
$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = 'SjCarouselHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = $cacheid;
$list = JModuleHelper::moduleCache($module, $params, $cacheparams);
//var_dump($list); die("abc");
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_sj_carousel', $params->get('layout', 'default'));?>