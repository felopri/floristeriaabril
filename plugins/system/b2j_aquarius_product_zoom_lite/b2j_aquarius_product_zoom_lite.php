<?php

/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.helper');
class plgSystemb2j_aquarius_product_zoom_lite extends JPlugin
{
    // onAfterRoute function, it loads all necessary scripts and js files.
    public function onAfterRoute(){
        $app = JFactory::getApplication();
        $view = JRequest::getVar('view');
        if ($app->isAdmin()){
            return true;
        }
        if ($view =='productdetails' || $view =='product'){
            $doc = JFactory::getDocument();
            $baseDir = JURI::root(true).'/plugins/system/b2j_aquarius_product_zoom_lite/';

            include_once(dirname(__FILE__).DS.'browser.php');

            $browser = New B2J_Browser();
            if ($browser->isMobile()) echo "<input type='hidden' id='is_mobile' value='1'/>";
            
            if($this->params->get('css_setting', 1)){
                $doc->addStyleSheet($baseDir.'css/style.css');
                $doc->addStyleDeclaration($this->getCustomCss());
            }
            if($this->params->get('jquery_setting', 1)){
                //$doc->addScript($baseDir.'admin/js/jquery-ui-1.8.22.custom.min.js');
            }
            $doc->addScript($baseDir.'js/jquery.easing.1.3.js');
            $doc->addScript($baseDir.'js/b2jslider.js');
            $doc->addScript($baseDir.'js/script_front.js');
        }
    }
    // OnAfterRender function, it changes elements of the page with the news elemens, need the plugin to work
    public function onAfterRender() {
        $doc = JFactory::getDocument();        
		$app = JFactory::getApplication();
		$view = JRequest::getVar('view');
		if ($app->isAdmin()){
				return true;
			}
		if ($view =='productdetails' || $view =='product'){
            if ($view =='productdetails') {
                $current_product_info = $this->get_product_id_cat_id('v');
            } else if ($view =='product'){
                $current_product_info = $this->get_product_id_cat_id('r');
            } else {
                return true;
            }
        
            if ($view =='productdetails') {
                $getMainImage = $this->getMainImage('v');
                $getAddImages = $this->getAddImages('v', 1);
            } else if ($view =='product'){
                $getMainImage = $this->getMainImage('r');
                $getAddImages = $this->getAddImages('r', 1);
            } else {
                return true;
            }
            
            $buffer = JResponse::getBody();
            if ($getMainImage) {
                $dom = new DOMDocument;
                $dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$buffer);
                $xpath = new DOMXPath( $dom );
                
                $addDivs = $xpath->query(".//div[@class='additional-images']");
                $mainDivs = $xpath->query(".//div[@class='main-image']");
                $wrapDivs = $xpath->query(".//div[@class='productImageWrap']");
                
                if ($getMainImage  && $view =='productdetails') {
                    foreach ( $mainDivs as $div ) {
                        $template = $dom->createCDATASection($getMainImage);
                        $div->parentNode->appendChild( $template );
                        $div->parentNode->removeChild($div);
                    }
                }
                
                if ($getAddImages && $view =='productdetails') {
                    foreach ( $addDivs as $div ) {
                        $template = $dom->createCDATASection($getAddImages);
                        $div->parentNode->appendChild( $template );
                        $div->parentNode->removeChild($div);
                    }
                }
                
                if ($getMainImage  && $view =='product') {
                   foreach ( $wrapDivs as $div ) {
                        $template = $dom->createCDATASection($getMainImage);
                        $div->parentNode->appendChild( $template );
                        $div->parentNode->removeChild($div);
                    }
                }
                $buffer = $dom->saveHTML();
                $dom->loadHTML($buffer);
                $xpath = new DOMXPath( $dom );
                $redaddDivs = $xpath->query(".//div[@class='redshop_additional']");
                if ($getAddImages  && $view =='product') {
                    foreach ( $redaddDivs as $div ) {
                        
                        $template = $dom->createCDATASection($getAddImages);
                        $div->appendChild( $template );
                    }
                }
                
                $buffer = $dom->saveHTML();
                
                $xpath = new DOMXPath( $dom );
                $pDivs = $xpath->query(".//div[@class='product_more_images']");
                $ptDivs = $xpath->query(".//div[@class='product-thumb']");
                $fDivs = $xpath->query(".//div[@class='floatleft']");

                foreach ( $pDivs as $div ) {
                    $div->parentNode->removeChild( $div );
                }
                foreach ( $ptDivs as $div ) {
                    $div->parentNode->removeChild( $div );
                }
                foreach ( $fDivs as $div ) {
                    $div->parentNode->removeChild( $div );
                }
                
                $buffer = $dom->saveHTML();
            }
            JResponse::setBody($buffer);
        }
        return true;
	}
    // The main css creation function
    public function getCustomCss(){
        
        // Getting parameters from backend    
        $image_width = $this->params->get('image_width', 480);
        $image_height = $this->params->get('image_height', 320);
        $image_background_color = $this->params->get('image_background_color', '#fff');
        $image_border = $this->params->get('image_border', 0);
        $image_border_color = $this->params->get('image_border_color', '#000');
        $image_border_radius = $this->params->get('image_border_radius', 0);

        $image_margin_top = $this->params->get('image_margin_top', 0);
        $image_margin_left = $this->params->get('image_margin_left', 0);
        $image_margin_right = $this->params->get('image_margin_right', 0);
        $image_margin_bottom = $this->params->get('image_margin_bottom', 0);
        
        $no_columns = $this->params->get('no_columns', 3);
        $column_per_slide = $this->params->get('column_per_slide', 1);
        $thumbnail_padding = $this->params->get('thumbnail_padding', 0);
        $thumbnail_border = $this->params->get('thumbnail_border', 0);
        $thumbnail_border_color = $this->params->get('thumbnail_border_color', '#000');
        $thumbnail_border_radius = $this->params->get('thumbnail_border_radius', 0);
        
        $thumbnail_margin_top = $this->params->get('thumbnail_margin_top', 0);
        $thumbnail_margin_left = $this->params->get('thumbnail_margin_left', 0);
        $thumbnail_margin_right = $this->params->get('thumbnail_margin_right', 0);
        $thumbnail_margin_bottom = $this->params->get('thumbnail_margin_bottom', 0);
        
        $ignore_left_margin = $this->params->get('ignore_left_margin', 1);
        $ignore_right_margin = $this->params->get('ignore_right_margin', 1);
        
        $zoom_mode = $this->params->get('zoom_mode', 1);
        $magnifier_background_color = $this->params->get('magnifier_background_color', '#fff');
        $magnifier_border = $this->params->get('magnifier_border', 0);
        $magnifier_border_color = $this->params->get('magnifier_border_color', '#000');
        $magnifier_border_radius = $this->params->get('magnifier_border_radius', 0);
        $zoom_percent = $this->params->get('zoom_percent', 100);
        $zoom_width = $image_width;
        $zoom_height = $image_height;
        $zoom_position = $this->params->get('zoom_position', 1);
        $zoom_border = $this->params->get('zoom_border', 0);
        $zoom_border_color = $this->params->get('zoom_border_color', '#000');
        $zoom_border_radius = $this->params->get('zoom_border_radius', 0);
        
        $fullscreen_background_color = $this->params->get('fullscreen_background_color', '#000');
        $fullscreen_background_opacity = $this->params->get('fullscreen_background_opacity', 0.8);
        $fullscreen_thumbnail_position = 0;
        
        $addable_margin_left = 0;
        $addable_margin_right = 0;
        
        if ($no_columns == 0) {
            return;
        }
        
        //Calculations of item widths and heights, margins, paddings, borders and etc
        $thumbnail_width = ($image_width - $no_columns*(2*$thumbnail_border + 2*$thumbnail_padding + $thumbnail_margin_left + $thumbnail_margin_right))/$no_columns;
        $thumbnail_container_width = $image_width / $no_columns;
        $right_button_right = $thumbnail_margin_right;
        if ($ignore_left_margin) {
            $thumbnail_width += $thumbnail_margin_left/$no_columns;
            $thumbnail_container_width += $thumbnail_margin_left/$no_columns;
            $addable_margin_left = $thumbnail_margin_left;
        }
        if ($ignore_right_margin) {
            $thumbnail_width += $thumbnail_margin_right/$no_columns;
            $thumbnail_container_width += $thumbnail_margin_right/$no_columns;
            $addable_margin_right = $thumbnail_margin_right;
        }
        
        $thumbnail_height = $thumbnail_width;
        $thumbnail_container_height = $thumbnail_height + 2*$thumbnail_border + 2*$thumbnail_padding + $thumbnail_margin_top + $thumbnail_margin_bottom;
        
        $fullscreen_thumbnail_container_width = $thumbnail_width + 2*$thumbnail_border + 2*$thumbnail_padding + $thumbnail_margin_left + $thumbnail_margin_right;
        $fullscreen_thumbnail_container_height = $thumbnail_width + 2*$thumbnail_border + 2*$thumbnail_padding + $thumbnail_margin_top + $thumbnail_margin_bottom;
        
        $fullscreen_container_height = $no_columns*(2*$thumbnail_border + 2*$thumbnail_padding + $thumbnail_margin_top + $thumbnail_margin_bottom + $thumbnail_height);
        
        // Setting calculated variables into params for later use
        $this->params->set('fullscreen_thumbnail_container_width', $fullscreen_thumbnail_container_width);
        $this->params->set('fullscreen_thumbnail_container_height', $fullscreen_thumbnail_container_height);
        
        $this->params->set('thumbnail_container_width', $thumbnail_container_width);
        $this->params->set('thumbnail_container_height', $thumbnail_container_height);
        $this->params->set('thumbnail_width', $thumbnail_width);
        $this->params->set('thumbnail_height', $thumbnail_width);

        $additional_width = $no_columns * $thumbnail_width;
        
        // Setting zoom container position
        $zoom_container_css = ".zoom_container{
                width:".$zoom_width."px;
                height:".$zoom_height."px;
                border: ".$zoom_border."px solid ".$zoom_border_color.";
                border-radius: ".$zoom_border_radius."px;";
        $zoom_container_css .= "right:-".($zoom_width + $image_border + 2*$zoom_border + $image_margin_right)."px;";
        $zoom_container_css .= "top:-".($image_border)."px;";
              
        $zoom_container_css .="}";
        
        //Setting fullscreen mode slider position
        $slider_css = '';
        $slider_css .= "left:".($thumbnail_border + $thumbnail_margin_left)."px;";
        $slider_css .= "top:50%;";
        $slider_css .= "margin-top:-".($fullscreen_container_height/2)."px;";
        
        //Including css only for vertical mode in fullscreen mode
        if ($fullscreen_thumbnail_position == 0 || $fullscreen_thumbnail_position == 1){
            $vertical_fullscreen_slider_content = "
            #b2j_plg_galleryzoom_container2{
                width: ".$fullscreen_thumbnail_container_width."px;
                height: ".$fullscreen_container_height."px;
            }
            #b2j_plg_galleryzoom_main_container2{
			    width: ".$fullscreen_thumbnail_container_width."px;
                margin-left:".-$addable_margin_left."px;
                ".$slider_css."
		    }";
        } else {
            $vertical_fullscreen_slider_content = "
            #b2j_plg_galleryzoom_main_container2{
                ".$slider_css."
		    }";
        }
        
        // Main css rules
        $style = "
            .main-image{
                max-width: ".$image_width."px !important;
                height: ".$image_height."px;
                border: ".$image_border."px solid ".$image_border_color." !important;
                border-radius: ".$image_border_radius."px !important;
                margin:".$image_margin_top."px ".$image_margin_right."px ".$image_margin_bottom."px ".$image_margin_left."px !important;
                background: ".$image_background_color." !important;
            }
            .main-image .image_container {
                border-radius: ".$image_border_radius."px;
            }
            .zoom_box{
                background: ".$this->HexToRGB($magnifier_background_color, 0.2).";
                border: ".$magnifier_border."px solid ".$magnifier_border_color.";
                border-radius: ".$magnifier_border_radius."px;
            }
            .b2j_slider_arrow_horizontal_left{
                left:".$thumbnail_margin_left."px;
                top:".$thumbnail_margin_top."px;
                height:".($thumbnail_container_height - $thumbnail_margin_top - $thumbnail_margin_bottom)."px;
                border-radius: ".$thumbnail_border_radius."px;
                width:".(($thumbnail_width + 2*$thumbnail_border + 2*$thumbnail_padding)/3)."px;
            }
            .fullscreen_layer{
                background: ".$fullscreen_background_color.";
                opacity: ".$fullscreen_background_opacity.";
            }
        ".$zoom_container_css;
        return $style;
    }
    
    //Hex to RGB conversion function
    public function HexToRGB($hex, $opacity) {
        $hex = substr($hex, 1);
        
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,4));
        $b = hexdec(substr($hex,4,6));

        $result = 'rgba('.$r.','.$g.','.$b.','.$opacity.')';
        return $result;
    }
    
    // The function which generates main image element and subtree
   	function getMainImage($component_type){
        // VirtueMart
		if ($component_type == 'v'){
            $product_model = VmModel::getModel('product');
            $virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
            $product = $product_model->getProduct($virtuemart_product_id);
            $images = $product->images;
            if ($images[0]->file_url == ''){
                return false;
            }
            $main_image_url = JURI::root().$images[0]->file_url;
        // redShop
        } else if ($component_type == 'r'){
            $redshop_product_id = JRequest::getInt('pid', 0);
            $class = new producthelper;
            $product = $class->getProductById($redshop_product_id);
            $image = $product->product_full_image;
            if ($image == ''){
                return false;
            }
            $main_image_url = JURI::root().'components/com_redshop/assets/images/product/'.$image;
        } else {
            return false;
        }
        // subtree html
		$html = "";
        //Container mode
        if($this->params->get('zoom_mode', 1) == 1){
            $zoom_movement = $this->params->get('zoom_movement', 0);
            $html .= "<div class='main-image loading' rel='".$this->params->get('zoom_mode', 1)."'>";
            $html .= "<div class='zoom_container movement".$zoom_movement."'><img pos='".$this->params->get('zoom_position', 1)."' percent='100'/></div>";
            $html .= "<div class='zoom_box'></div>";
            $html .= "<div class='fullscreen_button'></div>";
            $html .= "<div class='fullscreen_layer_container'>";
                $html .= "<div class='fullscreen_layer'></div>";
                $html .= "<div class='fullscreen_image_container'><img src='".$main_image_url."' /></div><div class='fullscreen_close_button'></div>";
                if ($component_type == 'v'){
                    $html .= $this->getAddImages('v', 2);
                } else if ($component_type == 'r'){
                    $html .= $this->getAddImages('r', 2);
                } else {
                    return false;
                }
            $html .= "</div>";
            $html .= "<div class='image_container'><img src='".$main_image_url."' /><div class='zoom_overlay'></div></div>";
            $html .= "</div><div class='redshop_additional'></div>";
        //Fullscreen mode
        }else if($this->params->get('zoom_mode', 1) == 2){
            $html .= "<div class='main-image nozoom loading'  rel='".$this->params->get('zoom_mode', 1)."'>";
            $html .= "<div class='fullscreen_button'></div>";
            $html .= "<div class='fullscreen_layer_container'>";
                $html .= "<div class='fullscreen_layer'></div>";
                $html .= "<div class='fullscreen_image_container'><img src='".$main_image_url."' /></div><div class='fullscreen_close_button'></div>";
                if ($component_type == 'v'){
                    $html .= $this->getAddImages('v', 2);
                } else if ($component_type == 'r'){
                    $html .= $this->getAddImages('r', 2);
                } else {
                    return false;
                }
            $html .= "</div>";
            $html .= "<div class='image_container'><img src='".$main_image_url."' /><div class='zoom_overlay'></div></div>";
            $html .= "</div><div class='redshop_additional'></div>";
        } else {
            $html .= "<div class='main-image nozoom loading'  rel='".$this->params->get('zoom_mode', 1)."'>";
            $html .= "<div class='image_container'><img src='".$main_image_url."' /><div class='zoom_overlay'></div></div>";
            $html .= "</div><div class='redshop_additional'></div>";
        }
        return $html;
	}
	
	// The function which generates additional image elements and subtree (available only in Pro version)
	function getAddImages($component_type, $container_id){
        return '';
	}
    
    // This function returns array of 2 elements, first one is current product id, second one is current product id
    public function get_product_id_cat_id($component_type) {
        if ($component_type == 'v'){
            $virtuemart_product_id = JRequest::getInt('virtuemart_product_id', 0);
            $product_model = VmModel::getModel('product');
            $product = $product_model->getProduct($virtuemart_product_id);
            $virtuemart_category_id = $product->virtuemart_category_id;
            $return = array();
            $return[0] = $virtuemart_product_id;
            $return[1] = $virtuemart_category_id;
            return $return;
        } else if ($component_type == 'r'){
            $redshop_product_id = JRequest::getInt('pid', 0);
            $class = new producthelper;
            $product = $class->getProductById($redshop_product_id);
            $redshop_category_id = $product->cat_in_sefurl;
            $return = array();
            $return[0] = $redshop_product_id;
            $return[1] = $redshop_category_id;
            return $return;
        } else {
            return false;
        }
    }
    
    //Image resize function for creating thumbnails
    public function imageResize($filename, $thumbnail_width, $thumbnail_height, $thumbnail_mode, $keep_aspect) {
        $image_params = getimagesize($filename);
        $image_res = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        $rgb = imagecolorallocatealpha($image_res, 0, 0, 0, 127);
        imagefill($image_res, 0, 0, $rgb);
        imagealphablending($image_res, false);
        imagesavealpha($image_res, true);
        //resize
        if (!$thumbnail_mode) {
            if (!$keep_aspect) {
                if ($image_params['mime'] == 'image/gif') {
                    $image = imagecreatefromgif($filename);
                } else if ($image_params['mime'] == 'image/png') {
                    $image = imagecreatefrompng($filename);
                } else if ($image_params['mime'] == 'image/jpeg') {
                    $image = imagecreatefromjpeg($filename);
                }

                imagecopyresampled($image_res, $image, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $image_params[0], $image_params[1]);
            } else {
                if ($image_params['mime'] == 'image/gif') {
                    $image = imagecreatefromgif($filename);
                } else if ($image_params['mime'] == 'image/png') {
                    $image = imagecreatefrompng($filename);
                } else if ($image_params['mime'] == 'image/jpeg') {
                    $image = imagecreatefromjpeg($filename);
                }
                $new_width = $thumbnail_width;
                $new_height = $thumbnail_height;
                $start_width = 0;
                $start_height = 0;
                if ($image_params[0] < $image_params[1]) {
                    $new_width = ($image_params[0] / $image_params[1]) * $thumbnail_height;
                    $start_width = ($thumbnail_width - $new_width) / 2;
                } else {
                    $new_height = ($image_params[1] / $image_params[0]) * $thumbnail_width;
                    $start_height = ($thumbnail_height - $new_height) / 2;
                }
                imagecopyresampled($image_res, $image, $start_width, $start_height, 0, 0, $new_width, $new_height, $image_params[0], $image_params[1]);
            }
        //crop
        } else {
            if ($image_params['mime'] == 'image/gif') {
                $image = imagecreatefromgif($filename);
            } else if ($image_params['mime'] == 'image/png') {
                $image = imagecreatefrompng($filename);
            } else if ($image_params['mime'] == 'image/jpeg') {
                $image = imagecreatefromjpeg($filename);
            }
            $new_width = $thumbnail_width;
            $new_height = $thumbnail_height;
            $start_width = 0;
            $start_height = 0;
            $start_width = ($thumbnail_width - $image_params[0]) / 2;
            $start_height = ($thumbnail_height - $image_params[1]) / 2;
            imagecopy($image_res, $image, $start_width, $start_height, 0, 0, $image_params[0], $image_params[1]);
        }
        return $image_res;
    }

    //createThumbnail function which creates thumbnails by given parameters
    public function createThumbnail($item_url, $thumbnail_width, $thumbnail_height) {
        $cache_dir = JPATH_SITE."/plugins/system/b2j_aquarius_product_zoom_lite/cache/";
        $url='';

        if (JFile::exists($cache_dir . md5("Image" . $item_url) . ".jpg")) {
            $url = "plugins/system/b2j_aquarius_product_zoom_lite/cache/".md5("Image" . $item_url) . ".jpg";
        } else if (JFile::exists(JPATH_SITE . DS .$item_url)) {
            $url = $item_url;
            $image_params = getimagesize($url);
            if ($image_params[0] >= $thumbnail_width && $image_params[1] >= $thumbnail_height) {
                if ($image_params[0] - $thumbnail_width > $image_params[1] - $thumbnail_height) {
                    $img = $this->imageResize($url, $image_params[0] * $thumbnail_height / $image_params[1], $thumbnail_height, 0, 1);
                } else {
                    $img = $this->imageResize($url, $thumbnail_width, $image_params[1] * $thumbnail_width / $image_params[0], 0, 1);
                }
                imagepng($img, $cache_dir . "tmp.jpg");
                $url = "plugins/system/b2j_aquarius_product_zoom_lite/cache/tmp.jpg";
                $img = $this->imageResize(JPATH_SITE.DS. $url, $thumbnail_width, $thumbnail_height, 0, 1);
                imagepng($img, $cache_dir . md5("Image" .$item_url) . ".jpg");
                unlink($cache_dir . "tmp.jpg");
            } else {
                $img = $this->imageResize(JPATH_SITE .DS. $url, $thumbnail_width, $thumbnail_height, 0,1);
                imagepng($img, $cache_dir . md5("Image" . $item_url) . ".jpg");
            }
            $url = JURI::root() . "plugins/system/b2j_aquarius_product_zoom_lite/cache/" .md5("Image" . $item_url) . ".jpg";
        }
        return $url;
    }
}
