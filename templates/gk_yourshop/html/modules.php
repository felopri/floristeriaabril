<?php

/**
 *
 * Framework module styles
 *
 * @version             1.0.0
 * @package             GK Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 * @license                
 */
 
// No direct access.
defined('_JEXEC') or die;

/**
 * gk_style
 */
 
function modChrome_gk_style($module, $params, $attribs) {
	if (!empty ($module->content)) {
		$badge = preg_match('/badge/', $params->get('moduleclass_sfx')) ? '<span class="badge">badge</span>' : '';
		echo '<div class="box' . $params->get('moduleclass_sfx') . '">';
		
		if($module->showtitle) {
			$part_one = explode(' ', $module->title);
			$part_one = $part_one[0];
			
			if(count(explode(' ', $module->title)) > 1) {
				$part_two = substr($module->title, strpos($module->title,' '));
			} else {
				$part_two = '';
			}
			
			echo '<h3 class="header"><span>'.$part_one.$part_two.$badge.'</span></h3>';
		}
		
		echo '<div class="content">' . $module->content . '</div>';
		echo '</div>';
	 }
}