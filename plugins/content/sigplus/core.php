<?php
/**
* @file
* @brief    sigplus Image Gallery Plus plug-in for Joomla
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

if (!defined('SIGPLUS_VERSION')) {
	define('SIGPLUS_VERSION', '1.4.2');
}

if (!defined('SIGPLUS_DEBUG')) {
	define('SIGPLUS_DEBUG', false);
}
if (!defined('SIGPLUS_LOGGING')) {
	define('SIGPLUS_LOGGING', false);
}
if (!defined('SIGPLUS_CONTENT_CACHING')) {
	define('SIGPLUS_CONTENT_CACHING', true);
}

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'exception.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'params.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'services.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'thumbs.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'engines.php';

function array_value($array, $key) {
	return isset($array[$key]) ? $array[$key] : false;
}

/**
* Builds HTML from tag name, attribute array and element content.
*/
function make_html($element, $attrs = false, $content = false) {
	$html = '<'.$element;
	if ($attrs !== false) {
		foreach ($attrs as $key => $value) {
			if ($value !== false) {
				$html .= ' '.$key.'="'.htmlspecialchars($value).'"';
			}
		}
	}
	if ($content !== false) {
		$html .= '>'.$content.'</'.$element.'>';
	} else {
		$html .= '/>';
	}
	return $html;
}

