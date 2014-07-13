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

JLoader::register('ImageHelper', dirname(__FILE__).'/helper_image.php');

if (!class_exists('BaseHelper')){
	/**
	 * BaseHelper for com_content only.
	 */
	abstract class BaseHelper{
		/**
		 * Cache all image path or url
		 * @var array
		 */
		protected static $image_cache = array();
		


		/**
		 * strips all tag, except a, em, strong
		 * @param string $text
		 * @return string
		*/
		public function _cleanText($text){
			$text = str_replace('<p>', ' ', $text);
			$text = str_replace('</p>', ' ', $text);
			$text = strip_tags($text, '<a><em><strong>');
			$text = trim($text);
			return $text;
		}

		/**
		 * Parse and build target attribute for links.
		 * @param string $value (_self, _blank, _windowopen, _modal)
		 * _blank 	Opens the linked document in a new window or tab
		 * _self 	Opens the linked document in the same frame as it was clicked (this is default)
		 * _parent 	Opens the linked document in the parent frame
		 * _top 	Opens the linked document in the full body of the window
		 * _windowopen  Opens the linked document in a Window
		 * _modal		Opens the linked document in a Modal Window
		 */
		public static function parseTarget($type='_self'){
			$target = '';
			switch($type){
				default:
				case '_self':
					break;
				case '_blank':
				case '_parent':
				case '_top':
					$target = 'target="'.$type.'"';
					break;
				case '_windowopen':
					$target = "onclick=\"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,false');return false;\"";
					break;
				case '_modal':
					// user process
					break;
			}
			return $target;
		}

		/**
		 * Truncate string by $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		public static function truncate($string, $length, $etc='...'){
			return defined('MB_OVERLOAD_STRING')
			? self::_mb_truncate($string, $length, $etc)
			: self::_truncate($string, $length, $etc);
		}

		/**
		 * Truncate string if it's size over $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		private static function _truncate($string, $length, $etc='...'){
			if ($length>0 && $length<strlen($string)){
				$buffer = '';
				$buffer_length = 0;
				$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
				$self_closing_tag = split(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
				$open = array();

				foreach($parts as $i => $s){
					if( false===strpos($s, '<') ){
						$s_length = strlen($s);
						if ($buffer_length + $s_length < $length){
							$buffer .= $s;
							$buffer_length += $s_length;
						} else if ($buffer_length + $s_length == $length) {
							if ( !empty($etc) ){
								$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
							}
							break;
						} else {
							$words = preg_split('/([^\s]*)/', $s, - 1, PREG_SPLIT_DELIM_CAPTURE);
							$space_end = false;
							foreach ($words as $w){
								if ($w_length = strlen($w)){
									if ($buffer_length + $w_length < $length){
										$buffer .= $w;
										$buffer_length += $w_length;
										$space_end = (trim($w) == '');
									} else {
										if ( !empty($etc) ){
											$more = $space_end ? $etc : " $etc";
											$buffer .= $more;
											$buffer_length += strlen($more);
										}
										break;
									}
								}
							}
							break;
						}
					} else {
						preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
						//$tagclose = isset($m[1]) && trim($m[1])=='/';
						if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
							array_push($open, $m[2]);
						} else if (trim($m[1])=='/') {
							$tag = array_pop($open);
							if ($tag != $m[2]){
								// uncomment to to check invalid html string.
								// die('invalid close tag: '. $s);
							}
						}
						$buffer .= $s;
					}
				}
				// close tag openned.
				while(count($open)>0){
					$tag = array_pop($open);
					$buffer .= "</$tag>";
				}
				return $buffer;
			}
			return $string;
		}

		/**
		 * Truncate mutibyte string if it's size over $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		private static function _mb_truncate($string, $length, $etc='...'){
			$encoding = mb_detect_encoding($string);
			if ($length>0 && $length<mb_strlen($string, $encoding)){
				$buffer = '';
				$buffer_length = 0;
				$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
				$self_closing_tag = explode(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
				$open = array();

				foreach($parts as $i => $s){
					if (false === mb_strpos($s, '<')){
						$s_length = mb_strlen($s, $encoding);
						if ($buffer_length + $s_length < $length){
							$buffer .= $s;
							$buffer_length += $s_length;
						} else if ($buffer_length + $s_length == $length) {
							if ( !empty($etc) ){
								$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
							}
							break;
						} else {
							$words = preg_split('/([^\s]*)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
							$space_end = false;
							foreach ($words as $w){
								if ($w_length = mb_strlen($w, $encoding)){
									if ($buffer_length + $w_length < $length){
										$buffer .= $w;
										$buffer_length += $w_length;
										$space_end = (trim($w) == '');
									} else {
										if ( !empty($etc) ){
											$more = $space_end ? $etc : " $etc";
											$buffer .= $more;
											$buffer_length += mb_strlen($more);
										}
										break;
									}
								}
							}
							break;
						}
					} else {
						preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
						//$tagclose = isset($m[1]) && trim($m[1])=='/';
						if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
							array_push($open, $m[2]);
						} else if (trim($m[1])=='/') {
							$tag = array_pop($open);
							if ($tag != $m[2]){
								// uncomment to to check invalid html string.
								// die('invalid close tag: '. $s);
							}
						}
						$buffer .= $s;
					}
				}
				// close tag openned.
				while(count($open)>0){
					$tag = array_pop($open);
					$buffer .= "</$tag>";
				}
				return $buffer;
			}
			return $string;
		}

		/**
		 * First image in list of images for Article
		 * @param object $item is an Article of Content
		 * @param unknown_type $params
		 * @return Ambigous <NULL, unknown>
		 */
		public static function getAImage($item, $params, $prefix='imgcfg'){
			$images = &self::getAImages($item, $params, $prefix);
			return is_array($images) && count($images) ? $images[0] : null;
		}

		/**
		 *
		 * @param unknown_type $item
		 * @param unknown_type $params
		 */
		public static function getAImages($item, $params, $prefix='imgcfg'){
			$hash = md5( serialize(array($params, 'article')) );
			if ( !isset(self::$image_cache[$hash][$item->id]) ){
				$defaults = array(
						'external'			=> 1,
						'image_intro'		=> 1,
						'inline_introtext'	=> 1,
						'image_fulltext'	=> 1,
						'inline_fulltext'	=> 1
				);

				$images_path = array();
				$priority = preg_split('/[\s|,|;]/', $params->get($prefix.'_order', 'external, imagE_intro,inline_introtext,image_fulltext,inline_fulltext'), -1, PREG_SPLIT_NO_EMPTY);
				if ( count($priority) > 0 ){
					$priority = array_map('strtolower', $priority);
					$mark = array();

					for($i=0; $i<count($priority); $i++){
						$type = $priority[$i];
						if ( array_key_exists($type, $defaults) )
							unset($defaults[ $type ]);
						if ( $params->get($prefix.'_from_'.$type, 1) )
							$mark[ $type ] = 1;
					}
				}

				foreach($defaults as $type => $val){
					if ( $params->get($prefix.'_from_'.$type, 1) )
						$mark[ $type ] = 1;
				}

				if ( count($mark) > 0 ){
					// prepare data.
					$images_data = null;
					if (array_key_exists('image_intro', $mark) || array_key_exists('image_fulltext', $mark)){
						$images_data = json_decode($item->images, true);
					}

					foreach($mark as $type => $true){
						switch ($type){
							case 'image_intro':
							case 'image_fulltext':
								if ( isset($images_data) && isset($images_data[$type]) && !empty($images_data[$type])){
									$image = array(
											'src' => $images_data[$type]
									);
									if (array_key_exists($type.'_alt', $images_data)){
										$image['alt'] = $images_data[$type.'_alt'];
									}
									if (array_key_exists($type.'_caption', $images_data)){
										/* $image['class'] = 'caption'; */
										$image['title'] = $images_data[$type.'_caption'];
									}
									array_push($images_path, $image);
								}
								break;
							case 'inline_introtext':
								$text = $item->introtext;
							case 'inline_fulltext':
								if ($type == 'inline_fulltext'){
									$text = $item->fulltext;
								}
								$inline_images = self::getInlineImages($text);
								for ($i=0; $i<count($inline_images); $i++){
									array_push($images_path, $inline_images[$i]);
								}
								break;
									
							case 'external':
								$exf = $params->get($prefix.'_external_url', 'images/article/{id}/');
								preg_match_all('/{([a-zA-Z0-9_]+)}/', $exf, $m);
								if ( count($m)==2 && count($m[0])>0 ){
									$compat = 1;
									foreach ($m[1] as $property){
										!property_exists($item, $property) && ($compat=0);
									}
									if ($compat){
										$replace = array();
										foreach ($m[1] as $property){
											$replace[] = is_null($item->$property) ? '' : $item->$property;
										}
										$exf = str_replace($m[0], $replace, $exf);
									}
								}
								$files = self::getExternalImages($exf);
								for ($i=0; $i<count($files); $i++){
									array_push($images_path, array('src'=>$files[$i]));
								}
								break;
							default:
								break;
						}
					}
				}
					
				if ( count($images_path) == 0 && $params->get($prefix.'_placeholder', 1)==1){
					$images_path[] = array('src'=> $params->get($prefix.'_placeholder_path', null), 'class'=>'placeholder');
				}
					
				self::$image_cache[$hash][$item->id] = $images_path;
			}
			return self::$image_cache[$hash][$item->id];
		}

		public static function getCImage($item, $params, $prefix='imgcfg'){
			$images = &self::getCImages($item, $params, $prefix);
			return is_array($images) && count($images) ? $images[0] : null;
		}

		public static function getCImages($item, $params, $prefix='imgcfg'){
			$hash = md5( serialize(array($params, 'category')) );
			if ( !isset(self::$image_cache[$hash][$item->id]) ){
				$defaults = array(
						'external'		=> 1,
						'params'		=> 1,
						'description'	=> 1
				);
				$images_path = array();
				$priority = preg_split('/[\s|,|;]/', $params->get($prefix.'_order', 'external, params, description'), -1, PREG_SPLIT_NO_EMPTY);
				if ( count($priority) > 0 ){
					$priority = array_map('strtolower', $priority);
					$mark = array();

					for($i=0; $i<count($priority); $i++){
						$type = $priority[$i];
						if ( array_key_exists($type, $defaults) )
							unset($defaults[ $type ]);
						if ( $params->get($prefix.'_from_'.$type, 1) )
							$mark[ $type ] = 1;
					}
				}
				foreach($defaults as $type => $val){
					if ( $params->get($prefix.'_from_'.$type, 1) )
						$mark[ $type ] = 1;
				}
				if ( count($mark) > 0 ){
					$cparams = null;
					if (array_key_exists('params', $mark)){
						$cparams = new JRegistry;
						$cparams->loadString($item->params);
					}

					foreach($mark as $type => $true){
						switch ($type){
							case 'params':
								if ( $cparams instanceof JRegistry && $cparams->get('image') ){
									$image = array(
											'src' => $cparams->get('image')
									);
									array_push($images_path, $image);
								}
								break;
							case 'description':
								$inline_images = self::getInlineImages($item->description);
								for ($i=0; $i<count($inline_images); $i++){
									array_push($images_path, $inline_images[$i]);
								}
								break;
									
							case 'external':
								$exf = $params->get($prefix.'_external_url', 'images/category/{id}/');
								preg_match_all('/{([a-zA-Z0-9_]+)}/', $exf, $m);
								if ( count($m)==2 && count($m[0])>0 ){
									$compat = 1;
									foreach ($m[1] as $property){
										!property_exists($item, $property) && ($compat=0);
									}
									if ($compat){
										$replace = array();
										foreach ($m[1] as $property){
											$replace[] = is_null($item->$property) ? '' : $item->$property;
										}
										$exf = str_replace($m[0], $replace, $exf);
									}
								}
								$files = self::getExternalImages($exf);
								for ($i=0; $i<count($files); $i++){
									array_push($images_path, array('src'=>$files[$i]));
								}
								break;
							default:
								break;
						}
					}
				}
					
				if ( count($images_path) == 0 && $params->get($prefix.'_placeholder', 1)==1){
					$images_path[] = array('src'=> $params->get($prefix.'_placeholder_path', null), 'class'=>'placeholder');
				}
					
				self::$image_cache[$hash][$item->id] = $images_path;
			}
			return self::$image_cache[$hash][$item->id];
		}


		/**
		 * Get all image url|path in $text.
		 * @param string $text
		 * @return string
		 */
		public static function getInlineImages($text){
			$images = array();
			$searchTags = array(
					'img'	=> '/<img[^>]+>/i',
					'input'	=> '/<input[^>]+type\s?=\s?"image"[^>]+>/i'
			);
			foreach ($searchTags as $tag => $regex){
				preg_match_all($regex, $text, $m);
				if ( is_array($m) && isset($m[0]) && count($m[0])){
					foreach ($m[0] as $htmltag){
						$tmp = JUtility::parseAttributes($htmltag);
						if ( isset($tmp['src']) ){
							if ($tag == 'input'){
								array_push( $images, array('src' => $tmp['src']) );
							} else {
								array_push( $images, $tmp );
							}
						}
					}
				}
			}
			return $images;
		}
		public static function curlHeaderCallBack(){
			//var_dump('Call '.__METHOD__);
			//var_dump( func_get_args() );
		}
		public static function isValidUrl($url){
			JFactory::getApplication()->enqueueMessage('isValidUrl:'.$url);
			$http = new JHttp();
			$h = $http->head($url);
			//var_dump($h);
			return 1;
			if (function_exists('curl_init')){
				JFactory::getApplication()->enqueueMessage('curl_init ok');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'self::curlHeaderCallback');
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				curl_exec ($ch);
				$intReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				
				curl_close($ch);
				var_dump($intReturnCode);
				//$intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE);
				//if ($intReturnCode != 200 && $intReturnCode != 302 && $intReturnCode != 304) { return 0; } else return 1;
			} else {
				JFactory::getApplication()->enqueueMessage('curl_init ko');
			}
			return 1;
		}

		/**
		 *
		 * @param string $path
		 * @return multitype:multitype:unknown  |Ambigous <multitype:, boolean, multitype:unknown multitype:unknown  >
		 */
		public static function getExternalImages($path){
			jimport('joomla.filesystem.folder');
			$files = array();

			// check if $path is url
			$path = trim($path);
			$isHttp = stripos($path, 'http') === 0;
			if ($isHttp){
				if ( !JUri::isInternal($path) ){
					// is external, test if is valid
					if ( version_compare(JVERSION, '3.0.0', '>=') ){
						// is Joomla 3
						$http = JHttpFactory::getHttp();
						$head = $http->head($path);
						if ($head->code == 200 || $head->code == 302 || $head->code == 304){
							// is valid url
							if (preg_match('/image/', $head->headers['Content-Type'])){
								// is true image
								$files[] = $path;
							}
						}
					} else {
						// for Joomla 3 older
						$files[] = $path;
					}
					if (!count($files)){ 
						//var_dump('Url is not valid'); 
					}
					return $files;
				} else {
					$uri = JUri::getInstance($path);
					$uri_path = (string)$uri->getPath();
					$uri_base = (string)JURI::base(true);
					if (stripos($uri_path, $uri_base)===0 && ($baselen = strlen($uri_base))){
						$uri_path = substr($uri_path, $baselen);
					}
					$path = JPATH_BASE.$uri_path;
				}
			}
			
			if ( ($realpath = realpath($path))===false ){
				//var_dump('File or Folder is not exists!');
				return $files;
			}

			if ( is_file($realpath) ){
				$files[] = $realpath;
			} else if ( is_dir($realpath) ){
				$files = JFolder::files($path, '.jpg|.png|.gif', false, true);
			}
			return $files;
		}

		/**
		 * Get an image helper object
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return ImageHelper
		 */
		public static function getImageHelper($image, $options=array()){
			return ImageHelper::init($image, $options);
		}

		/**
		 * Resize and return image tag (<img .../>)
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return string
		 */
		public static function imageTag($image, $options=array()){
			return ImageHelper::init($image, $options)->tag();
		}

		/**
		 * Resize and return image src
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return string
		 */
		public static function imageSrc($image, $options=array()){
			return ImageHelper::init($image, $options)->src();
		}

	}
}
