<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Fancybox engine
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for Fancybox (jQuery-based).
* @see http://fancybox.net
*/
class SIGPlusFancyboxEngine extends SIGPlusLightboxEngine {
	public function getIdentifier() {
		return 'fancybox';
	}

	public function isInlineContentSupported() {
		return true;
	}

	protected function addCommonScripts() {
		$this->addJQuery();
		$document = JFactory::getDocument();
		$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/'.$this->getScriptFilename('jquery.easing'));  // duplicates are ignored
		parent::addCommonScripts();
	}
	
	public function addMetadataScripts() {
		$this->addCommonScripts();
	}
	
	public function getMetadataFunction() {
		return 'function (icon, image) { icon.fancybox({href:"#" + image.attr("id") + "_iptc"}); }';
	}

	public function addScripts($galleryid, SIGPlusGalleryParameters $params) {
		$this->addCommonScripts();
		$script = '__jQuery__("#'.$galleryid.' a[rel|=\'fancybox\']").each(function(index, el) { __jQuery__(el).fancybox('.$this->getCustomParameters($params).'); });';
		$this->addOnReadyScript($script);
	}
}