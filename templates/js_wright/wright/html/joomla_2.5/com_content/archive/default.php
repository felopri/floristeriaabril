<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_content_archive")) :

	function replace_content_archive_readmore($matches) {
		return '<p class="readmore">' . $matches[1] . '<a' . $matches[2] . ' class="btn">';
	}
	
	function wright_joomla_content_archive($buffer) {
		
		$buffer = preg_replace('/<dd class="category-name">/Ui', '<dd class="category-name"><i class="icon-folder-close"></i>', $buffer);
		$buffer = preg_replace('/<dd class="create">/Ui', '<dd class="create"><i class="icon-calendar"></i>', $buffer);
		$buffer = preg_replace('/<dd class="modified">/Ui', '<dd class="modified"><i class="icon-edit"></i>', $buffer);
		$buffer = preg_replace('/<dd class="published">/Ui', '<dd class="published"><i class="icon-table"></i>', $buffer);
		$buffer = preg_replace('/<dd class="createdby">/Ui', '<dd class="createdby"><i class="icon-user"></i>', $buffer);
		$buffer = preg_replace('/<dd class="hits">/Ui', '<dd class="hits"><i class="icon-signal"></i>', $buffer);
		$buffer = preg_replace('/<dd class="parent-category-name">/Ui', '<dd class="hits"><i class="icon-folder-close"></i>', $buffer);
		$buffer = preg_replace_callback('/<p class="readmore">([^<]*)<a([^>]*)>/Ui', "replace_content_archive_readmore", $buffer);	
		$buffer = preg_replace('/<fieldset class="filters">/Ui', '<fieldset class="filters form-actions">', $buffer);
		$buffer = preg_replace('/ class="button"/Ui', 'class="button btn" style="display:block; float:right; margin-left:5px;"', $buffer);
		$buffer = preg_replace('/<h2>/Ui', '<div class="page-header"> <h2>', $buffer);
		$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
		return $buffer;
	}

endif;

ob_start("wright_joomla_content_archive");
require('components/com_content/views/archive/tmpl/default.php');
ob_end_flush();

