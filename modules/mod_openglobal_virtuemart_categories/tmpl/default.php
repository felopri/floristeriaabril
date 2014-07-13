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


require_once('printcategories.php');

echo '<div class="virtuemartcategories'.$params->get('moduleclass_sfx').'">';
printCategories($params, $categories, $class_sfx, $parentCategories);
echo '</div>';


