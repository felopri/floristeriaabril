<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

class ADocumentHtml extends ADocument
{
	public function render()
	{
		$template = AApplication::getInstance()->getTemplate();
		$templatePath = APATH_THEMES . '/' . $template;
		
		include $templatePath . '/index.php';
	}
}