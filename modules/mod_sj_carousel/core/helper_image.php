<?php
/**
 * @package Sj Carousel 
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;

class ImageHelper{
	protected static $instances = array();
	protected static $defaults = array(
			'type' => null,
			'width' => null,
			'height' => null,
			'quality' => 90,
			'function' => null,
			'function_mode' => null,
			'transparency' => true,
			'background' => array('r'=>255, 'g'=>255, 'b'=>255),
			'cache' => null,
			'cache_url' => null
	);
	public static $supports = array(
			IMAGETYPE_PNG,
			IMAGETYPE_GIF,
			IMAGETYPE_JPEG
	);
	public static $functions = array(
			'resize',
			'crop',
			'rotate',
			'flip_horizontal',
			'flip_vertical'
	);
	public static $modes = array(
			'center',
			'fill',
			'fit',
			'stretch'
	);

	protected $image = null;
	protected $in = null;
	protected $out = null;
	protected $errors = array();
	protected $options = null;
	protected $_process = 1;
	protected $debug = false;
	/**
	 *
	 * @param mixed $image
	 * @param array $options
	 * @return ImageHelper
	 */
	public static function init($image, $options = array()){
		$cacheid = count(self::$instances);
		if (!isset(self::$instances[$cacheid])){
			self::$instances[$cacheid] = new ImageHelper($image, $options);
		}
		return self::$instances[$cacheid];
	}

	/**
	 * ImageHelper constructor
	 * @param unknown_type $image
	 * @param unknown_type $options
	 */
	public function __construct($image = null, $options = array()){
		!is_null($image) && $this->image = $image;

		// defaults
		foreach (ImageHelper::$defaults as $name => $value){
			$this->options[$name] = array_key_exists($name, $options) ? $options[$name] : $value;
		}
		$this->in = array(
				'type'=>null,
				'src' =>null
		);
		$this->out = array();
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::__construct');
	}

	public static function setDefault($params = null){
		self::$defaults = array(
				'type' => null,
				'width' => null,
				'height' => null,
				'quality' => 90,
				'function' => null,
				'function_mode' => null,
				'transparency' => true,
				'background' => array('r'=>255, 'g'=>255, 'b'=>255),
				'cache' => null,
				'cache_url' => null
		);

		if ($params instanceof JRegistry){
			$opts = array();

			// output type ?
			$imgcfg_type = $params->get('imgcfg_type', 0);
			if (in_array($imgcfg_type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))){
				$opts['type'] = $imgcfg_type;
			}

			// function ?
			$imgcfg_fn = $params->get('imgcfg_function', 'none_resize');
			$imgcfg_fn = explode('_', $imgcfg_fn);
			if ( in_array($imgcfg_fn[0], self::$functions) ){
				$opts['function'] = $imgcfg_fn[0];
				if ($imgcfg_fn[0] == 'resize'){
					if ( isset($imgcfg_fn[1]) && in_array($imgcfg_fn[1], self::$modes) ){
						$opts['function_mode'] = $imgcfg_fn[1];
					} else {
						$opts['function_mode'] = self::$modes[2];
					}
				}

			}

			// background
			$imgcfg_background = $params->get('imgcfg_background', null);
			if (!is_null($imgcfg_background)){
				$opts['background'] = $imgcfg_background;
			}

			// quality
			$imgcfg_quality = $params->get('imgcfg_quality', null);
			if ( $imgcfg_quality != null && intval($imgcfg_quality) > 0 && intval($imgcfg_quality) < 100 ){
				$opts['quality'] = intval($imgcfg_quality);
			}

			// transparency
			$imgcfg_transparency = $params->get('imgcfg_transparency', 1);
			if ( $imgcfg_transparency != null){
				$opts['transparency'] = $imgcfg_transparency ? true : false;
			}

			// dimension ?
			$imgcfg_w = $params->get('imgcfg_width', 0);
			$imgcfg_h = $params->get('imgcfg_height', 0);
			if ( intval($imgcfg_w) > 0 ){
				$opts['width'] = $imgcfg_w;
			}
			if ( intval($imgcfg_h) > 0 ){
				$opts['height'] = $imgcfg_h;
			}

			// cache ?
			if ( defined('JPATH_CACHE') ){
				$cache_path_default = JPATH_CACHE.'/resized';
				$cache_path_url_default = 'cache/resized/';
			} else {
				$cache_path_default = null;
				$cache_path_url_default = null;
			}
			$cache_path = $params->get('imgcfg_cache', $cache_path_default);
			$cache_url  = $params->get('imgcfg_cache_url', $cache_path_url_default);
			if ($cache_path) $opts['cache'] = $cache_path;
			if ($cache_url) $opts['cache_url'] = $cache_url;

		}
		self::$defaults = array_merge(self::$defaults, $opts);
	}

	public static function testDefault(){
		echo 'ImageHelper::$defaults'.'<br>';
		var_dump(self::$defaults);
	}

	/**
	 * Get image type for input url or path of file.
	 * @return mixed
	 */
	protected function getInputType(){
		if ( !isset($this->in['type']) ){
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getInputType');
			if (is_string($this->image)){
				$path = $this->image;
			} else {
				$path = @$this->image['src'];
			}

			if (is_string($path)){
				$ps = JString::parse_url($path);
				if ( array_key_exists('path', $ps) && !empty($ps['path']) ){
					$isHttp = isset($ps['scheme']) && in_array($ps['scheme'], array('http', 'https'));
					if (!$isHttp || JURI::isInternal($path)){
						$path = $ps['path'];
					} else {
						// is extenal url.
						$this->in['type'] = 'url';
						$this->in['src'] = $path;
						return $this->in['type'];
					}
				}
			} else if (!$path){
				$this->errors[] = 'Image path must be string or stored in array_argument[src]!';
			}
			//var_dump('Path: ', $path);
			if ( !file_exists($path) ){
				$this->errors[] = 'Image path is not exists!';
			} else {
				//var_dump('EXISTS');
				$this->in['src'] = realpath($path);
				$infor = array();
				if ( !function_exists('exif_imagetype') ){
					$intype = exif_imagetype($path);
				} else {
					$infor = getimagesize($path);
					$intype= @$infor[2];
				}
				switch ($intype){
					case IMAGETYPE_PNG:
						$this->in['type'] = IMAGETYPE_PNG;
						break;
					case IMAGETYPE_GIF:
						$this->in['type'] = IMAGETYPE_GIF;
						break;
					case IMAGETYPE_JPEG:
						$this->in['type'] = IMAGETYPE_JPEG;
						break;
					default:
				}
			}
		}
		return $this->in['type'];
	}

	protected function getInputResource(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getInputResource');
		//var_dump('getInputResource: '.$this->in['src']);
		if ( !isset($this->in['image']) ){
			$intype = $this->getInputType();
			switch ($intype){
				case IMAGETYPE_PNG:
					$this->in['image'] = imagecreatefrompng($this->in['src']);
					break;
				case IMAGETYPE_GIF:
					$this->in['image'] = imagecreatefromgif($this->in['src']);
					break;
				case IMAGETYPE_JPEG:
					$this->in['image'] = imagecreatefromjpeg($this->in['src']);
					break;
				default:
					$this->in['image'] = null;
					return false;
			}
		}
		return is_resource($this->in['image']) ? $this->in['image'] : null;
	}

	protected function validOptions(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::validOptions');
		// TODO: validate input
		$type = $this->getInputType();
		$input_type_ok = ($type=='url') || in_array($type, ImageHelper::$supports);
		if ( !$input_type_ok ){
			$this->errors[] = 'Input type is not supported!';
			$this->_process = 0;
		} else if ($type=='url'){
			$this->_process = 0;
		}

		// required options
		$output_type_ok = !is_null($this->options['type']) && in_array($this->options['type'], ImageHelper::$supports);
		if ( !$output_type_ok ){
			if ( $input_type_ok ){
				$this->options['type'] = $type;
			}
		}

		if ( !($this->options['width']>0 || $this->options['height']>0) ){
			$this->errors[] = 'Output width or height must be greater than zero (>0)!';
			$this->_process = 0;
		}

		if ( in_array($this->options['function'], ImageHelper::$functions) ){
			if ($this->options['function'] == 'resize' && !in_array($this->options['function_mode'], ImageHelper::$modes)){
				$this->errors[] = 'Resize mode is not valid! should be in ['.implode(', ', ImageHelper::$modes).']';
				$this->_process = 0;
			}
		} else {
			$this->errors[] = 'Output function is not valid! should be in ['.implode(', ', ImageHelper::$functions).']';
			$this->_process = 0;
		}

		// auto parse options
		if ( !isset($this->options['quality']) || !is_numeric($this->options['quality']) || $this->options['quality']==0 ){
			$this->options['quality'] = 90;
		} else {
			if ($this->options['quality'] < 10){
				$this->options['quality'] = 10;
			}
			if ($this->options['quality'] > 100){
				$this->options['quality'] = 100;
			}
		}

		$c = &$this->options['background'];
		if ( is_string($this->options['background']) ){
			if ( preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/i', $this->options['background'], $m) ){
				$hex = isset($m[1]) ? $m[1] : null;
				if (strlen($hex)==3){
					$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
				}
				if ($hex){
					$dec = hexdec($hex);
					$this->options['background'] = array(
							'r' => 0xFF & ($dec >> 0x10),
							'g' => 0xFF & ($dec >> 0x8),
							'b' => 0xFF & $dec
					);
				} else {
					$this->options['background'] = null;
				}
			} else {
				$this->options['background'] = null;
			}
		}
		if ( !is_array($this->options['background']) ){
			$this->options['background'] = null;
		} else {
			$valid = 1;
			$bg = $this->options['background'];
			foreach (array('r', 'g', 'b') as $e){
				if ( !array_key_exists($e, $bg) || ($bg[$e]<0) || ($bg[$e]>255) ){
					$valid = 0;
					break;
				}
			}
			if (!$valid){
				$this->options['background'] = null;
			}
		}

		if ( !is_null($this->options['cache']) ){
			if ( !@is_dir($this->options['cache']) && !@mkdir($this->options['cache']) ){
				$this->options['cache'] = __DIR__.'/resized';
			}
		} else {
			$this->options['cache'] = __DIR__.'/resized';
		}
		if ( !file_exists($this->options['cache']) && !@mkdir($this->options['cache'], 0755, true) ){
			$this->errors[] = 'Cache is not created!';
			$this->_process = 0;
		} else {
			$this->options['cache'] = realpath($this->options['cache']);
		}

		return count($this->errors) == 0;
	}

	protected function getOutputFile(){
		if ( !array_key_exists('src', $this->out) || is_null($this->out['src']) ){
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getOutputFile');
			$hash = md5( serialize( array($this->options, $this->in['src'], $this->in['type']) ) );
			if (defined('DEBUG') && 0){
				$hash = basename($this->in['src'], '.jpg');
				$w = isset($this->options['width']) ? $this->options['width'] : 0;
				$h = isset($this->options['height']) ? $this->options['height'] : 0;
				$hash .= '-'.$w.'x'.$h;
				$hash .= '-'.$this->options['function'];
				$hash .= '-'.$this->options['function_mode'];
				$hash .= '-'.$this->options['quality'];
			}
			switch ($this->options['type']){
				case IMAGETYPE_PNG:
					$ext = '.png';
					break;
				case IMAGETYPE_GIF:
					$ext = '.gif';
					break;
				case IMAGETYPE_JPEG:
					$ext = '.jpg';
					break;
				default:
					$ext = image_type_to_extension($this->options['type'], true);
			}
			$this->out['src'] = $this->options['cache'].'/'.$hash.$ext;
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getOutputFile::::'.$this->out['src']);
		}
		return $this->out['src'];
	}

	protected function getOutputUrl(){
		if (!isset($this->out['url'])){
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getOutputUrl');
			if ( $this->validOptions() && in_array($this->options['type'], ImageHelper::$supports) ){
				$this->out['url'] = $this->options['cache_url'].basename( $this->getOutputFile() );
			} else {
				$this->out['url'] = is_string($this->image) ? $this->image : @$this->image['src'];
				$this->debug && JFactory::getApplication()->enqueueMessage('Not validOptions::'.$this->out['url']);
			}
		}
		return $this->out['url'];
	}

	/**
	 * @return mixed
	 */
	public function output(){
		if ( $this->validOptions() && $this->getInputResource()){
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::output');
			$this->process();
			if (is_resource($this->out['image'])){
				$oImage = &$this->out['image'];
				switch( $this->options['type'] ){
					case IMAGETYPE_JPEG:
						imagejpeg($oImage, $this->getOutputFile(), $this->options['quality']);
						break;
					case IMAGETYPE_GIF:
						imagegif($oImage, $this->getOutputFile());
						break;
					case IMAGETYPE_PNG:
						imagepng($oImage, $this->getOutputFile(), 10 - round($this->options['quality']/10.0));
						break;
				}
			}
		}
	}

	/**
	 *@return string
	 */
	public function tag(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::tag');
		if (!$this->getOutputUrl()){
			return '';
		}
		$tag = array();
		if (is_array($this->image)){
			foreach ($this->image as $att => $val){
				if ($att == 'src'){
					$tag[] = 'src="'.$this->getOutputUrl().'"';
				} else {
					$tag[] = $att.'="'.$val.'"';
				}
			}
			if (!array_key_exists('alt', $this->image)){
				$tag[] = 'alt="'.$this->getOutputUrl().'"';
			}
		} else {
			$tag[] = 'src="'.$this->getOutputUrl().'"';
			$tag[] = 'alt="'.$this->getOutputUrl().'"';
		}
		return count($tag) ? '<img '.implode(' ', $tag).'/>' : '';
	}

	/**
	 * @return string
	 */
	public function path(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::path');
		return $this->__toString();
	}

	public function src(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::src');
		return $this->getOutputUrl();
	}


	/**
	 * @return string
	 */
	public function error(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::error');
		return $this->errors;
	}


	/**
	 * Resize input image by size
	 * @param int $w
	 * @param int $h
	 * @return ImageHelper
	 */

	public function resize($w = 0, $h = 0){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::resize');
		if ($w > 0) $this->options['width'] = $w;
		if ($h > 0) $this->options['height'] = $h;
		return $this;
	}

	protected function actionPerformed(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::actionPerformed');
		$sImage = $this->getInputResource();
		if ( !is_resource($sImage) ) return;
		$sw = imagesx($sImage);
		$sh = imagesy($sImage);
		if ( $this->options['function']=='resize' ){
			$ow = is_null($this->options['width']) ? 0 : intval($this->options['width']);
			$oh = is_null($this->options['height']) ? 0 : intval($this->options['height']);
			if ( $ow==0 && $oh==0 ){
				//die('thoi khoi resize');
			} else if ($ow == 0){
				// resize by height.
				$this->options['function_mode'] = ImageHelper::$modes[3];
				$ow = $this->options['width'] = round($oh * $sw/$sh);
			} else if ($oh == 0){
				$this->options['function_mode'] = ImageHelper::$modes[3];
				$oh = $this->options['height'] = (int)round($ow * $sh/$sw);
				// resize by width
			} else if ( abs($oh-round($ow*$sh/$sw)) <= 1 ){
				// resize by ca hai
				$this->options['function_mode'] = ImageHelper::$modes[3];
				$oh = $this->options['height'] = round($ow*$sh/$sw);
			}
			$oImage = $this->getCanvas();
			// build agrs
			$sx = $sy = 0;
			$dx = $dy = 0;
			$dw = $sw;
			$dh = $sh;
			switch($this->options['function_mode']){
				case ImageHelper::$modes[0]:
					if ($ow > $dw) {
						$dx = ($ow-$dw)/2;
					}
					if ($oh > $dh) {
						$dy = ($oh-$dh)/2;
					}
					if ($sw > $ow){
						$sx = ($sw-$ow)/2;
					}
					if ($sh > $oh){
						$sy = ($sh-$oh)/2;
					}
					break;
				case ImageHelper::$modes[1]:
					$or = 1.0*$sw/$sh;
					if ($ow < $oh*$or){
						$dw = round($oh*$or);
						$dh = $oh;
						$dx = ($ow-$dw)/2;
						$dy = 0;
					} else {
						$dw = $ow;
						$dh = round($ow/$or);
						$dx = 0;
						$dy = ($oh-$dh)/2;
					}
					break;
				case ImageHelper::$modes[2]:
					$or = 1.0*$sw/$sh;
					if ($ow > $oh*$or){
						$dw = round($oh*$or);
						$dh = $oh;
						$dx = ($ow-$dw)/2;
						$dy = 0;
					} else {
						$dw = $ow;
						$dh = round($ow/$or);
						$dx = 0;
						$dy = ($oh-$dh)/2;
					}
					break;
				default:
				case ImageHelper::$modes[3]:
					$dw = $ow;
					$dh = $oh;
					break;
			}

			imagecopyresampled(
			$oImage,
			$sImage,
			$dx, $dy, $sx, $sy,
			$dw, $dh, $sw, $sh
			);

			$this->out['image'] = $oImage;
		}
		return 1;
	}

	public function __destruct(){
		$this->save2f();
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::__destruct');
	}

	protected function save2f(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::save2f');
		if ($this->_process && ($of = $this->getOutputFile()) && !file_exists($of)){
			$this->actionPerformed();
			if ( array_key_exists('image', $this->out) && is_resource($this->out['image'])){
				$oImage = &$this->out['image'];
				switch( $this->options['type'] ){
					case IMAGETYPE_JPEG:
						imagejpeg($oImage, $of, $this->options['quality']);
						break;
					case IMAGETYPE_GIF:
						imagegif($oImage, $of);
						break;
					case IMAGETYPE_PNG:
						imagepng($oImage, $of, 10 - round($this->options['quality']/10.0));
						break;
				}
			}
		}
	}

	protected function getCanvas(){
		if ( !isset($this->out['image']) ){
			$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::getCanvas');
			$oImage = imagecreatetruecolor($this->options['width'], $this->options['height']);
			if ($this->options['transparency'] && in_array($this->options['type'], array(IMAGETYPE_GIF, IMAGETYPE_PNG))){
				if (IMAGETYPE_GIF == $this->in['type']){
					$trans_index = imagecolortransparent($this->in['image']);
					if ($trans_index >= 0) {
						$trans_color = imagecolorsforindex($this->in['image'], $trans_index);
						$trans_index = imagecolorallocate($oImage, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
						imagefill($oImage, 0, 0, $trans_index);
						imagecolortransparent($oImage, $trans_index);
					}
				} else if (IMAGETYPE_PNG == $this->in['type']){
					$png_alpha = ((ord(file_get_contents($this->in['src'], false, null, 25, 1)) & 6) & 4) == 4;
					if ($png_alpha){
						imagealphablending($oImage, true);
						$transparent = imagecolorallocatealpha($oImage, 0, 0, 0, 127);
						imagefilledrectangle($oImage, 0, 0, $this->options['width'], $this->options['height'], $transparent);
						imagealphablending($oImage, false);
						imagesavealpha($oImage, true);
					}
				}
			} else if ( !is_null($this->options['background'])
					&& $this->options['function']==ImageHelper::$functions[0]
					&& in_array($this->options['function_mode'], array(ImageHelper::$modes[0], ImageHelper::$modes[2]))){
				$bgc = imagecolorallocate(
						$oImage,
						$this->options['background']['r'],
						$this->options['background']['g'],
						$this->options['background']['b']
				);
				imagefilledrectangle($oImage, 0, 0, $this->options['width'], $this->options['height'], $bgc);
			}
			$this->out['image'] = $oImage;
		}
		return $this->out['image'];
	}

	public function __toString(){
		$this->debug && JFactory::getApplication()->enqueueMessage('ImageHelper::__toString');
		$f = $this->getOutputFile();
		return is_null($f) ? '' : $f;
	}




}