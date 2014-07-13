<?php
/**
* @file
* @brief    sigplus Image Gallery Plus javascript engine service classes
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Service class for JavaScript code management.
*/
class SIGPlusEngineServices {
	/** True if the engine uses the MooTools library. */
	private $mootools = false;
	/** True if the engine uses the jQuery library. */
	private $jquery = false;
	/** Custom tags added to page header. */
	private $customtags = array();
	/** List of registered lightbox engine instances. */
	private $lightboxengines = array();
	/** List of registered slider engine instances. */
	private $sliderengines = array();
	/** List of caption engine instances. */
	private $captionsengines = array();
	/** JavaScript snippets to run on HTML DOM ready event. */
	private $scripts = array();
	private $scriptblocks = array();
	/** True if the deferred attribute is to be added to scripts. */
	private $deferred = false;
	/** URL of external content that replaces an element in the HTML DOM. */
	private $ajaxurl = false;
	/** Identifier of HTML DOM element that is replaced by external content. */
	private $ajaxid = false;

	/** Content delivery network to use on a site that is publicly available (i.e. not an intranet network), 'none' or 'local'. */
	public $ajaxapi = 'default';
	/** Whether to use uncompressed versions of scripts. */
	public $debug = null;  // true = enabled, false = disabled, null = not set (disabled)
	/** Singleton instance. */
	private static $inst = false;

	public static function instance() {
		if (self::$inst === false) {
			self::$inst = new SIGPlusEngineServices();
		}
		return self::$inst;
	}

	/**
	* Adds MooTools support.
	*/
	public function addMooTools() {
		if ($this->mootools) {
			return;
		}
		switch ($this->ajaxapi) {
			case 'none':
				break;
			default:
				if ($this->debug) {
					JHTML::_('behavior.mootools', true);
				} else {
					JHTML::_('behavior.mootools');
				}
		}
		$this->mootools = true;
	}

