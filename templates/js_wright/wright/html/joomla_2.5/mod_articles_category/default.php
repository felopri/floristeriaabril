<?php
/**
 * @version		$Id: default.php 22355 2011-11-07 05:11:58Z github_bot $
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

if (!function_exists("wright_joomla_articles_category")) :
	function wright_joomla_articles_category_icon($matches) {
		return '<a' . $matches[1] . '>'.'<i class="icon-circle-arrow-right icons-left"></i>' . $matches[2] .'</a>';
	}

	function wright_joomla_articles_category($buffer) {
		$buffer = preg_replace('/class="category-module"/Ui', 'class="category-module nav"', $buffer);	
		$buffer = preg_replace_callback('/<a([^>]*)>([^<]*)<\/a>/Ui', 'wright_joomla_articles_category_icon', $buffer);
		return $buffer;
	}
endif;

ob_start("wright_joomla_articles_category");
require('modules/mod_articles_category/tmpl/default.php');
ob_end_flush();
?>