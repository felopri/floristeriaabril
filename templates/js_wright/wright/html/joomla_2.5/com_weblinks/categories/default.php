<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_weblinks_categories")) :

	
	
	
	function wright_joomla_weblinks_categories($buffer) {
			
			$buffer = preg_replace('/<a/Ui', '<i class="icon-folder-close"> </i><a', $buffer);
			
				return $buffer;
				
	}

endif;

ob_start("wright_joomla_weblinks_categories");
require('components/com_weblinks/views/categories/tmpl/default.php');
ob_end_flush();

