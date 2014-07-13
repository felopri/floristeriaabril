<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_content_category")) :

	function replace_readmore_category($matches) {
		return '<p class="readmore">' . $matches[1] . '<a' . $matches[2] . ' class="btn btn-small btn-primary">';
		
	}
	
	function wright_joomla_content_category($buffer) {
		
		$buffer = preg_replace('/<dd class="category-name">/Ui', '<dd class="category-name"><i class="icon-folder-close"></i>', $buffer);
		$buffer = preg_replace('/<dd class="create">/Ui', '<dd class="create"><i class="icon-calendar"></i>', $buffer);
		$buffer = preg_replace('/<dd class="modified">/Ui', '<dd class="modified"><i class="icon-edit"></i>', $buffer);
		$buffer = preg_replace('/<dd class="published">/Ui', '<dd class="published"><i class="icon-table"></i>', $buffer);
		$buffer = preg_replace('/<dd class="createdby">/Ui', '<dd class="createdby"><i class="icon-user"></i>', $buffer);
		$buffer = preg_replace('/<dd class="hits">/Ui', '<dd class="hits"><i class="icon-signal"></i>', $buffer);
		$buffer = preg_replace('/<dd class="parent-category-name">/Ui', '<dd class="hits"><i class="icon-folder-close"></i>', $buffer);
	    $buffer = preg_replace('/<ul class="actions">/Ui', '<ul class="btn-group actions">', $buffer);
		$buffer = preg_replace('/<li class="([^-]+)-icon">/Ui', '<li class="btn $1-icon">', $buffer);
		
		$buffer = preg_replace_callback('/<p class="readmore">([^<]*)<a([^>]*)>/Ui', "replace_readmore", $buffer);	
		$buffer = preg_replace('/ class="button"/Ui', 'class="button btn " style="display:block; float:right; margin-left:5px;"', $buffer);
		
		$buffer = preg_replace('/<h2>/Ui', '<div class="page-header"> <h2>', $buffer);
		$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
		$buffer = preg_replace('/<div class="items-more">/Ui', '<div class="items-more well">', $buffer);
		$buffer = preg_replace('/<ol>/Ui', '<ol class="nav nav-list">', $buffer);
		
		$buffer = preg_replace('/<span class="item-title">/Ui', '<span class="item-title"><i class="icon-folder-open"></i>', $buffer);
		$buffer = preg_replace('/<dl>/Ui', '<dl class="label label-info">', $buffer);
		
		$buffer = preg_replace('/<span class="pagenav">/Ui', '<span class="pagenav disabled"><a>', $buffer);
		$buffer = preg_replace('/<\/span><\/li>/Ui', '</a></span></li>', $buffer);
		
		$buffer = preg_replace('/<table class="category">/Ui', '<table class="table table-striped table-hover">', $buffer);
		
		$buffer = preg_replace('/Display #/Ui', '<label> Display # </label>', $buffer);

			
		
		
		return $buffer;
	}

endif;

ob_start("wright_joomla_content_category");
require('components/com_content/views/category/tmpl/default.php');
ob_end_flush();

