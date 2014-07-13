<?php
/**
* @file
* @brief    sigplus Image Gallery Plus global and local parameters
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'constants.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'useragent.php';

// sort criterion override modes
define('SIGPLUS_SORT_LABELS_OR_FILENAME', 0);  // sort based on labels file with fallback to file name
define('SIGPLUS_SORT_LABELS_OR_MTIME', 1);     // sort based on labels file with fallback to last modified time
define('SIGPLUS_SORT_FILENAME', 2);            // sort based on file name ignoring order in labels file
define('SIGPLUS_SORT_MTIME', 3);               // sort based on last modified time ignoring order in labels file
define('SIGPLUS_SORT_RANDOM', 4);              // random order
define('SIGPLUS_SORT_RANDOMLABELS', 5);        // random order restricting images to those listed in labels file

/**
* Converts a string containing key-value pairs into an associative array.
* @param string $string The string to split into key-value pairs.
* @param string $separator The optional string that separates the key from the value.
* @return array An associative array that maps keys to values.
*/
function string_to_array($string, $separator = '=', $quotechars = array("'",'"')) {
	$separator = preg_quote($separator);
	if (is_array($quotechars)) {
		$quotedvalue = '';
		foreach ($quotechars as $quotechar) {
			$quotechar = preg_quote($quotechar[0]);  // escape characters with special meaning to regex
			$quotedvalue .= $quotechar.'[^'.$quotechar.']*'.$quotechar.'|';
		}
	} else {
		$quotechar = preg_quote($quotechar[0]);  // make sure quote character is a single character
		$quotedvalue = $quotechar.'[^'.$quotechar.']*'.$quotechar.'|';
	}
	$regularchar = '[A-Za-z0-9_.:/-]';
	$namepattern = '([A-Za-z_]'.$regularchar.'*)';  // html attribute name
	$valuepattern = '('.$quotedvalue.'-?[0-9]+(?:[.][0-9]+)?|'.$regularchar.'+)';
	$pattern = '#(?:'.$namepattern.$separator.')?'.$valuepattern.'#';

	$array = array();
	$matches = array();
	$result = preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
	if (!$result) {
		return false;
	}
	foreach ($matches as $match) {
		$name = $match[1];
		$value = trim($match[2], implode('', $quotechars));
		if (strlen($name) > 0) {
			$array[$name] = $value;
		} else {
			$array[] = $value;
		}
	}
    return $array;
}

define('SIGPLUS_THUMB_MAXSIZE', 60);

class SIGPlusPreviewParameters {
	/** Width of preview/thumbnail image (px). */
	public $width = 100;
	/** Height of preview/thumbnail image (px). */
	public $height = 100;
	/** Whether the original images was cropped when the preview/thumbnail was generated. */
	public $crop = true;
	/** JPEG quality measure. */
	public $quality = 85;

	function __construct(SIGPlusGalleryParameters $params = null) {
		if ($params) {
			$this->width = $params->width;
			$this->height = $params->height;
			$this->crop = $params->crop;
			$this->quality = $params->quality;
		}
	}

	/**
	* Whether a gallery requires separate thumbnail and preview image sets.
	*/
	public function isThumbnailRequired() {
		return $this->width > SIGPLUS_THUMB_MAXSIZE || $this->height > SIGPLUS_THUMB_MAXSIZE;
	}

	/**
	* Returns thumbnail parameters, reducing image dimensions as necessary.
	*/
	public function getThumbnailParameters() {
		if ($this->isThumbnailRequired()) {
			$ratio = ($this->width >= $this->height ? $this->width : $this->height) / SIGPLUS_THUMB_MAXSIZE;
			$thumbparams = clone $this;
			$thumbparams->width = (int)($thumbparams->width / $ratio);
			$thumbparams->height = (int)($thumbparams->height / $ratio);
			return $thumbparams;
		} else {
			return $this;
		}
	}
}

