<?php
/**
 * @version		$Id: default.php 22355 2011-11-07 05:11:58Z github_bot $
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

if (!function_exists("wright_joomla_mod_feed")) :
	
function wright_joomla_mod_feed($buffer) {
	
	$buffer = preg_replace('/<ul class="newsfeed">/Ui', '<ul class="newsfeed nobullet">', $buffer);
	$buffer = preg_replace('/<li class="newsfeed-item">/Ui', '<li class="newsfeed-item"><i class="icon-external-link icons-left"></i>', $buffer);
	return $buffer;
				
}
	

endif;

ob_start("wright_joomla_mod_feed");
require('modules/mod_feed/tmpl/default.php');
ob_end_flush();
?>