<?php
/**
* @file
* @brief    sigplus Image Gallery Plus boxplus Facebook extension
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for jQuery-based boxplus lightweight pop-up window engine with Facebook support.
* @see http://hunyadi.info.hu/projects/boxplus/
*/
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'lightbox.boxplus.php';

class SIGPlusBoxPlusFacebookEngine extends SIGPlusBoxPlusEngine {
	public function __construct($params = false) {
		parent::__construct($params);
	}

	protected function getDescriptionFunction() {
		return 'boxplusFacebookCaption';
	}

	public function addScripts($galleryid, SIGPlusGalleryParameters $params) {
		parent::addScripts($galleryid, $params);
		$this->addScript('/plugins/content/sigplus/engines/boxplus/popup/js/boxplus.facebook.js');

		// include XFBML scripts
		//$language = JFactory::getLanguage();
		//$languagecode = str_replace('-', '_', $language->getTag());
		//$document = JFactory::getDocument();
		//$document->addScript('http://connect.facebook.net/'.$languagecode.'/all.js#xfbml=1');
}
}