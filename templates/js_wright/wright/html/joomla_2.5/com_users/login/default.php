<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_users_login")) :

	function wright_joomla_users_login_li($matches) {
		return '<button class="btn btn-block" onclick="javascript:document.location=\'' . $matches[3] . '\'">' . $matches[5] . '</button>';
	}

	function wright_joomla_users_login($buffer) {
		
		$buffer = preg_replace('/ class="button"/Ui', 'class="button btn btn-success"', $buffer);
		$buffer = preg_replace('/class="validate"/Ui', 'class="validate btn btn-success"', $buffer);
		$buffer = preg_replace('/<ul>/Ui', '<div class="span3">', $buffer);
		$buffer = preg_replace('/<\/ul>/Ui', '</div>', $buffer);
		$buffer = preg_replace_callback('/<li>([^<]*)<a([^>]*)href="([^"]*)"([^>]*)>([^<]*)<\/a>([^<]*)<\/li>/Ui', "wright_joomla_users_login_li", $buffer);
		
		return $buffer;

	}

endif;

ob_start("wright_joomla_users_login");
require('components/com_users/views/login/tmpl/default.php');
ob_end_flush();

