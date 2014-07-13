<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_weblinks_category")) :

	
	
	
	function wright_joomla_weblinks_category($buffer) {
			$buffer = preg_replace('/<h2>/Ui', '<div class="page-header"> <h2>', $buffer);
			$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
			$buffer = preg_replace('/<table class="category">/Ui', '<table class="category table table-striped">', $buffer);
			$buffer = preg_replace('/<span class="item-title">/Ui', '<span class="item-title"><i class="icon-folder-close"> </i>', $buffer);
			$buffer = preg_replace('/<dl>/Ui', '<dl class="label label-info">', $buffer);
				return $buffer;
				
	}

endif;

ob_start("wright_joomla_weblinks_category");
require('components/com_weblinks/views/category/tmpl/default.php');
ob_end_flush();

