<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_content_categories_default_items")) :

	
	
	function wright_joomla_content_categories_default_items($buffer) {
		
		$buffer = preg_replace('/<dl>/Ui', '<dl class="label label-info">', $buffer);
		
		return $buffer;
	}

endif;

ob_start("wright_joomla_content_categories_default_items");
require('components/com_content/views/categories/tmpl/default_items.php');
ob_end_flush();