/**
* Parameter values for images galleries.
* Global values are defined in the administration back-end, which are overridden in-place with local parameter values.
*/
class SIGPlusGalleryParameters {
	public $path = null;
	/** The JavaScript lightbox engine to use, false to disable the lightbox engine, or null for default. */
	public $lightbox = null;
	/** The JavaScript image slider engine to use, false to disable the slider engine, or null for default. */
	public $slider = null;
	/** The JavaScript captions engine to use, false to disable the captions engine, or null for default. */
	public $captions = null;
	/** Unique identifier to use for gallery. */
	public $id = false;
	/** The way the gallery is rendered in HTML. */
	public $layout = 'fixed';
	/**
	* Number of preview images to display at once without scrolling.
	* A value of 0 displays all preview images without navigation controls.
	* Negative values force displaying the set number of images, regardless of the actual number
	* of images in the gallery.
	* This parameter is deprecated as of sigplus version 1.3.0.
	*/
	public $count = false;
	/** Number of rows per slider page. */
	public $rows = false;
	/** Number of columns per slider page. */
	public $cols = false;
	/** Maximum number of preview images to show in the gallery. */
	public $maxcount = 0;
	/** Width of preview images [px]. */
	public $width = 100;
	/** Height of preview images [px]. */
	public $height = 100;
	/** Whether to allow cropping images for more aesthetic thumbnails. */
	public $crop = true;
	/** JPEG quality. */
	public $quality = 85;
	/** Alignment of image gallery. */
	public $alignment = 'before';
	/** Time an image is shown before navigating to the next in a slideshow. */
	public $slideshow = 0;
	/** Orientation of image gallery viewport. */
	public $orientation = 'horizontal';
	/** Position of navigation bar. */
	public $navigation = 'bottom';
	/** Show control buttons in navigation bar. */
	public $buttons = true;
	/** Show control links in navigation bar. */
	public $links = true;
	/** Show page counter in navigation bar. */
	public $counter = true;
	/** Show overlay paging controls. */
	public $overlay = false;
	/** Time taken for the slider to move from one page to another [ms]. */
	public $duration = 800;
	/** Animation delay. */
	public $animation = 0;
	/** Position of image captions. */
	public $imagecaptions = 'overlay';
	/** Labels file name. */
	public $labels = 'labels';
	/** Default title to assign to images. */
	public $deftitle = false;
	/** Default description to assign to images. */
	public $defdescription = false;
	/** Show icon to download original image. */
	public $download = false;
	/** Show icon to display metadata information. */
	public $metadata = false;
	/** Margin [px], or false for default (inherit from sigplus.css). */
	public $margin = false;
	/** Border width [px], or false for default (inherit from sigplus.css). */
	public $borderwidth = false;
	/** Border style, or false for default (inherit from sigplus.css). */
	public $borderstyle = false;
	/** Border color as a hexadecimal value in between 000000 or ffffff inclusive, or false for default. */
	public $bordercolor = false;
	/** Padding [px], or false for default (inherit from sigplus.css). */
	public $padding = false;
	/** Sort criterion. */
	public $sortcriterion = SIGPLUS_SORT_LABELS_OR_FILENAME;
	/** Sort order, ascending or descending. */
	public $sortorder = SIGPLUS_ASCENDING;
	/** Depth limit for scanning directory hierarchies recursively. Use -1 to set no recursion limit. */
	public $depth = 0;
	/** How to link gallery to document. */
	public $linkage = 'inline';
	/** Whether to require Joomla authentication to view images. */
	public $authentication = false;
	/** Whether to create watermarked images. */
	public $watermark = false;
	/** Whether to use progressive loading. */
	public $progressive = true;
	/** Unique identifier of the gallery to link to. */
	public $link = false;
	/** One-based index of representative image in the gallery. */
	public $index = 1;

	/** Custom settings. */
	public $settings = false;

	public $lightbox_params = array();
	public $slider_params = array();
	public $captions_params = array();
	public $watermark_params = array();

	public function __set($name, $value) {
		// ignore unrecognized name/value pairs
	}

	/**
	* True if the parameters involve a random element.
	* Galleries with a random element cannot be cached.
	*/
	public function hasRandom() {
		return $this->sortcriterion == SIGPLUS_SORT_RANDOM || $this->sortcriterion == SIGPLUS_SORT_RANDOMLABELS;
	}

