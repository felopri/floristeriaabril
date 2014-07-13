<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 * Category menu module
 *
 * @package VirtueMart
 * @subpackage Modules
 * @copyright Copyright (C) OpenGlobal. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author OpenGlobal
 *
 */
if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
$config= VmConfig::loadConfig();
if (!class_exists( 'VirtueMartModelVendor' )) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
//if (!class_exists( 'VmImage' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'image.php');
//if (!class_exists( 'shopFunctionsF' )) require(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctionsf.php');
if(!class_exists('TableMedias')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'medias.php');
if(!class_exists('TableCategories')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'categories.php');
if (!class_exists( 'VirtueMartModelCategory' )) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'category.php');

function getRecursiveCategories($vendor_id, $category_id) {
        $categoryModel = VmModel::getModel('Category');
        $categories = $categoryModel->getChildCategoryList($vendor_id, $category_id);
        $categoryModel->addImages($categories);

        foreach ($categories as $category) {
                $category->childs = getRecursiveCategories($vendor_id, $category->virtuemart_category_id);
        }

        return $categories;
}
?>