/**
* Returns the href attribute value of an anchor element.
*/
function get_anchor_attrs($html) {
	$matches = array();
	if (!preg_match('#<a\s([^<>]*)>#u', $html, $matches)) {
		return false;
	}
	$attrs = string_to_array(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
	return $attrs;
}

/**
* Logging services.
*/
class SIGPlusLogging {
	/** Error log. */
	private $log = array();
	/** Whether interactive javascript to show/hide listings has been added to the page head. */
	private $script = false;
	/** Singleton instance. */
	private static $inst = false;

	public static function instance() {
		if (self::$inst === false) {
			self::$inst = new SIGPlusLogging();
		}
		return self::$inst;
	}

	public function append($message) {
		$this->log[] = $message;
	}

	public function appendblock($message, $block) {
		$this->log[] = $message.' <a href="#" class="sigplus-logging">Show</a><pre class="sigplus-logging">'.htmlspecialchars($block).'</pre>';
	}

	public function fetch() {
		if (!$this->script) {
			$this->script = true;
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('window.addEvent("domready", function () { var links = $$("a.sigplus-logging"); links.addEvent("click", function (event) { var el = $(this).getNext(); el.setStyle("display", el.getStyle("display") != "none" ? "none" : "block"); event.preventDefault(); }); links.getNext().setStyle("display", "none"); });');
		}

		ob_start();
			print '<ul>';
			foreach ($this->log as $logentry) {
				print '<li>'.$logentry.'</li>';
			}
			print '</ul>';
			$this->log = array();
		return ob_get_clean();
	}
}

class SIGPlusCoreConfiguration {
	/** Whether to utilize a content delivery network to load javascript frameworks. */
	public $ajaxapi = 'default';
	/** Whether to enter debug mode. */
	public $debug = false;

	public function validate() {
		if (is_string($this->ajaxapi)) {
			switch ($this->ajaxapi) {
				case 'none': case 'local': case 'cdn-google': case 'cdn-microsoft': case 'cdn-jquery':
					break;
				default:
					$this->ajaxapi = 'default';
			}
		} else {
			$this->ajaxapi = (bool) $this->ajaxapi ? 'default' : 'local';
		}
		$this->debug = (bool) $this->debug;
	}

	/**
	* Set parameter object from a Joomla JParameters object.
	*/
	public function setParameters($params) {
		$this->ajaxapi = self::getParameterValue($params, 'ajaxapi', 'default');
		$this->debug = self::getParameterValue($params, 'debug', false);
		$this->validate();
	}
	
	private static function getParameterValue($params, $name, $default) {
		if ($params instanceof stdClass) {
			if (isset($params->$name)) {
				return $params->$name;
			}
		} else if ($params instanceof JRegistry) {  // Joomla 2.5 and earlier
			$paramvalue = $params->get($name);
			if (isset($paramvalue)) {
				return $paramvalue;
			}
		}
		return $default;
	}
}

/**
* System-wide global configuration settings.
*/
class SIGPlusConfiguration {
	public $core;
	public $services;
	public $galleries;

	public function setConfiguration(SIGPlusCoreConfiguration $core, SIGPlusImageServicesConfiguration $services, SIGPlusGalleryParameters $galleries) {
		$this->core = $core;
		$this->services = $services;
		$this->galleries = $galleries;
	}

	public function setParameters($params) {
		$this->core = new SIGPlusCoreConfiguration();  // global settings
		$this->core->setParameters($params);
		$this->services = new SIGPlusImageServicesConfiguration();  // image service settings
		$this->services->setParameters($params);
		$this->galleries = new SIGPlusGalleryParameters();  // administration back-end parameters
		$this->galleries->setParameters($params);
	}
}

/**
* sigplus Image Gallery Plus service class.
*/
class SIGPlusCore {
	/** General parameters. */
	private $imageservices;
	/** Associative array of default gallery-specific parameters. */
	private $defparams;
	/** Associative array of current gallery-specific parameters. */
	private $curparams;
	/** A list of identifiers issued. The list ensures uniqueness: duplicate identifiers are decorated to make them unique. */
	private static $galleryids = array();

    public function __construct(SIGPlusConfiguration $configuration = null) {
		// set general parameters
		$engineservices = SIGPlusEngineServices::instance();
		$conf = is_null($configuration) ? new SIGPlusCoreConfiguration() : $configuration->core;
		$engineservices->ajaxapi = $conf->ajaxapi;
		if (SIGPLUS_DEBUG) {  // force debug mode
			$engineservices->debug = true;
		} elseif (!isset($engineservices->debug)) {
			$engineservices->debug = (bool) $conf->debug;  // do not disable debug mode if already set
		}

		// set default global parameters for image galleries
		$this->defparams = is_null($configuration) ? new SIGPlusGalleryParameters() : $configuration->galleries;
		if (SIGPLUS_LOGGING) {
			$logging = SIGPlusLogging::instance();
			$logging->appendblock('Global parameters are:', print_r($this->defparams, true));
		}

		// create image services object
		try {
			$this->imageservices = new SIGPlusImageServices(is_null($configuration) ? null : $configuration->services);
		} catch (Exception $e) {
			$this->imageservices = null;  // image services not available
			throw $e;                     // re-throw exception
		}
	}

	/**
	* Creates a thumbnail image, a preview image, and a watermarked image for an original.
	* Images are generated only if they do not already exist.
	* A separate thumbnail image is generated if the preview is too large to act as a thumbnail.
	* @param string $imageref An absolute file system path or URL to an image.
	*/
	private function createPreviewImage($imageref) {
		$params = new SIGPlusPreviewParameters($this->curparams);
		$imagelibrary = SIGPlusImageLibrary::instantiate($this->imageservices->getLibrary());

		// create watermarked image
		if (!is_remote_path($imageref) && $this->curparams->watermark && ($watermarkpath = $this->imageservices->checkWatermarkPath(dirname($imageref))) !== false) {
			if ($this->imageservices->checkWatermarkedPath($imageref) === false) {
				$watermarkedpath = $this->imageservices->createWatermarkedPath($imageref);
				$watermarkparams = $this->curparams->watermark_params;
				$watermarkparams['quality'] = $params->quality;  // GD cannot extract quality parameter from stored image, use quality set by user
				$result = $imagelibrary->createWatermarked($imageref, $watermarkpath, $watermarkedpath, $watermarkparams);
				if (SIGPLUS_LOGGING) {
					$logging = SIGPlusLogging::instance();
					if ($result) {
						$logging->append('Saved watermarked image to <kbd>'.$watermarkedpath.'</kbd>');
					} else {
						$logging->append('Failed to save watermarked image to <kbd>'.$watermarkedpath.'</kbd>');
					}
				}
			}
		}

		// create preview image
		if ($this->imageservices->checkPreviewPath($imageref, $params) === false) {  // create image on-the-fly if not exists
			$previewpath = $this->imageservices->createPreviewPath($imageref, $params);
			$result = $imagelibrary->createThumbnail($imageref, $previewpath, $params->width, $params->height, $params->crop, $params->quality);
			if (SIGPLUS_LOGGING) {
				$logging = SIGPlusLogging::instance();
				if ($result) {
					$logging->append('Saved preview image to <kbd>'.$previewpath.'</kbd>');
				} else {
					$logging->append('Failed to save preview image to <kbd>'.$previewpath.'</kbd>');
				}
			}
		}

		// create thumbnail image
		if ($this->imageservices->checkThumbnailPath($imageref, $params) === false) {  // separate thumbnail image is required
			$thumbpath = $this->imageservices->createThumbnailPath($imageref, $params);
			$thumbparams = $params->getThumbnailParameters();
			if (!isset($previewpath)) {  // preview image already exists but not thumbnail
				$previewpath = $this->imageservices->createPreviewPath($imageref, $params);
			}
			$result = $imagelibrary->createThumbnail($previewpath, $thumbpath, $thumbparams->width, $thumbparams->height, $thumbparams->crop, $thumbparams->quality);  // use preview image as source
			if (SIGPLUS_LOGGING) {
				$logging = SIGPlusLogging::instance();
				if ($result) {
					$logging->append('Saved thumbnail to <kbd>'.$thumbpath.'</kbd>');
				} else {
					$logging->append('Failed to save thumbnail to <kbd>'.$thumbpath.'</kbd>');
				}
			}
		}
	}

	/**
	* Fetches metadata associated with a (local) image.
	*/
	private function getImageMetadata($imageref) {
		if (is_remote_path($imageref)) {
			return false;  // do not extract metadata of remote images
		} else {
			require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'metadata.php';
			return SIGPlusIPTCServices::getImageMetadata($imageref);
		}
	}

	/**
	* Retrieves all data associated with an image.
	* @param string $imageref An absolute file system path or URL to an image.
	* @return An associative array of image (meta)data.
	*/
	private function getImageData($imageref, $index, $label = false) {
		// get lightbox
		$engineservices = SIGPlusEngineServices::instance();
		$lightbox = $engineservices->getLightboxEngine($this->curparams->lightbox);  // get selected lightbox engine if any or use default

		// avoid generating HTML for those images that cannot be accessed
		if ($this->curparams->maxcount > 0 && $index >= $this->curparams->maxcount && !($lightbox && $lightbox->isQuickNavigationSupported())) {
			return false;
		}

		// get image thumbnail URL and parameters
		$params = new SIGPlusPreviewParameters($this->curparams);
		$thumburl = $this->imageservices->getThumbnailUrl($imageref, $params);
		$previewurl = $this->imageservices->getPreviewUrl($imageref, $params);

		// use caption and summary from labels file if available
		if ($label !== false) {
			$caption = $label->getCaptionHtml();
			$summary = $label->getDescriptionHtml();
		} else {
			$caption = false;
			$summary = false;
		}

		// try to fill in missing caption or summary from image metadata
		if (!$caption || !$summary) {
			$metadata = $this->getImageMetadata($imageref);

			if ($metadata !== false) {
				// use caption and summary from metadata
				if (!$caption && isset($metadata['Headline'])) {
					if (is_array($metadata['Headline'])) {
						$caption = implode(';', $metadata['Headline']);
					} else {
						$caption = $metadata['Headline'];
					}
					if (!$caption && isset($metadata['Title'])) {
						$caption = $metadata['Title'];
					}

					// make caption suitable for embedding in HTML
					$caption = htmlspecialchars($caption);
				}
				if (!$summary && isset($metadata['Caption-Abstract'])) {
					if (is_array($metadata['Caption-Abstract'])) {
						$summary = implode(';', $metadata['Caption-Abstract']);
					} else {
						$summary = $metadata['Caption-Abstract'];
					}
					$summary = htmlspecialchars($summary);
				}
			}
		}

		// get image metadata (for display in metadata window) if not yet processed
		if ($this->curparams->metadata && !isset($metadata)) {
			$metadata = $this->getImageMetadata($imageref);
		}

		// get target URL for preview image
		$url = false;
		if ($lightbox) {  // display lightbox pop-up window when thumbnail is clicked
			if (is_remote_path($imageref)) {
				$url = $imageref;
			} else {
				$url = $this->imageservices->getImageUrl($imageref, $this->curparams->authentication);
				if ($this->curparams->watermark && $this->imageservices->checkWatermarkedPath($imageref) !== false) {
					$url = $this->imageservices->getWatermarkedUrl($imageref);
				}
			}
			$anchor_attrs = array('href' => $url);
		} elseif ($summary && ($anchor = get_anchor_attrs($summary)) !== false) {  // check if there is a hyperlink in the description and use it as target link
			$anchor_attrs = $anchor;
		}

		// get preview image parameters
		$img_attrs = array('preview' => $previewurl);
		if (!$this->curparams->crop) {
			if (($previewpath = $this->imageservices->checkPreviewPath($imageref, $params)) !== false) {
				$imagedims = getimagesize($previewpath);
			} else {
				$imagedims = false;
			}
			if ($imagedims !== false) {
				$params->width = $imagedims[0];
				$params->height = $imagedims[1];
			}
		}
		$img_attrs['width'] = $params->width;
		$img_attrs['height'] = $params->height;

		// get thumbnail image parameters
		if ($thumburl != $previewurl) {
			$img_attrs['thumb'] = $thumburl;
			$thumbparams = $params->getThumbnailParameters();
			$img_attrs['thumb-width'] = $thumbparams->width;
			$img_attrs['thumb-height'] = $thumbparams->height;
		}

		// set image caption and summary
		if ($caption) {
			$img_attrs['caption'] = $caption;
		}
		if ($summary) {
			$img_attrs['summary'] = $summary;
		}

		// get download URL
		if ($this->curparams->download && ($downloadurl = $this->imageservices->getFullsizeImageDownloadUrl($imageref, $this->curparams->authentication)) !== false) {
			$img_attrs['fullsize'] = $downloadurl;
		}

		if (SIGPLUS_LOGGING) {
			$logging = SIGPlusLogging::instance();
			$logging->append('Preview image URL is <kbd>'.$previewurl.'</kbd>');
			if ($thumburl != $previewurl) {
				$logging->append('Thumbnail image URL is <kbd>'.$thumburl.'</kbd>');
			}
			if (isset($metadata)) {
				if ($metadata !== false) {
					$logging->append('Image metadata is available.');
				} else {
					$logging->append('Image metadata has been processed but is not available.');
				}
			}
		}

		$imagedata = array(
			'image' => $img_attrs);
		if (isset($anchor_attrs)) {
			$imagedata['anchor'] = $anchor_attrs;
		}
		if ($this->curparams->metadata && isset($metadata) && $metadata !== false) {
			$imagedata['metadata'] = $metadata;
		}
		return $imagedata;
	}

	/**
	* Returns JavaScript code for a preview image in a gallery list.
	* @param integer $index The zero-based index of the image in the gallery.
	* @param integer $total The total number of images in the gallery.
	* @return string JavaScript code.
	*/
	private function getPreviewScript($galleryid, $index, $total, $imagedata) {
		$anchor_attrs = isset($imagedata['anchor']) ? $imagedata['anchor'] : false;
		$img_attrs = $imagedata['image'];

		return '['.implode(',',
			array(
				($anchor_attrs ? json_encode($anchor_attrs['href']) : 'null'),
				json_encode(isset($img_attrs['preview']) ? $img_attrs['preview'] : ''),
				$img_attrs['width'],
				$img_attrs['height'],
				json_encode(isset($img_attrs['thumb']) ? $img_attrs['thumb'] : ''),  // only with progressive loading enabled
				json_encode(isset($img_attrs['caption']) ? $img_attrs['caption'] : ''),
				json_encode(isset($img_attrs['summary']) ? $img_attrs['summary'] : ''),
				json_encode(isset($img_attrs['fullsize']) ? $img_attrs['fullsize'] : ''),
				json_encode(isset($imagedata['metadata']) ? $imagedata['metadata'] : null)
			)
		).']';
	}

	/**
	* Returns HTML code for a preview image in a gallery list.
	* @param integer $index The zero-based index of the image in the gallery.
	* @param integer $total The total number of images in the gallery.
	* @return string HTML code.
	*/
	private function getPreviewHtml($galleryid, $index, $total, $imagedata) {
		ob_start();

		if (isset($imagedata['anchor'])) {
			$anchor_attrs = $imagedata['anchor'];
		}
		$img_params = $imagedata['image'];

		$engineservices = SIGPlusEngineServices::instance();
		$lightbox = $engineservices->getLightboxEngine($this->curparams->lightbox);  // get selected lightbox engine if any or use default

		// add rel attribute to hook lightbox
		if ($lightbox && isset($anchor_attrs)) {
			$anchor_attrs['rel'] = $lightbox->getLinkAttribute($galleryid);
		}

		// compose preview image (HTML img element)
		$imageid = $galleryid.'_img'.sprintf('%04d', $index);
		$img_attrs = array(
			'id' => $imageid,
			'width' => $img_params['width'],
			'height' => $img_params['height'],
			'src' => $img_params['preview']);
		if (isset($img_params['thumb'])) {  // a separate thumbnail image is available
			//$img_attrs['data-thumb'] = $img_params['thumb'];
			if ($this->curparams->maxcount > 0 && $index >= $this->curparams->maxcount) {
				// preview image would never be shown, image would only appear in lightbox navigation bar...
				$img_attrs['longdesc'] = $img_params['thumb'];
				// ...but lightbox can extract URL from longdesc attribute
				$img_attrs['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';  // fully transparent blank 1x1 image
			} elseif ($this->curparams->progressive) {  // a slider is present with progressive loading enabled
				$img_attrs['longdesc'] = $img_attrs['src'];
				$img_attrs['src'] = $img_params['thumb'];  // overriden with preview image by progressive loading
			}
		}
		if (!isset($img_params['caption']) && $this->curparams->deftitle !== false) {
			$img_params['caption'] = $this->curparams->deftitle;
		}
		if (isset($img_params['caption'])) {  // replace placeholders for current image number and total image count with actual values
			$img_params['caption'] = str_replace(array('{$current}', '{$total}'), array((string) ($index+1), $total), $img_params['caption']);
		}
		$img_attrs['alt'] = isset($img_params['caption']) ? strip_tags($img_params['caption']) : '';

		if (!isset($img_params['summary']) && $this->curparams->defdescription !== false) {  // set default description if no description is supplied
			$img_params['summary'] = $this->curparams->defdescription;
		}
		if (isset($img_params['summary'])) {
			$img_params['summary'] = str_replace(array('{$current}', '{$total}'), array((string) ($index+1), $total), $img_params['summary']);
		}
		if (isset($img_params['summary'])) {
			// convert HTML entities in summary text and strip HTML tags, special characters in text will be escaped when added to HTML element attributes
			$summary = html_entity_decode(strip_tags($img_params['summary']), ENT_QUOTES, 'UTF-8');
			if (isset($anchor_attrs) && !isset($anchor_attrs['title'])) {
				$anchor_attrs['title'] = $summary;
			} else {
				$img_attrs['title'] = $summary;
			}
		}

		// compose metadata field (invisible HTML div element)
		ob_start();
			if (isset($img_params['caption']) && $img_params['caption'] != $img_attrs['alt']) {  // HTML has been stripped away
				print '<div id="'.$imageid.'_caption">'.$img_params['caption'].'</div>';
			}

			// summary text to display below image
			if (isset($img_params['summary'])) {
				print '<div id="'.$imageid.'_summary">'.$img_params['summary'].'</div>';
			}

			// image icons
			if (isset($img_params['fullsize'])) {
				print '<a rel="download" href="'.$img_params['fullsize'].'"></a>';
			}

			// image metadata
			if (isset($imagedata['metadata'])) {  // display IPTC image metadata in pop-up window if set
				print '<div id="'.$imageid.'_iptc">';
				print '<table>';
				foreach ($imagedata['metadata'] as $key => $value) {
					print '<tr><th>'.htmlspecialchars($key).'</th>';
					if (is_array($value)) {
						$stringvalue = implode(', ', $value);
					} else {
						$stringvalue = $value;
					}
					print '<td>'.nl2br(htmlspecialchars($stringvalue)).'</td></tr>';
				}
				print '</table>';
				print '</div>';
			}
		$meta = ob_get_clean();

		if ($this->curparams->maxcount > 0 && $index >= $this->curparams->maxcount) {  // images in excess of maximum thumbnail count
			print '<li style="display:none !important;">';  // images are shown in pop-up window but not on page
		} else {
			print '<li>';
		}
		$imagehtml = make_html('img', $img_attrs);
		if (isset($anchor_attrs)) {
			print make_html('a', $anchor_attrs, $imagehtml);
		} else {
			print $imagehtml;
		}
		if ($meta) {
			print '<div id="'.$imageid.'_metadata" style="display:none !important;">'.$meta.'</div>';
		}
		print '</li>';
		return ob_get_clean();
	}

	/**
	* Adds style and script declarations for an image gallery.
	*/
	private function addStylesAndScripts($galleryid) {
		// add styles and scripts for image gallery
		$engineservices = SIGPlusEngineServices::instance();
		$engineservices->addStyleDefaultLanguage();
		$engineservices->addStyles();
		$lightbox = $engineservices->getLightboxEngine($this->curparams->lightbox);  // get selected lightbox engine if any, or use default
		if ($lightbox) {
			$lightbox->addStyles();
			$lightbox->addScripts($galleryid, $this->curparams);
		}
		if ($this->curparams->metadata) {
			$metadatabox = $engineservices->getMetadataEngine($this->curparams->lightbox);
			if ($metadatabox) {
				$metadatabox->addStyles();
				$metadatabox->addMetadataScripts();
			}
		}
		$slider = $engineservices->getSliderEngine($this->curparams->slider);  // get selected slider engine if any, or use default
		if ($slider) {  // use image thumbnail navigation controls
			$slider->addStyles();
			$slider->addScripts($galleryid, $this->curparams);
		}
		$captions = $engineservices->getCaptionsEngine($this->curparams->captions);  // get selected captions engine if any, or use default
		if ($captions) {
			if ($this->curparams->metadata) {
				$captions->showMetadata(true);
			}
			if ($this->curparams->download) {
				$captions->showDownload(true);
			}
			$captions->addStyles();
			$captions->addScripts($galleryid, $this->curparams);
		}

		// add custom style declaration based on back-end and inline settings
		$cssrules = array();
		if ($this->curparams->margin !== false) {
			$cssrules[] = 'margin:'.$this->curparams->margin.' !important;';
		}
		if ($this->curparams->borderwidth !== false && $this->curparams->borderstyle !== false && $this->curparams->bordercolor !== false) {
			$cssrules[] = 'border:'.$this->curparams->borderwidth.' '.$this->curparams->borderstyle.' #'.$this->curparams->bordercolor.' !important;';
		} else {
			if ($this->curparams->borderwidth !== false) {
				$cssrules[] = 'border-width:'.$this->curparams->borderwidth.' !important;';
			}
			if ($this->curparams->borderstyle !== false) {
				$cssrules[] = 'border-style:'.$this->curparams->borderstyle.' !important;';
			}
			if ($this->curparams->bordercolor !== false) {
				$cssrules[] = 'border-color:#'.$this->curparams->bordercolor.' !important;';
			}
		}
		if ($this->curparams->padding !== false) {
			$cssrules[] = 'padding:'.$this->curparams->padding.' !important;';
		}
		if (!empty($cssrules)) {
			$document = JFactory::getDocument();
			$selectors = array(
				'ul > li img');
			if ($slider && ($selector = $slider->getImageStyleSelector())) {
				$selectors[] = $selector;
			}
			foreach ($selectors as &$selector) {
				$selector = '#'.$galleryid.' '.$selector;
			}
			$document->addStyleDeclaration(implode(', ', $selectors).' { '.implode("\n", $cssrules).' }');
		}
		//$document->addStyleDeclaration('#'.$galleryid.' ul > li { width: '.$this->curparams->width.'px; height: '.$this->curparams->height.'px; }');

		$engineservices->addOnReadyScripts();
	}

	/**
	* Generates an image gallery whose images come from Picasa Web Albums.
	* @see http://picasaweb.google.com
	* @param string $url The Picasa album RSS feed URL.
	* @param string $galleryid An identifier for the gallery to generate.
	*/
	private function getPicasaImageGallery($url, $galleryid) {
		// check for presence of XML parser
		if (!function_exists('simplexml_load_file')) {
			throw new SIGPlusNotSupportedException();
		}

		// parse album feed URL
		$urlparts = parse_url(htmlspecialchars_decode($url));

		// extract Picasa user identifier and album identifier from feed URL
		$urlpath = $urlparts['path'];
		$match = array();
		if (!preg_match('"^/data/feed/(?:api|base)/user/([^/?#]+)/albumid/([^/?#]+)"', $urlpath, $match)) {
			return array();
		}
		$userid = $match[1];
		$albumid = $match[2];

		// extract feed URL parameters (including authorization key if any)
		$urlquery = array();
		if (isset($urlparts['query'])) {
			parse_str($urlparts['query'], $urlquery);
		}

		// define fixed thumbnail sizes provided by Picasa
		$sizes_cropped = array(32, 48, 64, 72, 104, 144, 150, 160);
		$sizes_uncropped = array_merge($sizes_cropped, array(94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600));
		sort($sizes_uncropped);

		// choose cropped vs. uncropped
		if ($this->curparams->crop) {
			$sizes = $sizes_cropped;
			$crop = 'c';
		} else {
			$sizes = $sizes_uncropped;
			$crop = 'u';
		}

		// get thumbnail size(s) that best match(es) expected preview image dimensions
		$mindim = min($this->curparams->width, $this->curparams->height);  // smaller dimension
		$minsize = $sizes[0];
		for ($k = 0; $k < count($sizes) && $mindim >= $sizes[$k]; $k++) {  // smaller than both width and height
			$minsize = $sizes[$k];
		}
		$preferred = array($minsize);
		$maxdim = max($this->curparams->width, $this->curparams->height);  // larger dimension
		for ($k = 0; $k < count($sizes) && $maxdim >= $sizes[$k]; $k++) {
			$preferred[] = $sizes[$k];
		}
		sort($preferred, SORT_REGULAR);
		$preferred = array_unique($preferred, SORT_REGULAR);

		// build URL query string to fetch list of photos in album
		$feedquery = array(
			'kind' => 'photo',
			'thumbsize' => implode($crop.',', $preferred).$crop,  // preferred thumb sizes
			'fields' => 'id,updated,entry(id,updated,media:group)'  // fetch only the listed XML elements
		);
		if ($this->curparams->maxcount) {
			$feedquery['max-results'] = $this->curparams->maxcount;
		}
		if (isset($urlquery['authkey'])) {  // pass on authorization key
			$feedquery['authkey'] = $urlquery['authkey'];
		}

		// build URL to fetch list of photos in album
		$uri = JFactory::getURI();
		$scheme = $uri->isSSL() ? 'https:' : 'http:';
		$feedurl = $scheme.'//picasaweb.google.com/data/feed/api/user/'.$userid.'/albumid/'.$albumid.'?'.http_build_query($feedquery);

		// get XML file of list of photos in an album
		$sxml = simplexml_load_file($feedurl);
		if ($sxml === false) {
			return array();
		}

		$items = array();
		$index = 0;
		foreach ($sxml->entry as $entry) {  // enumerate album entries with XPath "/feed/entry"
			$media = $entry->children('http://search.yahoo.com/mrss/');  // children with namespace "media"
			$mediagroup = $media->group;

			// get image title and description
			$title = (string) $mediagroup->title;
			$description = (string) $mediagroup->description;

			// get image URL
			$attrs = $mediagroup->content->attributes();
			$imageurl = (string) $attrs['url'];  // <media:content url='...' height='...' width='...' type='image/jpeg' medium='image' />

			// get preview image URL
			$thumburl = null;
			$thumbwidth = 0;
			$thumbheight = 0;
			foreach ($mediagroup->thumbnail as $thumbnail) {
				$attrs = $thumbnail->attributes();
				$curwidth = (int) $attrs['width'];
				$curheight = (int) $attrs['height'];

				// update thumbnail to use if it fits in image bounds
				if ($this->curparams->width >= $curwidth && $this->curparams->height >= $curheight && ($curwidth > $thumbwidth || $curheight > $thumbheight)) {
					$thumburl = (string) $attrs['url'];  // <media:thumbnail url='...' height='...' width='...' />
					$thumbwidth = $curwidth;
					$thumbheight = $curheight;
				}
			}

			// build image data
			$imagedata = array(
				'image' => array(
					'width' => $thumbwidth,
					'height' => $thumbheight,
					'preview' => $thumburl
				),
				'anchor' => array(
					'href' => $imageurl
				)
			);
			if ($title) {
				$imagedata['image']['caption'] = $title;
			}
			if ($description) {
				$imagedata['image']['summary'] = $description;
			}

			// generate code
			switch ($this->curparams->linkage) {
				case 'inline':
					$items[] = $this->getPreviewHtml($galleryid, $index, count($sxml->entry), $imagedata);
					break;
				default:
					$items[] = $this->getPreviewScript($galleryid, $index, count($sxml->entry), $imagedata);
			}
			$index++;
		}
		return $items;
	}

	/**
	* Generates an image gallery entirely defined with a list of label objects of remote URLs.
	* @param $list An array of label objects.
	*/
	private function getUserDefinedRemoteImageGallery(array $list, $galleryid) {
		foreach ($list as $listitem) {
			$this->createPreviewImage($listitem->imagefile);
		}

		$images = array();
		foreach ($list as $listitem) {
			if (($image = $this->getImageData($listitem->imagefile, count($images), $listitem)) === false) {
				continue;
			}
			$images[] = $image;  // add image to list of image data
		}

		$items = array();
		foreach ($images as $index => $image) {
			switch ($this->curparams->linkage) {
				case 'inline':
					$items[] = $this->getPreviewHtml($galleryid, $index, count($images), $image);
					break;
				default:
					$items[] = $this->getPreviewScript($galleryid, $index, count($images), $image);
			}
		}
		return $items;
	}

	/**
	* Generates an image gallery entirely defined with a list of filenames or a list of label objects.
	* @param $imagedirectory An absolute path to a directory.
	* @param $list An array of filenames and/or label objects.
	*/
	private function getUserDefinedImageGallery($imagedirectory, array $list, $galleryid) {
		foreach ($list as $listitem) {
			if (is_string($listitem)) {
				$this->createPreviewImage($imagedirectory.DIRECTORY_SEPARATOR.$listitem);
			} else {
				$this->createPreviewImage($imagedirectory.DIRECTORY_SEPARATOR.$listitem->imagefile);
			}
		}

		$images = array();
		foreach ($list as $listitem) {
			if (is_string($listitem)) {
				$image = $this->getImageData($imagedirectory.DIRECTORY_SEPARATOR.$listitem, count($images));
			} else {
				$image = $this->getImageData($imagedirectory.DIRECTORY_SEPARATOR.$listitem->imagefile, count($images), $listitem);
			}
			if ($image === false) {
				continue;
			}
			$images[] = $image;  // add image to list of image data
		}

		$items = array();
		foreach ($images as $index => $image) {
			switch ($this->curparams->linkage) {
				case 'inline':
					$items[] = $this->getPreviewHtml($galleryid, $index, count($images), $image);
					break;
				default:
					$items[] = $this->getPreviewScript($galleryid, $index, count($images), $image);
			}
		}
		return $items;
	}

	/**
	* Generates an image gallery where some files have labels.
	* @param $imagedirectory An absolute path to a directory in the file system.
	*/
	private function getLabeledImageGallery($imagedirectory, $files, $labels, $galleryid) {
		if (empty($files)) {
			return false;
		}
		$labelmap = array();
		foreach ($labels as $label) {  // enumerate images listed in labels.txt
			$labelmap[$label->imagefile] = $label;
		}
		$files = array_filter($files, 'is_imagefile');
		foreach ($files as $file) {
			$this->createPreviewImage($imagedirectory.DIRECTORY_SEPARATOR.$file);
		}

		$images = array();
		foreach ($files as $file) {
			if (($image = $this->getImageData($imagedirectory.DIRECTORY_SEPARATOR.$file, count($images), array_value($labelmap, $file))) === false) {
				continue;
			}
			$images[] = $image;
		}

		$items = array();
		foreach ($images as $index => $image) {
			switch ($this->curparams->linkage) {
				case 'inline':
					$items[] = $this->getPreviewHtml($galleryid, $index, count($images), $image);
					break;
				default:
					$items[] = $this->getPreviewScript($galleryid, $index, count($images), $image);
			}
		}
		return $items;
	}

	/**
	* Generates an image gallery where files have no labels.
	* @param $imagedirectory An absolute path to a directory in the file system.
	*/
	private function getUnlabeledImageGallery($imagedirectory, $files, $galleryid) {
		return $this->getLabeledImageGallery($imagedirectory, $files, array(), $galleryid);
	}

	/**
	* Ensures that a gallery identifier is unique across the page.
	* A gallery identifier is specified by the user or generated from the relative image path. Other extensions,
	* however, may duplicate article content on the page (e.g. show a short article extract in a module position),
	* making an identifier no longer unique. This function adds an ordinal to prevent conflicts when the same gallery
	* would occur multiple times on the page, causing scripts not to function properly.
	*/
	private function getUniqueGalleryId($galleryid) {
		if (in_array($galleryid, self::$galleryids)) {  // look for identifier in script-lifetime container
			$counter = 1000;
			do {
				$counter++;
				$gid = $galleryid.'_'.$counter;
			} while (in_array($gid, self::$galleryids));
			$galleryid = $gid;
		}
		self::$galleryids[] = $galleryid;
		return $galleryid;
	}

	/**
	* Generates image previews with alternate text, title and lightbox pop-up activation on mouse click.
	* @param string $body Data associated with the gallery.
	* @param string $paramstring A whitespace-separated list of name="value" parameter values.
	*/
	private function getImageGalleryHtml($body, $params = array()) {
		// set gallery parameters
		$this->curparams = clone $this->defparams;  // parameters set in back-end
		if (is_array($params)) {
			$this->curparams->setArray($params);
		} else {
			$paramstring = htmlspecialchars_decode((string) $params);
			$this->curparams->setString($paramstring);  // parameters set inline
		}
		if (!isset($body)) {  // path is set via parameter with compact activation syntax
			$body = $this->curparams->path;
		}
		$engineservices = SIGPlusEngineServices::instance();

		// generate link to an existing gallery
		if ($this->curparams->link !== false) {
			$lightbox = $engineservices->getLightboxEngine($this->curparams->lightbox);  // get selected lightbox engine if any or use default
			if ($lightbox && ($linkscript = $lightbox->getLinkScript($this->curparams->link, $this->curparams->index)) !== false) {
				return '<a href="javascript:void('.htmlspecialchars($linkscript).')">'.$body.'</a>';
			} else {  // engine does not support programmatic activation
				return $body;
			}
		}

		// set gallery folders
		$imageref = $body;  // a relative path to an image folder or an image, or an absolute URL to an image to display
		if ($isremote = is_remote_path($imageref)) {
			$imageurl = $imageref;
			$iswebalbum = (bool) preg_match('"^https?://picasaweb.google.com/data/feed/(?:api|base)/user/([^/?#]+)/albumid/([^/?#]+)"', $imageurl);  // test for Picasa galleries

			$imagehashbase = $imageurl;
		} else {
			$imageref = trim($imageref, '/');  // remove leading and trailing backslash

			// verify validity of relative path
			$imagepath = $this->imageservices->getImagePath($imageref);
			if (!file_exists($imagepath)) {
				throw new SIGPlusImageGalleryFolderException($imageref);
			}

			$imagehashbase = $imagepath;  // base in computing hash for content caching
		}

		// set gallery identifier
		if ($this->curparams->id) {  // use user-supplied identifier
			$galleryid = $this->curparams->id;
		} else {  // automatically generate identifier for thumbnail gallery
			$galleryid = 'sigplus_'.md5($imagehashbase);
		}
		$galleryid = $this->getUniqueGalleryId($galleryid);

		// force meaningful settings for single-image view (disable slider and activate flow layout)
		if ($this->curparams->layout != 'hidden' && ($isremote && !$iswebalbum || isset($imagepath) && is_file($imagepath))) {
			$this->curparams->layout = 'flow';
			$this->curparams->rows = false;
			$this->curparams->cols = false;
			$this->curparams->slider = false;
		}

		// substitute proper left or right alignment depending on whether language is LTR or RTL
		$language = JFactory::getLanguage();
		$this->curparams->alignment = str_replace(array('after','before'), $language->isRTL() ? array('left','right') : array('right','left'), $this->curparams->alignment);

		// get selected slider engine if any, or use default
		$slider = $engineservices->getSliderEngine($this->curparams->slider);
		if (!$slider) {
			$this->curparams->progressive = false;  // progressive loading is not supported unless a slider is enabled
		}

		// *** cannot update $this->curparams, which is used in content caching, beyond this point *** //

		// initialize logging
		if (SIGPLUS_LOGGING) {
			$logging = SIGPlusLogging::instance();
			if ($isremote) {
				$logging->append('Generating gallery "'.$galleryid.'" from URL: <kbd>'.$imageurl.'</kbd>');
			} else {
				$logging->append('Generating gallery "'.$galleryid.'" from file/directory: <kbd>'.$imagepath.'</kbd>');
			}
			$logging->appendblock('Local parameters for "'.$galleryid.'" are:', print_r($this->curparams, true));
		}

		// verify if content is available in cache folder
		if (!SIGPLUS_CONTENT_CACHING || $engineservices->debug || $this->curparams->hasRandom()) {
			$cachekey = false;  // galleries that involve a random element cannot be cached
		} elseif (($cachekey = $this->imageservices->getCachedContent($imagehashbase, $this->curparams)) !== false) {
			if (SIGPLUS_LOGGING) {
				$logging->append('Retrieving cached content with key <kbd>'.$cachekey.'</kbd>.');
			}
		}

		// generate gallery HTML code or setup script
		if ($cachekey === false) {
			// save default title and description, which might be overridden in labels file, affecting hash key used in caching
			$deftitle = $this->curparams->deftitle;
			$defdescription = $this->curparams->defdescription;

			if ($isremote) {  // access images remote domain
				if ($iswebalbum) {
					$htmlorscript = $this->getPicasaImageGallery($imageurl, $galleryid);
				} else {
					$extension = strtolower(pathinfo(parse_url($imageurl, PHP_URL_PATH), PATHINFO_EXTENSION));
					switch ($extension) {
						case 'gif': case 'jpg': case 'jpeg': case 'png':  // plug-in syntax {gallery}http://example.com/image.jpg{/gallery}
							$labels = array(new SIGPlusImageLabel($imageurl, false, false));  // artificial single-entry labels file
							$htmlorscript = $this->getUserDefinedRemoteImageGallery($labels, $galleryid);
							break;
						default:  // plug-in syntax {gallery}http://example.com{/gallery}
							throw new SIGPlusNotSupportedException();

							$labels = $this->imageservices->getLabels($imageurl, $this->curparams->labels, $this->curparams->deftitle, $this->curparams->defdescription);
							switch ($this->curparams->sortcriterion) {
								case SIGPLUS_SORT_RANDOMLABELS:
									shuffle($labels);
									// fall through
								case SIGPLUS_SORT_LABELS_OR_FILENAME:
								case SIGPLUS_SORT_LABELS_OR_MTIME:
									$htmlorscript = $this->getUserDefinedRemoteImageGallery($labels, $galleryid);
							}
					}
				}
			} else {
				if (is_file($imagepath)) {  // syntax {gallery}folder/subfolder/file.jpg{/gallery}
					$htmlorscript = $this->getUnlabeledImageGallery(dirname($imagepath), array(basename($imagepath)), $galleryid);
				} else {  // syntax {gallery}folder/subfolder{/gallery}
					// fetch image labels
					switch ($this->curparams->labels) {
						case 'filename':
							$labels = $this->imageservices->getLabelsFromFilenames($imagepath); break;
						default:
							$labels = $this->imageservices->getLabels($imagepath, $this->curparams->labels, $this->curparams->deftitle, $this->curparams->defdescription);
					}
					switch ($this->curparams->sortcriterion) {
						case SIGPLUS_SORT_LABELS_OR_FILENAME:
							if (empty($labels)) {  // there is no labels file to use
								$files = $this->imageservices->getListing($imagepath, SIGPLUS_FILENAME, $this->curparams->sortorder, $this->curparams->depth);
								$htmlorscript = $this->getUnlabeledImageGallery($imagepath, $files, $galleryid);
							} else {
								$htmlorscript = $this->getUserDefinedImageGallery($imagepath, $labels, $galleryid);
							}
							break;
						case SIGPLUS_SORT_LABELS_OR_MTIME:
							if (empty($labels)) {
								$files = $this->imageservices->getListing($imagepath, SIGPLUS_MTIME, $this->curparams->sortorder, $this->curparams->depth);
								$htmlorscript = $this->getUnlabeledImageGallery($imagepath, $files, $galleryid);
							} else {
								$htmlorscript = $this->getUserDefinedImageGallery($imagepath, $labels, $galleryid);
							}
							break;
						case SIGPLUS_SORT_MTIME:
							$files = $this->imageservices->getListing($imagepath, SIGPLUS_MTIME, $this->curparams->sortorder, $this->curparams->depth);
							$htmlorscript = $this->getLabeledImageGallery($imagepath, $files, $labels, $galleryid);
							break;
						case SIGPLUS_SORT_RANDOM:
							$files = $this->imageservices->getListing($imagepath, SIGPLUS_RANDOM, $this->curparams->sortorder, $this->curparams->depth);
							$htmlorscript = $this->getLabeledImageGallery($imagepath, $files, $labels, $galleryid);
							break;
						case SIGPLUS_SORT_RANDOMLABELS:
							if (empty($labels)) {  // there is no labels file to use
								$files = $this->imageservices->getListing($imagepath, SIGPLUS_RANDOM, $this->curparams->sortorder, $this->curparams->depth);
								$htmlorscript = $this->getUnlabeledImageGallery($imagepath, $files, $galleryid);
							} else {
								shuffle($labels);
								$htmlorscript = $this->getUserDefinedImageGallery($imagepath, $labels, $galleryid);
							}
							break;
						default:  // case SIGPLUS_SORT_FILENAME:
							$files = $this->imageservices->getListing($imagepath, SIGPLUS_FILENAME, $this->curparams->sortorder, $this->curparams->depth);
							$htmlorscript = $this->getLabeledImageGallery($imagepath, $files, $labels, $galleryid);
							break;
					}
				}
			}

			if (!empty($htmlorscript)) {
				switch ($this->curparams->linkage) {
					case 'inline':
						$cachedata = ($slider !== false ? '<ul style="visibility:hidden;">' : '<ul>').implode($htmlorscript).'</ul>';
						break;
					case 'head':  // put generated content in HTML head (does not allow HTML body with bloating size, which would cause preg_replace in System - SEF to fail)
						$cachedata = $this->getGalleryScript($galleryid, $htmlorscript);
						break;
					case 'external':
						$cachedata = '__jQuery__(function () { '.$this->getGalleryScript($galleryid, $htmlorscript).' });';
						break;
				}
			} else {
				$cachedata = false;
			}

			// restore default title and description, which might have been overridden in labels file
			$this->curparams->deftitle = $deftitle;
			$this->curparams->defdescription = $defdescription;

			if (SIGPLUS_CONTENT_CACHING && !$this->curparams->hasRandom()) {
				// save generated content for future re-use in a temporary file in the cache folder
				$this->imageservices->cleanCachedContent();
				$cachekey = $this->imageservices->saveCachedContent($imagehashbase, $this->curparams, $cachedata);
				if (SIGPLUS_LOGGING) {
					if ($cachekey !== false) {
						$logging->append('Saved cached content with key <kbd>'.$cachekey.'</kbd>.');
					} else {
						$logging->append('Failed to persist content in cache folder.');
					}
				}
			}
		} elseif ($this->curparams->linkage != 'external') {  // retrieve content from cache but no need to fetch content for linking external .js file
			$cachefile = $this->imageservices->getCachedContentPath($cachekey, $this->curparams->linkage == 'inline' ? '.html' : '.js');
			if (filesize($cachefile) > 0) {
				$cachedata = file_get_contents($cachefile);
			} else {
				$cachedata = false;  // empty gallery
			}
		} else {
			$cachedata = true;
		}

		if ($cachedata === false) {  // no content
			$html = JText::_('SIGPLUS_EMPTY');
		} else {
			switch ($this->curparams->linkage) {
				case 'inline':
					$html = $cachedata;  // content produced as HTML only in inline linkage mode
					break;
				case 'head':
					$this->addGalleryScript();  // add gallery population script
					$engineservices->addOnReadyScript($cachedata);  // add gallery data
					$html = '';  // no content produced in HTML except for placeholder
					break;
				case 'external':
					$this->addGalleryScript();
					if ($cachekey !== false) {  // include reference to generated script in external .js file
						$document = JFactory::getDocument();
						$document->addScript($this->imageservices->getCachedContentUrl($cachekey, '.js'));
					} else {  // add script to document head as a fall-back if could not save to external .js file in cache folder
						$engineservices->addOnReadyScript($cachedata);
					}
					$html = '';
					break;
			}
		}

		// set image gallery alignment (left, center or right) and style
		$gallerystyle = 'sigplus-gallery';
		switch ($this->curparams->alignment) {
			case 'left': case 'left-clear': case 'left-float': $gallerystyle .= ' sigplus-left'; break;
			case 'center': $gallerystyle .= ' sigplus-center'; break;
			case 'right': case 'right-clear': case 'right-float': $gallerystyle .= ' sigplus-right'; break;
		}
		switch ($this->curparams->alignment) {
			case 'left': case 'left-float': case 'right': case 'right-float': $gallerystyle .= ' sigplus-float'; break;
			case 'left-clear': case 'right-clear': $gallerystyle .= ' sigplus-clear'; break;
		}
		switch ($this->curparams->imagecaptions) {
			case 'above': $gallerystyle .= ' sigplus-captionsabove'; break;
			case 'below': $gallerystyle .= ' sigplus-captionsbelow'; break;
		}

		// output image gallery or gallery placeholder
		$div_attrs = array(
			'id' => $galleryid,
			'class' => $gallerystyle);
		if ($this->curparams->layout == 'hidden') {
			$div_attrs['style'] = 'display:none !important;';
		}
		$html = make_html('div', $div_attrs, $html);

		// add style and script declarations
		$this->addStylesAndScripts($galleryid);

		$this->curparams = false;
		return $html;
	}

	/**
	* Adds JavaScript code that dynamically creating an image gallery from a data array.
	*/
	private function addGalleryScript() {
		$engineservices = SIGPlusEngineServices::instance();
		$engineservices->addJQuery();
		$document = JFactory::getDocument();
		$document->addScript(JURI::base(true).'/plugins/content/sigplus/js/linkage'.($engineservices->debug ? '.js' : '.min.js'));
	}

	private function getGalleryScript($galleryid, $script) {
		$engineservices = SIGPlusEngineServices::instance();
		$lightbox = $engineservices->getLightboxEngine($this->curparams->lightbox);  // get selected lightbox engine if any or use default
		return '__jQuery__("#'.$galleryid.'").sigplusLinkage('.
			'['.implode(',', $script).'],'.
			'"'.($lightbox ? $lightbox->getLinkAttribute($galleryid) : '').'",'.  // rel attribute to hook lightbox
			$this->curparams->maxcount.','.
			($this->curparams->progressive ? 'true' : 'false').','.
			'"'.addslashes($this->curparams->deftitle).'",'.
			'"'.addslashes($this->curparams->defdescription).'");';
	}

	/**
	* Generates image thumbnails with alternate text, title and lightbox pop-up activation on mouse click.
	* This method is typically called by the class plgContentSIGPlus, which represents the sigplus Joomla plug-in.
	* The method takes two parameters:
	* [*] A string that defines the gallery source. Relative paths are interpreted w.r.t. the image base folder,
	*     which is passed in a configuration object to the class constructor.
	*     If used as a plug-in, this string would normally appear enclosed in an activation tag.
	* [*] Gallery parameters, which are an array of parameters or a whitespace-separated list of name="value" parameter
	*     values.
	*     If used as a plug-in, these would normally appear as the attribute list of the activation start tag.
	*
	* If you use the plug-in, the activation code {gallery key=value}myfolder{/gallery} would translate into PHP code:
	*     $core = new SIGPlusCore();
	*     $source = 'myfolder';
	*     $params = 'key=value';
	*     $core->getGalleryHtml($source, $params);
	*
	* @param string $body A string that defines the gallery source.
	* @param string $params An array of parameters or a whitespace-separated list of name="value" parameter values.
	*/
	public function getGalleryHtml($source, $params = array()) {
		if (!isset($this->imageservices)) {  // global error, image services are not available
			throw new SIGPlusInitializationException();
		}
		$oblevel = ob_get_level();
		try {
			return $this->getImageGalleryHtml($source, $params);
		} catch (Exception $e) {  // local error
			for ($k = ob_get_level(); $k > $oblevel; $k--) {  // release output buffers
				ob_end_clean();
			}
			throw $e;  // re-throw exception
		}
	}

	/**
	* Adds activation code to a (fully customized) gallery.
	*/
	public function addGalleryEngines($customized = false) {
		if (!isset($this->imageservices)) {  // global error, image services not available
			throw new SIGPlusInitializationException();
		}
		$engineservices = SIGPlusEngineServices::instance();
		if ($customized) {
			// hook anchors with image extensions to lightbox engine if any
			$engineservices->addStyles();
			$lightbox = $engineservices->getLightboxEngine($this->defparams->lightbox);  // get selected lightbox engine if any, or use default
			if ($lightbox) {
				$lightbox->addStyles();
				$lightbox->addActivationScripts();
			}
		}
		$engineservices->addOnReadyEvent();
	}
}