	private function getDefaultSlider() {
		return 'boxplus.slider';
	}

	protected static function as_nonnegative_integer($value) {
		if (is_null($value) || $value === '') {
			return false;
		} elseif ($value !== false) {
			$value = (int) $value;
			if ($value <= 0) {
				$value = 0;
			}
		}
		return $value;
	}
	
	/**
	* Casts a value to a CSS dimension measure with a unit.
	*/
	protected static function as_css_measure($value) {
		if (!isset($value) || $value === false) {
			return false;
		} elseif (is_numeric($value)) {
			return $value.'px';
		} elseif (preg_match('#^(?:(?:(?:0|[1-9][0-9]*)(?:[.][0-9]+)?(?:%|in|[cm]m|e[mx]|p[tcx])|0)\\b\\s*){1,4}$#', $value)) {  // "1px" or "1px 2em" or "1px 2em 3pt" or "1px 2em 3pt 4cm" or "1px 0 0 4cm"
			return $value;
		} else {
			return 0;
		}
	}
	
	/**
	* Enforces that parameters are of the valid type and value.
	*/
	private function validate() {
		if (is_string($this->settings)) {
			$params = string_to_array($this->settings);
			$this->settings = false;
			if ($params !== false) {
				$this->setValues($params);
			}
		}
		
		if (isset($this->path)) {
			$this->path = (string)$this->path;
		}

		// get engines to use
		if (isset($this->lightbox)) {
			switch ($this->lightbox) {
				case false: case 'none': $this->lightbox = false; break;
				case 'default':          $this->lightbox = null; break;
			}
		}
		if (isset($this->slider)) {
			switch ($this->slider) {
				case false: case 'none': $this->slider = false; break;
				case 'default':          $this->slider = null; break;
			}
		}
		if (isset($this->captions)) {
			switch ($this->captions) {
				case false: case 'none': $this->captions = false; break;
				case 'default':          $this->captions = null; break;
			}
		}

		// gallery layout, desired thumbnail count, dimensions and other thumbnail properties
		switch ($this->layout) {
			case 'hidden':
				$this->captions = false;
				// fall through
			case 'flow':
				$this->slider = false;
				$this->rows = false;
				$this->cols = false;
				break;
			default:  // case 'fixed':
				$this->layout = 'fixed';
				$this->rows = self::as_nonnegative_integer($this->rows);
				if ($this->rows < 1) {
					$this->rows = 1;
				}
				$this->cols = self::as_nonnegative_integer($this->cols);
				if ($this->cols < 1) {
					$this->cols = 1;
				}
		}
		$language = JFactory::getLanguage();
		$this->alignment = str_replace(array('lang','langinv'), array('before','after'), $this->alignment);  // compatibility
		switch ($this->alignment) {
			case 'before':  // 'left' (LTR) or 'right' (RTL) depending on language
			case 'after':   // 'right' (LTR) or 'left' (RTL) depending on language
			case 'before-clear': case 'after-clear':
			case 'before-float': case 'after-float':
			case 'center':
			case 'left': case 'right':  // 'left' or 'right' independent of language
			case 'left-clear': case 'right-clear':
			case 'left-float': case 'right-float':
				break;
			default:
				$this->alignment = 'before';
		}
		$this->maxcount = self::as_nonnegative_integer($this->maxcount);
		$this->width = (int) $this->width;
		if ($this->width <= 0) {
			$this->width = 200;
		}
		$this->height = (int) $this->height;
		if ($this->height <= 0) {
			$this->height = 200;
		}
		if ($this->crop) {
			$this->crop = true;
		} else {
			$this->crop = false;
		}
		$this->quality = (int) $this->quality;
		if ($this->quality < 0) {
			$this->quality = 0;
		}
		if ($this->quality > 100) {
			$this->quality = 100;
		}

		// lightbox properties
		$this->slideshow = (int) $this->slideshow;
		if ($this->slideshow < 0) {
			$this->slideshow = 0;
		}

		// image slider alignment, navigation bar positioning, and navigation control settings
		switch ($this->orientation) {
			case 'horizontal': case 'vertical': break;
			default: $this->orientation = 'horizontal';
		}
		switch ($this->navigation) {
			case 'top': case 'bottom': case 'both': break;
			default: $this->navigation = 'bottom';
		}
		$this->buttons = (bool) $this->buttons;
		$this->links = (bool) $this->links;
		$this->counter = (bool) $this->counter;
		$this->overlay = (bool) $this->overlay;

		// miscellaneous visual clues for the image slider
		$this->duration = self::as_nonnegative_integer($this->duration);
		$this->animation = self::as_nonnegative_integer($this->animation);

		// image labeling
		switch ($this->imagecaptions) {
			case 'above': case 'below': case 'overlay': break;
			default: $this->imagecaptions = 'overlay';
		}
		$this->labels = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace('.', '_', $this->labels));
		$this->download = (bool) $this->download;
		$this->metadata = (bool) $this->metadata;

