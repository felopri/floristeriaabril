<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_users_remind")) :

	
	
	
	function wright_joomla_users_remind($buffer) {
		
			$buffer = preg_replace('/ class="validate"/Ui', 'class="validate btn btn-success" style=" margin-left:5px;"', $buffer);
			$buffer = preg_replace('/<p>/Ui', '<p class="form-actions" >', $buffer);
				return $buffer;
				
	}

endif;

ob_start("wright_joomla_users_remind");
require('components/com_users/views/remind/tmpl/default.php');
ob_end_flush();

