<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
if (!function_exists("wright_joomla_contact_contact")) :

	function wright_joomla_contact_contact_address($matches) {
		return '<span' . $matches[1] . '>' . $matches[2] . '<i class="icon-home"></i>';
	}

	function wright_joomla_contact_contact_email($matches) {
		return '<span' . $matches[1] . '>' . $matches[2] . '<i class="icon-envelope"></i>';
	}
	
	function wright_joomla_contact_contact_tel($matches) {
		return '<span' . $matches[1] . '>' . $matches[2] . '<i class="icon-phone"></i>';
	}
	
	function wright_joomla_contact_contact_fax($matches) {
		return '<span' . $matches[1] . '>' . $matches[2] . '<i class="icon-print"></i>';
	}
	function wright_joomla_contact_contact_mobile($matches) {
		return '<span' . $matches[1] . '>' . $matches[2] . '<i class="icon-phone-sign"></i>';
	}
	function wright_joomla_contact_contact($buffer) {
		$buffer = preg_replace('/<h2>/Ui', '<div class="page-header"> <h2>', $buffer);
		$buffer = preg_replace('/<\/h2>/Ui', '</h2> </div>', $buffer);
		$buffer = preg_replace('/<div class="contact-links">/Ui', '<div class="contact-links well ">', $buffer);
		$buffer = preg_replace('/<ul>/Ui', '<ul class="nav nav-list ">', $buffer);
		$buffer = preg_replace('/<ol>/Ui', '<ol class="nav nav-list ">', $buffer);
		$buffer = preg_replace('/<div class="contact-articles">/Ui', '<div class="contact-articles well">', $buffer);
		$buffer = preg_replace('/class="tabs"/Ui', 'class="tabs nav nav-tabs"', $buffer); 
	    $buffer = preg_replace('/<div class="panel">/Ui', '<div class="panel"><i class="icon-sort-down" style="float:right; margin-top:10px;position:relative; z-index:1;"> </i>', $buffer); 
     	//$buffer = preg_replace('/pane-toggler-down/Ui', 'pane-toggler-down icon-folder-close', $buffer);
     	$buffer = preg_replace('/class="button validate"/Ui', 'class="button btn validate"', $buffer);

		$buffer = preg_replace_callback('/<span([^>]*)>([^<]*)<img([^>]*)con_address.png([^>]*)>/Ui', 'wright_joomla_contact_contact_address', $buffer);
		$buffer = preg_replace_callback('/<span([^>]*)>([^<]*)<img([^>]*)emailButton.png([^>]*)>/Ui', 'wright_joomla_contact_contact_email', $buffer);
		$buffer = preg_replace_callback('/<span([^>]*)>([^<]*)<img([^>]*)con_tel.png([^>]*)>/Ui', 'wright_joomla_contact_contact_tel', $buffer);
		$buffer = preg_replace_callback('/<span([^>]*)>([^<]*)<img([^>]*)con_fax.png([^>]*)>/Ui', 'wright_joomla_contact_contact_fax', $buffer);
		$buffer = preg_replace_callback('/<span([^>]*)>([^<]*)<img([^>]*)con_mobile.png([^>]*)>/Ui', 'wright_joomla_contact_contact_mobile', $buffer);
		

		return $buffer;
	}

endif;

ob_start("wright_joomla_contact_contact");
require('components/com_contact/views/contact/tmpl/default.php');
ob_end_flush();

