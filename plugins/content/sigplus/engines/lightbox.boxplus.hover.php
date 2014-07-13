<?php
/**
* @file
* @brief    sigplus Image Gallery Plus boxplus hover engine
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_PLUGINS.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'sigplus'.DIRECTORY_SEPARATOR.'params.php';

/**
* Support class for jQuery-based boxplus hover engine.
* @see http://hunyadi.info.hu/projects/boxplus/
*/
class SIGPlusBoxPlusHoverEngine extends SIGPlusLightboxEngine {
	public function getIdentifier() {
		return 'boxplus.hover';
	}

	public function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/boxplus/hover/css/'.$this->getStyleFilename());
	}

	public function addScripts($id, SIGPlusGalleryParameters $params) {
		$this->addJQuery();
		$this->addScript('/plugins/content/sigplus/engines/boxplus/hover/js/'.$this->getScriptFilename());
		$script = '__jQuery__("#'.$id.'").boxplusHoverGallery('.$this->getCustomParameters($params).');';
		$this->addOnReadyScript($script);
	}
}