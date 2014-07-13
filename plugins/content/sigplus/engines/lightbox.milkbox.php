<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Milkbox lightbox engine
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2010 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for Milkbox (MooTools-based).
* @see http://reghellin.com/milkbox/
*/
class SIGPlusMilkboxEngine extends SIGPlusLightboxEngine {
	public function getIdentifier() {
		return 'milkbox';
	}

	protected function addCommonScripts() {
		$this->addMooTools();
		parent::addCommonScripts();
	}

	protected function addInitializationScripts() {
		$this->addCommonScripts();
		// suppress initialization script for Milkbox
	}

	public function addScripts($galleryid, SIGPlusGalleryParameters $params) {
		if ($params->linkage != 'inline') {
			throw new SIGPlusNotSupportedException();
		}

		$this->addInitializationScripts();
		$script = 'milkbox.setAutoPlay({'.
			'gallery:"'.$galleryid.'",'.
			'delay:'.($params->slideshow/1000).
		'});';
		$this->addOnReadyScript($script);
	}
}