		// image styling
		$this->margin = self::as_css_measure($this->margin);
		$this->borderwidth = self::as_css_measure($this->borderwidth);
		switch ($this->borderstyle) {
			case 'none': case 'dotted': case 'dashed': case 'solid': case 'double': case 'groove': case 'ridge': case 'inset': case 'outset': break;
			default: $this->borderstyle = false;
		}
		if (is_null($this->bordercolor) || $this->bordercolor === '' || $this->bordercolor !== false && !preg_match('/^[0-9A-Fa-f]{6}$/', $this->bordercolor)) {
			$this->bordercolor = false;
		}
		$this->padding = self::as_css_measure($this->padding);

		// sort criterion and sort order
		if (is_numeric($this->sortcriterion)) {
			$this->sortcriterion = (int) $this->sortcriterion;
		} else {
			switch ($this->sortcriterion) {
				case 'labels':
				case 'labels-filename':
				case 'labels-fname':
					$this->sortcriterion = SIGPLUS_SORT_LABELS_OR_FILENAME; break;
				case 'labels-mtime':
					$this->sortcriterion = SIGPLUS_SORT_LABELS_OR_MTIME; break;
				case 'filename':
				case 'fname':
					$this->sortcriterion = SIGPLUS_SORT_FILENAME; break;
				case 'mtime':
					$this->sortcriterion = SIGPLUS_SORT_MTIME; break;
				case 'random':
					$this->sortcriterion = SIGPLUS_SORT_RANDOM; break;
				case 'randomlabels':
					$this->sortcriterion = SIGPLUS_SORT_RANDOMLABELS; break;
				default:
					$this->sortcriterion = SIGPLUS_SORT_LABELS_OR_FILENAME;
			}
		}
		if (is_numeric($this->sortorder)) {
			$this->sortorder = (int) $this->sortorder;
			switch ($this->sortorder) {
				case SIGPLUS_DESCENDING:
					$this->sortorder = SIGPLUS_DESCENDING; break;
				case SIGPLUS_ASCENDING:
				default:
					$this->sortorder = SIGPLUS_ASCENDING;
			}
		} else {
			switch ($this->sortorder) {
				case 'asc':  case 'ascending':  $this->sortorder = SIGPLUS_ASCENDING;  break;
				case 'desc': case 'descending': $this->sortorder = SIGPLUS_DESCENDING; break;
				default:           $this->sortorder = SIGPLUS_ASCENDING;
			}
		}
		$this->depth = (int) $this->depth;
		if ($this->depth < -1) {  // -1 for recursive listing with no limit, 0 for flat listing (no subdirectories), >0 for recursive listing with limit
			$this->depth = 0;
		}

		// miscellaneous advanced settings
		switch ($this->linkage) {
			case 'inline': case 'head': case 'external': break;
			default: $this->linkage = 'inline';
		}
		$this->authentication = (bool) $this->authentication;
		$this->watermark = (bool) $this->watermark;
		$this->progressive = (bool) $this->progressive;

		$this->index = self::as_nonnegative_integer($this->index);

