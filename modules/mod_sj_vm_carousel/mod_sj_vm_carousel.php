<?php
/**
 * @package Sj Carousel for Virtuemart
 * @version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined( '_JEXEC' ) or die;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

require_once dirname( __FILE__ ).'/core/helper.php';
$currency = CurrencyDisplay::getInstance( );
$layout = $params->get('layout','default');
$cacheid = md5(serialize(array ($layout, $module->id)));
$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = 'SjCarouselHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = $cacheid;
$list = JModuleHelper::moduleCache($module, $params, $cacheparams);
require JModuleHelper::getLayoutPath($module->module, $params->get('layout', $layout));?>