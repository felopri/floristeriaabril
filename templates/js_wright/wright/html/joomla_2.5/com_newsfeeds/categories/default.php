<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_newsfeeds_categories")) :


	function wright_joomla_newsfeeds_categories($buffer) {
			$buffer = preg_replace('/<span class="item-title">/Ui', '<span class="item-title"><i class="icon-folder-open"></i>', $buffer);
			$buffer = preg_replace('/<dl class="newsfeed-count">/Ui', '<dl class=" newsfeed-count label label-info">', $buffer);
				return $buffer;
	}

endif;

ob_start("wright_joomla_newsfeeds_categories");
require('components/com_newsfeeds/views/categories/tmpl/default.php');
ob_end_flush();