		// deprecated parameters
		if ($this->count !== false) {
			$this->count = (int) $this->count;
			if ($this->count < 0) {  // disable slider and set maximum number of thumbnails to show
				$this->maxcount = -$this->count;
				$this->rows = 1;
				$this->cols = $this->maxcount;
			} elseif ($this->count > 0) {  // set rows and columns automatically
				switch ($this->orientation) {
					case 'vertical':
						$this->rows = $this->count;
						$this->cols = 1;
						break;
					default:  // case 'horizontal':
						$this->rows = 1;
						$this->cols = $this->count;
				}
			} elseif ($this->layout != 'hidden') {
				$this->layout = 'flow';
				$this->rows = false;
				$this->cols = false;
				$this->slider = false;
			}
			$this->count = false;
		}

		// resolve parameter incompatibilities
		if ($this->layout == 'fixed' && !$this->slider) {  // fixed layout requires slider
			$this->slider = $this->getDefaultSlider();
			$this->buttons = false;
			$this->links = false;
			$this->counter = false;
		}
	}

	/**
	* Set parameters based on Joomla parameter object.
	*/
	public function setParameters($params) {
		$substitute = array(
			// class property aliases in XML
			'maxcount' => 'thumb_count',
			'width' => 'thumb_width',
			'height' => 'thumb_height',
			'crop' => 'thumb_crop',
			'quality' => 'thumb_quality',
			'slideshow' => 'lightbox_slideshow',
			'orientation' => 'slider_orientation',
			'navigation' => 'slider_navigation',
			'buttons' => 'slider_buttons',
			'links' => 'slider_links',
			'counter' => 'slider_counter',
			'overlay' => 'slider_overlay',
			'duration' => 'slider_duration',
			'animation' => 'slider_animation',
			'deftitle' => 'caption_title',
			'defdescription' => 'caption_description',
			'borderwidth' => 'border_width',
			'borderstyle' => 'border_style',
			'bordercolor' => 'border_color',
			'sortcriterion' => 'sort_criterion',
			'sortorder' => 'sort_order',

			// class properties to skip
			'id' => false,
			'lightbox_params' => false,
			'slider_params' => false,
			'captions_params' => false,
			'watermark_params' => false);

		foreach (get_class_vars(__CLASS__) as $name => $value) {  // enumerate properties in class
			if (isset($substitute[$name])) {
				$alias = $substitute[$name];
				if ($alias !== false) {
					$this->$name = self::getParameterValue($params, $alias, $value);
				}
			} else {
				$this->$name = self::getParameterValue($params, $name, $value);  // set property class value as default if not present in XML
			}
		}

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

	/**
	* Return the natural typed representation of a value, guessing at its type.
	*/
	private function getValue($value) {
		if (ctype_digit($value)) {  // digits only, treat as integer
			return (int) $value;
		} elseif (is_numeric($value)) {  // can represent a number, treat as floating-point
			return (float) $value;
		} else {
			switch (strtolower($value)) {  // check for boolean values
				case 'true': return true;
				case 'false': return false;
				case 'null': return null;
			}
			return $value;
		}
	}

	private function setValues(array $params) {
		foreach ($params as $key => $value) {
			if (ctype_alpha($key)) {  // ignore keys that are not valid PHP identifiers
				$this->$key = $value;
			} elseif (strpos($key, ':') !== false) {  // contains special instruction for pop-up window, slider or captions engine
				list($engine, $key) = explode(':', $key, 2);
				switch ($engine) {
					case 'mobile':  // settings for mobile devices
						if (SIGPlusUserAgent::handheld()) {
							$this->$key = $value;  // override values set for desktop computers
						}
						break;
					case 'lightbox':
						$this->lightbox_params[$key] = $this->getValue($value); break;
					case 'slider':
						$this->slider_params[$key] = $this->getValue($value); break;
					case 'captions':
						$this->captions_params[$key] = $this->getValue($value); break;
					case 'watermark':
						$this->watermark_params[$key] = $this->getValue($value); break;
				}
			}
		}
	}

	public function setArray(array $params) {
		$this->setValues($params);
		$this->validate();
	}

	/**
	* Set parameters based on a string with whitespace-delimited "key=value" pairs.
	*/
	public function setString($paramstring) {
		$params = string_to_array($paramstring);
		if ($params !== false) {
			$this->setArray($params);
		}
	}
}