	/**
	* Adds jQuery support.
	*/
	public function addJQuery() {
		if ($this->jquery) {
			return;
		}

		if ($this->ajaxapi !== false && $this->ajaxapi != 'none') {  // not loading jQuery is recommended when you have another extension (e.g. a system plug-in) that loads it
			$document = JFactory::getDocument();
			if (version_compare(JVERSION, '3.0') >= 0) {  // jQuery is native to Joomla 3.0 and later
				JHTML::_('jquery.framework');
			} else {
				// add support for HTTPS
				$uri = JFactory::getURI();
				$scheme = $uri->isSSL() ? 'https://' : 'http://';

				switch ($this->ajaxapi) {
					case 'none':  // do not load jQuery, recommended when you have another extension (e.g. a system plug-in) that loads jQuery
						break;
					case 'local':  // use local copy of jQuery, recommended only for intranet sites
						$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.js');
						$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						break;
					case 'cdn-google':  // use jQuery from Google AJAX library
						if ($this->debug) {
							$document->addScript($scheme.'ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.js');
						} else {
							$document->addScript($scheme.'ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
						}
						$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						break;
					case 'cdn-microsoft':  // use jQuery from Microsoft Ajax Content Delivery Network
						if ($this->debug) {
							$document->addScript($scheme.'ajax.microsoft.com/ajax/jQuery/jquery-1.8.3.js');
						} else {
							$document->addScript($scheme.'ajax.microsoft.com/ajax/jQuery/jquery-1.8.3.min.js');
						}
						$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						break;
					case 'cdn':
					case 'cdn-jquery':
						if ($this->debug) {
							$document->addScript('http://code.jquery.com/jquery-1.8.3.js');
						} else {
							$document->addScript('http://code.jquery.com/jquery-1.8.3.min.js');
						}
						$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						break;
					default:  // use jQuery from Google AJAX library with on-demand inclusion
						$document->addScript($scheme.'www.google.com/jsapi');
						if ($this->debug) {
							$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.include.debug.js');
							$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						} else {
							$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.include.min.js');
							$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/jquery.noconflict.js');
						}
				}
			}
			$document->addScriptDeclaration('if (typeof(__jQuery__) == "undefined") { var __jQuery__ = jQuery; }');
		}
		$this->jquery = true;
	}

	/**
	* Fetch an engine from the engine registry, adding a new instance if necessary.
	* @param string $enginetype Engine type (e.g. lightbox, slider or captions).
	* @param array $engines The associative array that maps engine names to instances.
	* @param $engine A unique name used to instantiate the engine.
	*/
	private function getEngine($enginetype, array $engines, $engine) {
		if (is_null($engine)) {  // use first registered engine, if any
			if (empty($engines)) {
				return false;
			} else {
				return reset($engines);  // returns first registered engine
			}
		} elseif ($engine === false) {
			return false;
		} else {
			if (!isset($engines[$engine])) {
				$engines[$engine] = SIGPlusEngine::create($enginetype, $engine);
			}
			return $engines[$engine];
		}
	}

	public function getLightboxEngine($lightboxengine) {
		return $this->getEngine('lightbox', $this->lightboxengines, $lightboxengine);
	}

	public function getSliderEngine($sliderengine) {
		return $this->getEngine('slider', $this->sliderengines, $sliderengine);
	}

	public function getCaptionsEngine($captionsengine) {
		return $this->getEngine('captions', $this->captionsengines, $captionsengine);
	}

	public function getMetadataEngine($lightboxengine) {
		$engine = $this->getLightboxEngine($lightboxengine);
		if ($engine !== false && $engine->isInlineContentSupported()) {
			return $engine;
		} else {
			return $this->getLightboxEngine('boxplus');
		}
	}

	public function addCustomTag($tag) {
		if (!in_array($tag, $this->customtags)) {
			$document = JFactory::getDocument();
			if ($document->getType() == 'html') {  // custom tags are supported by HTML document type only
				$document->addCustomTag($tag);
			}
			$this->customtags[] = $tag;
		}
	}

	public function addStyleDefaultLanguage() {
		$this->addCustomTag('<meta http-equiv="Content-Style-Type" content="text/css" />');
	}

	/**
	* Returns the minified version of a style or script file if available.
	*/
	public function getMinifiedFile($relpath) {
		$basename = pathinfo($relpath, PATHINFO_BASENAME);  // e.g. "sigplus.css"
		$folder = pathinfo($relpath, PATHINFO_DIRNAME);  // e.g. "/plugins/content/sigplus/css"
		$p = strrpos($basename, '.');
		if ($p !== false) {
			$filename = substr($basename, 0, $p);  // drop extension from filename
			$extension = substr($basename, $p);
		} else {
			$filename = $basename;
			$extension = '';
		}

		$path = JPATH_ROOT.str_replace('/', DIRECTORY_SEPARATOR, $relpath);
		$dir = pathinfo($path, PATHINFO_DIRNAME);
		$original = $dir.DIRECTORY_SEPARATOR.$basename;
		$minified = $dir.DIRECTORY_SEPARATOR.$filename.'.min'.$extension;
		if (!$this->debug && file_exists($original) && file_exists($minified) && filemtime($minified) >= filemtime($original)) {
			return JURI::base(true).$folder.'/'.$filename.'.min'.$extension;
		} else {
			return JURI::base(true).$relpath;
		}
	}

	public function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet($this->getMinifiedFile('/plugins/content/sigplus/css/sigplus.css'));
		$this->addCustomTag('<!--[if lt IE 8]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/css/sigplus.ie7.css" type="text/css" /><![endif]-->');
		$this->addCustomTag('<!--[if lt IE 9]><link rel="stylesheet" href="'.JURI::base(true).'/plugins/content/sigplus/css/sigplus.ie8.css" type="text/css" /><![endif]-->');
	}

	public function addScript($path) {
		if ($this->deferred) {
			$this->addCustomTag('<script type="text/javascript" defer="defer" src="'.JURI::base(true).$path.'"></script>');
		} else {
			$document = JFactory::getDocument();
			$document->addScript(JURI::base(true).$path);
		}
	}

	/**
	* Appends a JavaScript snippet to the code to be run on the HTML DOM ready event.
	*/
	public function addOnReadyScript($script) {
		$this->scripts[] = $script;
	}

	/**
	* Causes onready event scripts to execute only when an AJAX request has successfully terminated.
	* @param string $url The URL to use for the HTTP GET request.
	* @param $id The identifier of the HTML element that the fetched HTML content replaces.
	*/
	public function setAjaxOnReady($url, $id) {
		$this->ajaxurl = $url;
		$this->ajaxid = $id;
	}

