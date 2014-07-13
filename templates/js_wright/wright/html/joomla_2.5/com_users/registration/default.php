<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_users_registration")) :

	
	
	
	function wright_joomla_users_registration($buffer) {
		
			$buffer = preg_replace('/ class="validate"/Ui', 'class="validate btn btn-success" style=" margin-left:5px;"', $buffer);
			$buffer = preg_replace('/ title="cancel"/Ui', 'title="cancel" class=" btn btn-success" style=" margin-left:5px;"', $buffer);
			
			
				return $buffer;
				
	}

endif;

ob_start("wright_joomla_users_registration");
require('components/com_users/views/registration/tmpl/default.php');
ob_end_flush();

