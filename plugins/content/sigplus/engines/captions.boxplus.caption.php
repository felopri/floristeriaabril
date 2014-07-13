<?php
/**
* @file
* @brief    sigplus Image Gallery Plus boxplus mouse-over caption engine
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
* Support class for jQuery-based boxplus mouse-over caption engine.
* @see http://hunyadi.info.hu/projects/boxplus/
*/
class SIGPlusBoxPlusCaptionEngine extends SIGPlusCaptionsEngine {
	public function getIdentifier() {
		return 'boxplus.caption';
	}

	public function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/boxplus/caption/css/'.$this->getStyleFilename());

		// include style sheet in HTML head section to target Internet Explorer
		$this->addCustomTag('<!--[if lt IE 9]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/engines/boxplus/caption/css/boxplus.caption.ie8.css" type="text/css" /><![endif]-->');
		$this->addCustomTag('<!--[if lt IE 8]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/engines/boxplus/caption/css/boxplus.caption.ie7.css" type="text/css" /><![endif]-->');
	}

	public function addScripts($id, SIGPlusGalleryParameters $params) {
		$this->addJQuery();
		$this->addScript('/plugins/content/sigplus/engines/boxplus/caption/js/'.$this->getScriptFilename());
		$this->addScript('/plugins/content/sigplus/engines/boxplus/lang/'.$this->getScriptFilename('boxplus.lang'));

		$engineservices = SIGPlusEngineServices::instance();
		if ($params->metadata) {
			$metadatabox = $engineservices->getMetadataEngine($params->lightbox);
			if ($metadatabox) {
				$metadatabox->addStyles();
				$metadatafun = $metadatabox->getMetadataFunction();
			}
		}

		$language = JFactory::getLanguage();
		list($lang, $country) = explode('-', $language->getTag());
		$script =
			'__jQuery__("#'.$id.'").boxplusCaptionGallery(__jQuery__.extend('.$this->getCustomParameters($params).', { '.
				'position:'.($params->imagecaptions != 'overlay' ? '"figure"' : '"overlay"').', '.
				'caption:function (image) { var c = __jQuery__("#" + image.attr("id") + "_caption"); return c.size() ? c.html() : image.attr("alt"); }, '.
				'download:'.($params->download ? 'function (image) { var d = __jQuery__("#" + image.attr("id") + "_metadata a[rel=download]"); return d.size() ? d.attr("href") : false; }' : 'false').', '.
				'metadata:'.(isset($metadatafun) ? 'function (image) { var m = __jQuery__("#" + image.attr("id") + "_iptc"); return m.size() ? m : false; }' : 'false').', '.
				'dialog:'.(isset($metadatafun) ? $metadatafun : 'false').
			' })); '.
			'__jQuery__.boxplusLanguage("'.$lang.'", "'.$country.'");';
		$this->addOnReadyScript($script);
	}
}