	/**
	* Adds all HTML DOM ready event scripts to the page as a @c script declaration.
	*/
	public function addOnReadyScripts() {
		if (!empty($this->scripts)) {
			$script = implode("\n", $this->scripts);
			if ($this->ajaxurl !== false && $this->ajaxid !== false) {
				$this->addJQuery();
				// register client-side script to replace placeholder with external content
				$script =
					'__jQuery__.get("'.$this->ajaxurl.'", function(ajaxdata) {'."\n".
					'__jQuery__("#'.$this->ajaxid.'").replaceWith(ajaxdata);'."\n".
					$script."\n".
					'});';
				$this->ajaxurl = false;
				$this->ajaxid = false;
			}
			$this->scriptblocks[] = $script;
		}
		$this->scripts = array();  // clear scripts added to document
	}

	public function addOnReadyEvent() {
		if (!empty($this->scripts)) {
			$this->addOnReadyScripts();
		}
		if (!empty($this->scriptblocks)) {
			if ($this->jquery) {
				$onready = '__jQuery__(document).ready(';
			} else {
				$onready = 'window.addEvent("domready",';
			}
			$onready .= 'function() {'."\n".implode("\n", $this->scriptblocks)."\n".'});'."\n";
			$document = JFactory::getDocument();
			if ($this->deferred) {
				if ($document->getType() == 'html') {  // custom tags are supported by HTML document type only
					$document->addCustomTag('<script type="text/javascript" defer="defer">'.$onready.'</script>');
				}
			} else {
				$document->addScriptDeclaration($onready);
			}
			$this->scriptblocks = array();
		}
	}
}

/**
* Base class for engines based on a javascript framework.
*/
class SIGPlusEngine {
	public function getIdentifier() {
		return 'default';
	}

	public function getCustomParameters($params) {
		if ($params !== false && !empty($params)) {
			return json_encode($params);
		} else {
			return '{}';
		}
	}

	/**
	* Filename for CSS stylesheet to load.
	*/
	protected function getStyleFilename($identifier = false) {
		if (!$identifier) {
			$identifier = $this->getIdentifier();
		}
		$instance = SIGPlusEngineServices::instance();
		if ($instance->debug) {
			return $identifier.'.css';
		} else {
			return $identifier.'.min.css';
		}
	}

	/**
	* Filename for javascript code to load.
	*/
	protected function getScriptFilename($identifier = false) {
		if (!$identifier) {
			$identifier = $this->getIdentifier();
		}
		$instance = SIGPlusEngineServices::instance();
		if ($instance->debug) {
			return $identifier.'.js';
		} else {
			return $identifier.'.min.js';
		}
	}

	/**
	* Adds a script reference to the page.
	*/
	protected function addScript($path) {
		$instance = SIGPlusEngineServices::instance();
		$instance->addScript($path);
	}

	/**
	* Adds MooTools support.
	*/
	protected function addMooTools() {
		$instance = SIGPlusEngineServices::instance();
		$instance->addMooTools();
	}

	/**
	* Adds jQuery support.
	*/
	protected function addJQuery() {
		$instance = SIGPlusEngineServices::instance();
		$instance->addJQuery();
	}

	public function addCustomTag($tag) {
		$instance = SIGPlusEngineServices::instance();
		$instance->addCustomTag($tag);
	}

	/**
	* Adds style sheet references to the HTML @c head element.
	*/
	public function addStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true).'/plugins/content/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'.css');
	}

	/**
	* Appends a JavaScript snippet to the code to be run on the HTML DOM ready event.
	*/
	protected function addOnReadyScript($script) {
		$instance = SIGPlusEngineServices::instance();
		$instance->addOnReadyScript($script);
	}

	/**
	* Factory method for engine instantiation.
	*/
	public static function create($enginetype, $engine) {
		// check for parameters passed to engine
		$pos = strpos($engine, '/');
		if ($pos !== false) {
			$params = array('theme'=>substr($engine, $pos+1));
			$engine = substr($engine, 0, $pos);
		} else {
			$params = array();
		}

		$engineclassname = str_replace('.', '', $engine);
		if (!ctype_alnum($engineclassname)) {  // simple name required
			return false;
		}

		$engineclass = 'SIGPlus'.$engineclassname.'Engine';
		$enginedir = dirname(__FILE__).DIRECTORY_SEPARATOR.'engines';
		if (is_file($enginefile = $enginedir.DIRECTORY_SEPARATOR.$enginetype.'.'.$engine.'.php') || is_file($enginefile = $enginedir.DIRECTORY_SEPARATOR.$engine.'.php')) {
			require_once $enginefile;
		}
		if (class_exists($engineclass)) {
			return new $engineclass($params);
		} else {
			return false;  // inclusion failure
		}
	}
}

