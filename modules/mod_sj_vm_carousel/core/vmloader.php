<?php
/**
 * @package Sj Carousel for Virtuemart
 * @version 2.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */

defined('_JEXEC') or die;

if (!class_exists ('VmConfig')) {
    require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}
VmConfig::loadConfig ();

// Load the language file of com_virtuemart.
JFactory::getLanguage ()->load ('com_virtuemart');
if (!class_exists ('calculationHelper')) {
    require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'calculationh.php');
}
if (!class_exists ('CurrencyDisplay')) {
    require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'currencydisplay.php');
}
if (!class_exists ('VirtueMartModelVendor')) {
    require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'vendor.php');
}
if (!class_exists ('VmImage')) {
    require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'image.php');
}
if (!class_exists ('shopFunctionsF')) {
    require(JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'shopfunctionsf.php');
}
if (!class_exists ('calculationHelper')) {
    require(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'cart.php');
}
if (!class_exists ('VirtueMartModelProduct')) {
    JLoader::import ('product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
}
