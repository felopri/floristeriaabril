<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_newsfeeds_newsfeed")) :

	
	
	function wright_joomla_newsfeeds_newsfeed($buffer) {
		$buffer = preg_replace('/<h2 class=" redirect-rtl">/Ui', '<div class="page-header"> <h2>', $buffer);
		$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
		$buffer = preg_replace('/<ol>/Ui', '<ol class="unstyled">', $buffer);
		$buffer = preg_replace('/<\/li>/Ui', '</li> <hr>', $buffer);
				return $buffer;
	}

endif;

ob_start("wright_joomla_newsfeeds_newsfeed");
require('components/com_newsfeeds/views/newsfeed/tmpl/default.php');
ob_end_flush();

