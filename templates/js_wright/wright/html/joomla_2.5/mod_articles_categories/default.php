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

if (!function_exists("wright_joomla_articles_categories")) :
	function wright_joomla_articles_categories_icon($matches) {
		return '<a' . $matches[1] . '>'.'<i class="icon-folder-close icons-left"></i>' . $matches[2] .'</a>';
	}

	function wright_joomla_articles_categories($buffer) {
		$buffer = preg_replace('/class="categories-module"/Ui', 'class="categories-module nav"', $buffer);	
		$buffer = preg_replace_callback('/<a([^>]*)>([^<]*)<\/a>/Ui', 'wright_joomla_articles_categories_icon', $buffer);
		return $buffer;
	}
endif;

ob_start("wright_joomla_articles_categories");
require('modules/mod_articles_categories/tmpl/default.php');
ob_end_flush();
?>