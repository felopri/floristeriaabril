<?php // no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Category menu module
 *
 * @package VirtueMart
 * @subpackage Modules
 * @copyright Copyright (C) OpenGlobal E-commerce. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL V3, see LICENSE.php
 * @author OpenGlobal E-commerce
 *
 */

 function printCategories($params, $virtuemart_categories, $class_sfx, $parentCategories) {
        echo '<ul class="menu'.$class_sfx.'" >';
        foreach ($virtuemart_categories as $category) {
                $active_menu = '';
                $caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id);
                $images = $category->images;
                if (1 == $params->get('show_images')) {
                        $image = $images[0]->file_url_thumbnail;
                } else if (2 == $params->get('show_images')) {
                        $image = $images[0]->file_url;
                }
                $cattext = ($image ? '<img src="'.$image.'" alt="'.$category->category_name.'" />' : '').'<span>'.$category->category_name.'</span>';

                if (is_array($parentCategories)) {// Need this check because $parentCategories will be null if we're at category 0
                        if (in_array( $category->virtuemart_category_id, $parentCategories)) {
                                $active_menu = 'class="active"';
                        }
                }
                echo '<li '.$active_menu.'><div>'.JHTML::link($caturl, $cattext).'</div>';
                if ($category->childs) {
                        printCategories($params, $category->childs, $class_sfx, $parentCategories);
                }
                echo '</li>';
        }
        echo '</ul>';
}
