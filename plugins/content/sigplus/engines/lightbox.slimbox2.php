<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Slimbox2 lightbox engine
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for Slimbox2 (jQuery-based).
* @see http://www.digitalia.be/software/slimbox2
*/
class SIGPlusSlimbox2Engine extends SIGPlusLightboxEngine {
	public function getIdentifier() {
		return 'slimbox2';
	}

	/**
	* Adds style sheet references to the HTML @c head element.
	*/
	public function addStyles() {
		$document = JFactory::getDocument();
		$language = JFactory::getLanguage();
		if ($language->isRTL()) {
			$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'-rtl.css');
		} else {
			$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'.css');
		}
	}

	protected function addCommonScripts() {
		$this->addJQuery();
		parent::addCommonScripts();
	}

	public function addScripts($galleryid, SIGPlusGalleryParameters $params) {
		$this->addInitializationScripts();
		$script = '__jQuery__("#'.$galleryid.'").bindSlimbox('.$this->getCustomParameters($params).');';
		$this->addOnReadyScript($script);
	}
}