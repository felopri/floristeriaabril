<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_newsfeeds_category")) :

	
	
	function wright_joomla_newsfeeds_category($buffer) {
			$buffer = preg_replace('/<h2>/Ui', '<div class="page-header"> <h2>', $buffer);
		$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
			$buffer = preg_replace('/<table class="category">/Ui', '<table class="table table-striped table-hover">', $buffer);
				return $buffer;
	}

endif;

ob_start("wright_joomla_newsfeeds_category");
require('components/com_newsfeeds/views/category/tmpl/default.php');
ob_end_flush();

