<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_users_profile")) :

	
	
	
	function wright_joomla_users_profile($buffer) {
		
			$buffer = preg_replace('/title="Cancel"/Ui', 'title="Cancel" class="button btn " style=" margin-left:5px;"', $buffer);
			$buffer = preg_replace('/class="validate"/Ui', 'class="validate btn " style=" margin-left:5px;"', $buffer);
			
			$buffer = preg_replace('/title="">/Ui', ' title=""> <i class="icon-folder-close"> </i>', $buffer);
	
				return $buffer;
				
	}

endif;

ob_start("wright_joomla_users_profile");
require('components/com_users/views/profile/tmpl/edit.php');
ob_end_flush();