/**
* Base class for pop-up window (lightbox-clone) support.
*/
class SIGPlusLightboxEngine extends SIGPlusEngine {
	/**
	* A default constructor that ignores all optional arguments.
	*/
	public function __construct($params = false) { }
	
	public function getCustomParameters($params) {
		return parent::getCustomParameters($params->lightbox_params);
	}

	/**
	* Whether the pop-up window supports displaying arbitrary HTML content.
	* @return True if the pop-up window is not restricted to displaying images only.
	*/
	public function isInlineContentSupported() {
		return false;
	}

	/**
	* Whether the pop-up window supports fast navigation by displaying a ribbon of thumbnails
	* the user can click and jump to a particular image.
	*/
	public function isQuickNavigationSupported() {
		return false;
	}

	/**
	* JavaScript code subscribed to an anchor click event to programmatically activate a gallery.
	* The code must not contain double quotes (").
	* @param string $id The identifier of the gallery to activate.
	* @param int $index The index of the image within the gallery to show.
	*/
	public function getLinkScript($id, $index = 0) {
		return false;
	}

	/**
	* Adds script references that are common to normal and fully customized gallery initialization.
	* @remark When overriding this method, the base method should normally be called.
	*/
	protected function addCommonScripts() {
		$this->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/'.$this->getScriptFilename());
	}

	/**
	* Adds script references to the HTML @c head element to bind the click event to lightbox pop-up activation.
	*/
	protected function addInitializationScripts() {
		$this->addCommonScripts();
		$this->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/initialization.js');
	}

	/**
	* Adds script references to the HTML @c head element to support fully customized gallery initialization.
	* @remark When overriding this method, the base method should normally NOT be called.
	*/
	public function addActivationScripts() {
		$this->addCommonScripts();
		$this->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/activation.js');
	}

	/**
	* The value to use in the @c rel attribute of anchor elements to bind the lightbox-clone.
	* @param gallery The unique identifier for the image gallery. Images in the same gallery are grouped together.
	* @return A valid value for the @c rel attribute of an @c a element.
	*/
	public function getLinkAttribute($gallery = false) {
		if ($gallery !== false) {
			return $this->getIdentifier().'-'.$gallery;
		} else {
			return $this->getIdentifier();
		}
	}
}

/**
* Base class for image slider support.
*/
class SIGPlusSliderEngine extends SIGPlusEngine {
	public function getCustomParameters($params) {
		return parent::getCustomParameters($params->slider_params);
	}

	/**
	* Adds script references to the HTML @c head element to support an image slider.
	* @param string $id The HTML identifier of the gallery.
	* @param $params Gallery parameters.
	*/
	public function addScripts($id, SIGPlusGalleryParameters $params) {
		$this->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/'.$this->getScriptFilename());
	}

	/**
	* The CSS selector to add rules to in order to apply styling such as margin, border and padding to images.
	*/
	public function getImageStyleSelector() {
		return false;  // no special selector
	}
}

/**
* Base class for image captions support.
*/
class SIGPlusCaptionsEngine extends SIGPlusEngine {
	protected $download = false;
	protected $metadata = false;

	public function getCustomParameters($params) {
		return parent::getCustomParameters($params->captions_params);
	}

	public function showDownload($state = true) {
		$this->download = $state;
	}

	public function showMetadata($state = true) {
		$this->metadata = $state;
	}

	public function addScripts($id, SIGPlusGalleryParameters $params) {
		$this->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/'.$this->getScriptFilename().'.js');
	}
}
