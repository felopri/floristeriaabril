<?php
/**
* @file
* @brief    sigplus Image Gallery Plus boxplus lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for jQuery-based boxplus lightweight pop-up window engine.
* @see http://hunyadi.info.hu/projects/boxplus/
*/
class SIGPlusBoxPlusEngine extends SIGPlusLightboxEngine {
	private $theme = 'lightsquare';

	public function getIdentifier() {
		return 'boxplus';
	}

	public function __construct($params = false) {
		parent::__construct($params);
		if (isset($params['theme'])) {
			$this->theme = $params['theme'];
		}
	}

	public function isInlineContentSupported() {
		return true;
	}

	public function isQuickNavigationSupported() {
		return true;
	}

	public function addMetadataScripts() {
		$this->addCommonScripts();
	}

	/**
	* A JavaScript function that shows metadata in a pop-up dialog.
	* The pop-up window must support inline content.
	* @see self::isInlineContentSupported
	*/
	public function getMetadataFunction() {
		return 'function (icon, image) { icon.click(function () { __jQuery__("<a />").attr("href", "#"+image.attr("id")+"_iptc").boxplusDialog(); }); }';
	}

	public function getLinkScript($id, $index = 0) {
		$index = $index > 0 ? $index - 1 : 0;
		return '__jQuery__("#'.$id.' a[rel^='.$this->getIdentifier().']:eq('.$index.')").click()';
	}

	public function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/'.$this->getStyleFilename());
		$language = JFactory::getLanguage();
		if ($language->isRTL()) {
			$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/boxplus.rtl.css');
		}
		$this->addCustomTag('<!--[if lt IE 9]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/boxplus.ie8.css" type="text/css" /><![endif]-->');
		$this->addCustomTag('<!--[if lt IE 8]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/boxplus.ie7.css" type="text/css" /><![endif]-->');
		$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/boxplus.'.$this->theme.'.css', 'text/css', null, array('title'=>'boxplus-'.$this->theme));
		$this->addCustomTag('<!--[if lt IE 9]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/engines/boxplus/popup/css/boxplus.'.$this->theme.'.ie8.css" type="text/css" title="boxplus-'.$this->theme.'" /><![endif]-->');
	}

	protected function addCommonScripts() {
		$this->addJQuery();
		$this->addScript('/plugins/content/sigplus/engines/boxplus/popup/js/'.$this->getScriptFilename());
		$this->addScript('/plugins/content/sigplus/engines/boxplus/lang/'.$this->getScriptFilename('boxplus.lang'));
	}

	public function addActivationScripts() {
		$this->addCommonScripts();
		$this->addScript('/plugins/content/sigplus/engines/boxplus/popup/js/activation.js');
	}

	protected function getDescriptionFunction() {
		return 'function (anchor) { var s = __jQuery__("#" + __jQuery__("img", anchor).attr("id") + "_summary"); return s.size() ? s.html() : anchor.attr("title"); }';
	}

	public function addScripts($galleryid, SIGPlusGalleryParameters $params) {
		$this->addCommonScripts();
		$language = JFactory::getLanguage();
		list($lang, $country) = explode('-', $language->getTag());
		$script =
			'__jQuery__("#'.$galleryid.'").boxplusGallery(__jQuery__.extend('.$this->getCustomParameters($params).', { '.
				'rtl:'.($language->isRTL() ? 'true' : 'false').', '.
				'theme: "'.$this->theme.'", '.
				'title: function (anchor) { var t = __jQuery__("#" + __jQuery__("img", anchor).attr("id") + "_caption"); return t.size() ? t.html() : __jQuery__("img", anchor).attr("alt"); }, '.
				'description: '.$this->getDescriptionFunction().', '.
				'slideshow: '.$params->slideshow.', '.
				'download: function (anchor) { var d = __jQuery__("#" + __jQuery__("img", anchor).attr("id") + "_metadata a[rel=download]"); return d.size() ? d.attr("href") : ""; }, '.
				'metadata: function (anchor) { var m = __jQuery__("#" + __jQuery__("img", anchor).attr("id") + "_iptc"); return m.size() ? m : ""; } '.
			' })); '.
			'__jQuery__.boxplusLanguage("'.$lang.'", "'.$country.'");';
		$this->addOnReadyScript($script);
	}
}
