<?php

/**
 *
 * Main framework class
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;

/*
* Main framework class
*/
class GKTemplate {
    // template ID
    public $TID = 1;
    // access to the standard Joomla! template API
    public $API;
    /*
    * detected browser:
    *
    * browser
    * css3
    * mobile
    *
    */
    public $browser;
    // page config
    public $config;
    // page menu
    public $menu;
    // module styles
    public $module_styles;
    // page suffix
    public $page_suffix;
    // submenu
    public $generateSubmenu;
    // constructor
    public function __construct($tpl, $module_styles, $embed_mode = false) {
		$file = dirname(__file__) . DS . 'framework' . DS . 'gk.browser.php';
        if (!is_file($file)) return null;
        require_once ($file);
        // load the mootools
        JHtml::_('behavior.framework', true);
        // create instance of GKBrowser class
        $gkbrowser = new GKBrowser();
		// put the template handler into API field
        $this->API = $tpl;
        // put the styles to class field
        $this->module_styles = $module_styles;
        // check the browser
        $this->browser = $gkbrowser->detectBrowser();
        // get the params
        $this->getParameters();
        // get the page suffix
        $this->getSuffix();
        // get the modules overrides
        $this->getModuleStyles();
        // get type and generate menu
        $this->menu = $this->getMenuType();
        // enable/disable mootools for pages 
        $this->getMooTools();
        if(!$embed_mode) {
        	// mobile mode
        	if ($this->browser->get('mobile')) {
        	    $this->getLayout('mobile');
        	} else { 	
        		if ($this->browser->get('browser') == 'facebook') { // facebook mode
        	        $this->getLayout('facebook');
        	    } else { // normal mode
        	        $this->getLayout('normal');
        	    }
        	}
        }
        // parse FB and Tweeter buttons
        $this->socialApiParser($embed_mode);
        // mobile parsing
        $this->mobileParser();
        // define an event for replacement
        $dispatcher = JDispatcher::getInstance();
 		// set a proper event for GKParserPlugin 
 		if($this->getParam('use_gk_cache', 0) == 0) {
 			$dispatcher->register('onAfterRender', 'GKParserPlugin');
 		} else {
 			$dispatcher->register('onBeforeCache', 'GKParserPlugin');
 		}
    }
    // get the template parameters in PHP form
    public function getParameters() {
        // create config object
        $this->config = new JObject();
        // set layout override param
        $this->config->set('layout_override', $this->overrideArrayParse($this->getParam('layout_override', '')));
        // set menu override param
        $this->config->set('menu_override', $this->overrideArrayParse($this->getParam('menu_override', '')));
        $this->config->set('suffix_override', $this->overrideArrayParse($this->getParam('suffix_override', '')));
        $this->config->set('module_override', $this->overrideArrayParse($this->getParam('module_override', '')));  
        $this->config->set('tools_override', $this->overrideArrayParse($this->getParam('tools_for_pages', '')));
       $this->config->set('mootools_override', $this->overrideArrayParse($this->getParam('mootools_for_pages', '')));
	}



	 public function getMooTools() {
        
        $isOverrided = $this->getMooToolsOverride();
        
        if($isOverrided){
            $document = &JFactory::getDocument();
            $header = $document->getHeadData();
            $scripts = $header['scripts'];
            // table which contains scripts to disable
            $toRemove = array('mootools-core.js', 'mootools-more.js', 'caption.js');
       
            foreach ($scripts as $key => $value){
                foreach ($toRemove as $remove){
                    if (strpos($key, $remove) !== false) unset($scripts[$key]);
                    }
            }
            $header['scripts'] = $scripts;
            $document->setHeadData($header);
        }
    }
    // Browser detection
  
    //
    //
    //
    // Functions usable for views
    //
    //
    //

    // function to get layout for specified mode
    public function getLayout($mode) {
        // check layout saved in cookie
        $cookie_name = 'gkGavernMobile'.JText::_('TPL_GK_LANG_NAME');
        $cookie = (isset($_COOKIE[$cookie_name])) ? $_COOKIE[$cookie_name] : 'mobile';
        if ($mode == 'mobile' && $cookie == 'mobile') { // mobile mode
			if( $this->browser->get('browser') == 'iphone' ) { // iphone
				$layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('iphone_layout', 'iphone') . '.php';
				if (is_file($layoutpath)) include ($layoutpath);
				else echo 'iPhone layout doesn\'t exist!';
			} else if( $this->browser->get('browser') == 'android' ) { // android
				$layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('android_layout', 'android') . '.php';
				if (is_file($layoutpath)) include ($layoutpath);
				else echo 'Android layout doesn\'t exist!';
			} else { // handheld
				$layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('handheld_layout', 'handheld') . '.php';
				if (is_file($layoutpath)) include ($layoutpath);
				else echo 'Handheld layout doesn\'t exist!';
			}	
        } else {
            if ($mode == 'facebook') { // facebook mode
				$layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('facebook_layout', 'facebook') . '.php';
				if (is_file($layoutpath)) include ($layoutpath);
				else echo 'Facebook layout doesn\'t exist!';
            } else { // normal mode
                // check the override
                $is_overrided = $this->getLayoutOverride();
                // if current page is overrided
                if ($is_overrided !== false) {
                    $layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $is_overrided . '.php';
                    if (is_file($layoutpath)) {
                        include ($layoutpath);
                    } else {
                        $layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('default_layout', 'default') . '.php';
                        if (is_file($layoutpath)) {
                            include ($layoutpath);
                        } else {
                            echo 'Default layout doesn\'t exist!';
                        }
                    }
                } else { // else - load default layout
                    $layoutpath = $this->URLtemplatepath() . DS . 'layouts' . DS . $this->getParam('default_layout', 'default') . '.php';

                    if (is_file($layoutpath)) {
                        include ($layoutpath);
                    } else {
                        echo 'Default layout doesn\'t exist!';
                    }
                }
            }
        }
    }

    // function to get page suffix
    public function getSuffix() {
        // check the override
        $is_overrided = $this->getSuffixOverride();
        // if current page is overrided
        if ($is_overrided !== false) {
            $this->page_suffix = $is_overrided;
        } else { 
        	$this->page_suffix = '';
        }
    }

	// function to get page suffix
	public function getModuleStyles() {
	    $keys = array_keys($this->module_styles);
	    $module_override = $this->config->get('module_override');
	    
	    for($i = 0; $i < count($keys); $i++) {
	    	if(isset($module_override[$keys[$i]])) {
	    		$this->module_styles[$keys[$i]] = $module_override[$keys[$i]];
	    	}
	    }
	}
    
    // function to load specified block
    public function loadBlock($path) {
        jimport('joomla.filesystem.file');
        
        if(JFile::exists($this->URLtemplatepath() . DS . 'layouts' . DS . 'blocks' . DS . $path . '.php')) { 
            include($this->URLtemplatepath() . DS . 'layouts' . DS . 'blocks' . DS . $path . '.php');
        }
    }

    // function to get menu type
   // function to get menu type
    public function getMenuType() {


	// check layout saved in cookie
        $cookie_name = 'gkGavernMobile'.JText::_('TPL_GK_LANG_NAME');
        $cookie = (isset($_COOKIE[$cookie_name])) ? $_COOKIE[$cookie_name] : 'mobile';
        
        if(!$this->browser->get('mobile') || $cookie == 'desktop') {
        	// check the override
        	$is_overrided = $this->getMenuOverride();
        	$menu_type = 'gk_menu';
        	// if current menu is overrided
        	$menu_type = ($is_overrided !== false) ? $is_overrided : $this->getParam('menu_type', 0);

        } else {
        	if(($this->browser->get('browser') == 'iphone' && $this->getParam('iphone_layout', 'iphone')!='iphone') || ($this->browser->get('browser') == 'android' && $this->getParam('android_layout', 'android')!='android') || ($this->browser->get('browser') == 'handheld' && $this->getParam('handheld_layout', 'handheld')!='handheld')){
               $menu_type = 'gk_menu';
            } else {
                    $menu_type = ($this->browser->get('browser') == 'iphone' || $this->browser->get('browser') == 'android') ? 'gk_iphone' : 'gk_handheld';
            }
        }

		// select the menu
        switch ($menu_type) {
            case 'gk_iphone' :
            		$file = dirname(__file__) . DS . 'menu' . DS . 'GKIPhone.php';
            		if (!is_file($file)) return null;
            		require_once ($file);
            		$menuclass = 'GKIPhone';
           			$this->generateSubmenu = false;	
            	break;
            case 'gk_handheld' :
            		$file = dirname(__file__) . DS . 'menu' . DS . 'GKHandheld.php';
            		if (!is_file($file)) return null;
            		require_once ($file);
            		$menuclass = 'GKHandheld';
            		$this->generateSubmenu = false;
            	break;
            case 'gk_menu':
	                $file = dirname(__file__) . DS . 'menu' . DS . 'GKMenu.php';
	                if (!is_file($file)) return null;
	                require_once ($file);
	                $menuclass = 'GKMenu';
	                $this->generateSubmenu = false;
                break;
            case 'gk_dropline':
					$file = dirname(__file__) . DS . 'menu' . DS . 'GKDropline.php';
	                if (!is_file($file)) return null;
	                require_once ($file);
	                $menuclass = 'GKDropline';
	                $this->generateSubmenu = true;
                break;
            case 'gk_split':
					$file = dirname(__file__) . DS . 'menu' . DS . 'GKSplit.php';
	                if (!is_file($file)) return null;
	                require_once ($file);
	                $menuclass = 'GKSplit';
	                $this->generateSubmenu = true;
                break;
            default:
	            	$file = dirname(__file__) . DS . 'menu' . DS . 'GKMenu.php';
	            	if (!is_file($file)) return null;
	            	require_once ($file);
	            	$menuclass = 'GKMenu';
	            	$this->generateSubmenu = false;
                break;
        }
        
        $gkmenu = new $menuclass($this->API->params);
        $gkmenu->_tmpl = $this->API;
        
        return $gkmenu;
    }

    // function to get layout override
    public function getLayoutOverride() {
        // get current ItemID
        $ItemID = JRequest::getInt('Itemid');
        // get current option value
        $option = JRequest::getCmd('option');
        
        // override array
        $layout_overrides = $this->config->get('layout_override');
        // check the config
        if (isset($layout_overrides[$ItemID])) {
            return $layout_overrides[$ItemID];
        } else {
            if (isset($layout_overrides[$option])) {
                return $layout_overrides[$option];
            } else {
                return false;
            }
        }
    }

	// function to get layout override
	public function getSuffixOverride() {
	    // get current ItemID
	    $ItemID = JRequest::getInt('Itemid');
	    // get current option value
	    $option = JRequest::getCmd('option');
	    // override array
	    $suffix_overrides = $this->config->get('suffix_override');
	    // check the config
	    if (isset($suffix_overrides[$ItemID])) {
	        return $suffix_overrides[$ItemID];
	    } else {
	        if (isset($suffix_overrides[$option])) {
	            return $suffix_overrides[$option];
	        } else {
	            return false;
	        }
	    }
	}
	
    // function to get menu override
    public function getMenuOverride() {
        // get current ItemID
        $ItemID = JRequest::getInt('Itemid');
        // get current option value
        $option = JRequest::getCmd('option');
        // override array
        $menu_overrides = $this->config->get('menu_override');
        // check the config
        if (isset($menu_overrides[$ItemID])) {
            return $menu_overrides[$ItemID];
        } else {
            if (isset($menu_overrides[$option])) {
                return $menu_overrides[$option];
            } else {
                return false;
            }
        }   
    }

	// function to get tools override
     public function getToolsOverride() {
          // get current ItemID
          $ItemID = JRequest::getInt('Itemid');
          // get current option value
          $option = JRequest::getCmd('option');
          // override array
          $tools_override = $this->config->get('tools_override');
          // get current tools setting
        $tools_type = $this->getParam('tools', 'all');
        if($tools_type == 'all') { return true; }
        else if($tools_type == 'none') { return false; }
        else {
            // check the config
            if (isset($tools_override[$ItemID])) {
                  return ($tools_override[$ItemID] == 'Enabled') ? true : false;
              } else {

                  if (isset($tools_override[$option])) {
                      return ($tools_override[$option] == 'Enabled') ? true : false;
                  } else {

                      return false;
                  }
              }
        }
     }
    
    public function getMooToolsOverride() {
		// get current ItemID
        $ItemID = JRequest::getInt('Itemid');
        // get current option value
        $option = JRequest::getCmd('option');
        // override array
        $mootools_override = $this->config->get('mootools_override');
        // check the config
        if (isset($mootools_override[$ItemID])) {
            return $mootools_override[$ItemID];
        } else {
            if (isset($mootools_override[$option])) {
                return $mootools_override[$option];
            } else {
                return false;
            }
        }   
	}


    
    // function to generate columns block
    public function generateColumnsBlock($amount, $base_name, $group_id, $start_num) {
        // returns:
        // array(
        //    [number] => array(
        //          "class" => // class of the position
        //          "width" => // width of the position
        //          "name" => // name of the position
        //    ),
        //    ...
        // )
        // possible classes: gkColLeft, gkColRight, gkColCenter, gkColFull
        $amount_of_columns = 0;
        // column existing
        $columns = array();
        // check how many columns you have to generate
        for($i = $start_num; $i <= $amount + ($start_num - 1); $i++) {
            if($this->modules($base_name . $i)) {
                $columns[$i] = true;
                $amount_of_columns++;
            } else {
                $columns[$i] = false;
            }
        }
        // if any column exists
        if($amount_of_columns > 0) {
            // variable to store column width
            $column_width = '100';
            // check if more than one column exists
            if($amount_of_columns > 1) {
                // automatically recognize the widest column 
                $widest_column = $this->getParam($group_id . '_widest', '');
                $widest_column_value = $this->getParam($group_id . '_widest_value', 0);
                // check if the widest column is visible
                if($this->modules($widest_column) && $widest_column_value != 0) {
                    $column_width = round((100 - $widest_column_value) / ($amount_of_columns - 1), 2);


                    $result = array();
                    $added_amount = 0;
                    //
                    for($i = $start_num; $i <= $amount + ($start_num - 1); $i++) {
                        if($columns[$i]) {
                            $added_amount++;
                            $column_class = ($added_amount == 1) ? 'gkColLeft' : (($added_amount == $amount_of_columns) ? 'gkColRight' : 'gkColCenter');
                            $result[$i-$start_num] = array(
                                                "class" => $column_class,
                                                "width" => ($base_name . $i == $widest_column) ? $widest_column_value : $column_width,
                                                "name" => $base_name . $i
                                                );
                        }
                    }
                } else { // all columns are equal
                    $column_width = round(100 / $amount_of_columns, 2);


                    $result = array();
                    $added_amount = 0;
                    
                    for($i = $start_num; $i <= $amount + ($start_num - 1); $i++) {
                        if($columns[$i]) {
                            $added_amount++;
                            $column_class = ($added_amount == 1) ? 'gkColLeft' : (($added_amount == $amount_of_columns) ? 'gkColRight' : 'gkColCenter');
                            $result[$i-$start_num] = array(
                                                "class" => $column_class,
                                                "width" => $column_width,
                                                "name" => $base_name . $i
                                                );
                        }
                    }
                }    
            } else {
                $active_index = 0;
                
                for($i = $start_num; $i <= $amount + ($start_num - 1); $i++) {
                    if($columns[$i]) $active_index = $i;
                }
                
                $result = array(
                                "0" => array(
                                        "class" => 'gkColFull',
                                        "width" => '100',
                                        "name" => $base_name . $active_index
                                    )
                                );
            }
            
            return $result;
        } else { // if any column exists - return null
            return null;
        }
    }

    // function to generate columns widths
    public function generateColumnsWidth() {
        // header column
        if($this->modules('header1 and header2')) {
        	$this->addCSSRule('#gkHeaderModule1 { width: ' . $this->getParam('header_column_width', '50'). '%; }');
        	$this->addCSSRule('#gkHeaderModule2 { width: ' . (100 - $this->getParam('header_column_width', '50')) . '%; }');
        } 
        
        // left column
        if($this->modules('left_left and left_right')) {
        	$this->addCSSRule('#gkLeftLeft { width: ' . $this->getParam('left2_column_width', '50'). '%; }');
        	$this->addCSSRule('#gkLeftRight { width: ' . (100 - $this->getParam('left2_column_width', '50')) . '%; }');
        } 
        // right column
        if($this->modules('right_left and right_right')) {
        	$this->addCSSRule('#gkRightLeft { width: ' . $this->getParam('right2_column_width', '50'). '%; }');
        	$this->addCSSRule('#gkRightRight { width: ' . (100 - $this->getParam('right2_column_width', '50')) . '%; }');
        } 
        // main column
        if($this->modules('inset1 and inset2')) {
        	$this->addCSSRule('#gkInset1 { width: ' . $this->getParam('inset_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkInset2 { width: ' . $this->getParam('inset2_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkComponentWrap { width: ' . (100 - ($this->getParam('inset_column_width', '20') + $this->getParam('inset2_column_width', '20'))) . '%; }');
        } elseif($this->modules('inset1 or inset2')) {
        	if($this->modules('inset1')) {
        		$this->addCSSRule('#gkInset1 { width: ' . $this->getParam('inset_column_width', '20'). '%; }');
        		$this->addCSSRule('#gkComponentWrap { width: ' . (100 - $this->getParam('inset_column_width', '20')) . '%; }');
        	} else {
        		$this->addCSSRule('#gkInset2 { width: ' . $this->getParam('inset2_column_width', '20'). '%; }');
        		$this->addCSSRule('#gkComponentWrap { width: ' . (100 - $this->getParam('inset2_column_width', '20')) . '%; }');
        	}
        } 
        // all columns
        $left_column = $this->modules('left_top + left_bottom + left_left + left_right');
        $right_column = $this->modules('right_top + right_bottom + right_left + right_right');
        
        if($left_column && $right_column) {
        	$this->addCSSRule('#gkLeft { width: ' . $this->getParam('left_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkRight { width: ' . $this->getParam('right_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkContent { width: ' . (100 - ($this->getParam('left_column_width', '20') + $this->getParam('right_column_width', '20'))) . '%; }');
        } elseif ( $left_column ) {
        	$this->addCSSRule('#gkLeft { width: ' . $this->getParam('left_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkContent { width: ' . (100 - $this->getParam('left_column_width', '20')) . '%; }');
        } elseif ( $right_column ) {
        	$this->addCSSRule('#gkRight { width: ' . $this->getParam('right_column_width', '20'). '%; }');
        	$this->addCSSRule('#gkContent { width: ' . (100 - $this->getParam('right_column_width', '20')) . '%; }');
        }
    }
    
    // function to generate blocks paddings
    public function generatePadding($block) {
    	// main blocks
    	if($block == 'gkMainBlock') return 'gkPaddingTLR';	
		// gkMainBlock














    	if($block == 'gkLeftColumn') return 'gkPaddingR';
    	if($block == 'gkRightColumn') return 'gkPaddingL';
    	if($block == 'gkContentColumn') { return 'gkPaddingTBLR'; }
    	// Content
    	if($block == 'gkInset1') return 'gkPaddingR';
    	if($block == 'gkInset2') return 'gkPaddingL';
    	if($block == 'gkComponentWrap') return ($this->modules('inset1')) ? 'gkPaddingTBLR' : '';
    	// left column
    	if($block == 'gkLeftTop') return 'gkPaddingB';
    	if($block == 'gkLeftMiddle') return 'gkPaddingTBL';
    	if($block == 'gkLeftLeft') return ($this->modules('left_right')) ? 'gkPaddingTR' : 'gkPaddingTBLR';

    	if($block == 'gkLeftRight') return 'gkPaddingTL';
    	if($block == 'gkLeftBottom') return 'gkPaddingTB';
    	// right column
		if($block == 'gkRightTop') return 'gkPaddingB';
		if($block == 'gkRightMiddle') return 'gkPaddingTBLR';
		if($block == 'gkRightLeft') return ($this->modules('right_right')) ? 'gkPaddingR' : 'gkPaddingTBLR';
		if($block == 'gkRightRight') return 'gkPaddingTBLR';
		if($block == 'gkRightBottom') return 'gkPaddingTB';
		// main column
		if($block == 'gkContentTop' || $block == 'gkContentBottom') {
			if($this->modules('right_top + right_bottom + right_left + right_right')) return 'gkPaddingB';
			else return 'gkPaddingB';
		}
		
		if($block == 'gkContentMainbody') {
			if($this->modules('right_top + right_bottom + right_left + right_right')) {
				$this->addCSSRule('#gkContent { padding-right:0px !important; }');
			}
			return 'gkPaddingTBLR';

		}
		// mainbody content
		if($block == 'gkMainbodyTop') {
			if($this->modules('inset1 and inset2')) {
				return 'gkPaddingB';
			} elseif($this->modules('inset1')) {
				return 'gkPaddingB';
			} elseif($this->modules('inset2')) {
				return 'gkPaddingB';
			} else {
				return 'gkPaddingB';
			}
		} 
		
		if($block == 'gkMainbody') {
			if($this->modules('inset1 and inset2')) {
				return ($this->modules('mainbody_top')) ? 'gkPaddingBLR' : 'gkPaddingTBLR';
			} elseif($this->modules('inset1')) {
				return ($this->modules('mainbody_top')) ? 'gkPaddingBLR' : 'gkPaddingTBLR';
			} elseif($this->modules('inset2')) {
				return ($this->modules('mainbody_top')) ? 'gkPaddingBLR' : 'gkPaddingTBLR';
			} else {
				return ($this->modules('mainbody_top')) ? 'gkPaddingBLR' : 'gkPaddingTBLR';
			}
		}
		
		if($block == 'gkMainbodyBottom') {
			return 'gkPaddingB';
		}  	
    }
    
    // function to check if mainbody exists
    public function mainExists($mode){
    	if($mode == 'all') {
    		return ($this->checkComponent() || $this->checkMainbody() || $this->modules('left_top + left_bottom + left_left + left_right + right_top + right_bottom + right_left + right_right + top + bottom + mainbody_top + mainbody_bottom + inset1 + inset2 + mainbody'));
    	} elseif($mode == 'content') {
    		return ($this->checkComponent() || $this->checkMainbody() || $this->modules('mainbody_top + mainbody_bottom + mainbody + inset1 + inset2 + top + bottom'));
    	} elseif($mode == 'content_mainbody') {
    		return ($this->checkComponent() || $this->checkMainbody() || $this->modules('mainbody_top + mainbody_bottom + mainbody + inset1 + inset2'));
    	} elseif($mode == 'component_wrap') {
    		return ($this->checkComponent() || $this->checkMainbody() || $this->modules('mainbody_top + mainbody_bottom + mainbody'));
    	} elseif($mode == 'component') {
    		return ($this->checkComponent() || $this->checkMainbody());
    	}
    }
    
    // function to check if component exists	 
    function checkComponent() {	
    	if($this->isFrontpage()) {
    		$result = ($this->getParam('mainbody_frontpage', '') != 'only_mainbody');
    		return (!isset($_POST['option'])) ? $result : true;
    	}else {
    		return !($this->getParam('mainbody_subpage', '') == 'mainbody_or_component' && $this->modules("mainbody") > 0);
    	}
    }
    
    // function to check if mainbody exists
    function checkMainbody() { 
    	if($this->isFrontpage()) {
    		return (($this->getParam('mainbody_frontpage', '') != 'only_component') && $this->modules("mainbody") > 0);
    	} else {
    		return ($this->getParam('mainbody_subpage', '') == 'mainbody_or_component' && $this->modules("mainbody") > 0);
    	}
    }
    
    // function to check if the page is frontpage
    function isFrontpage(){
        // get all known languages
        $languages	= JLanguage::getKnownLanguages();
        $menu = JSite::getMenu();
        
        foreach($languages as $lang){
            if ($menu->getActive() == $menu->getDefault( $lang['tag'] )) {
            	return true;
            }
        }
    	
            
                return false;
            
    }
    
    // function to generate the messages on specified position
    public function messages($position) {
        if($position == $this->getParam('messages_position', 'message-position-1')) {
            echo '<jdoc:include type="message" />'; 
        }
    }
    
     // Parse Facebook and Tweeter buttons
    public function socialApiParser($embed_mode = false) {




         // FB login
         if(!($this->getParam('fb_api_id', '') != '' && $this->getParam('fb_login', '0') == 1) || $this->browser->get('mobile')) {
              // clear FB login
            GKParser::$customRules['/<gavern:fblogin(.*?)gavern:fblogin>/mis'] = '';
         }
        else {
            GKParser::$customRules['/<gavern:fblogin>/mi'] = '';
            GKParser::$customRules['/<\/gavern:fblogin>/mi'] = '';
        }

    	// get the informations about excluded articles and categories
    	$excluded_articles = explode(',', $this->getParam('excluded_arts', ''));
    	$excluded_categories = $this->getParam('excluded_cats', '');
    	if(is_array($excluded_categories) && $excluded_categories[0] == '') $excluded_categories = array(0);
    	else if(is_string($excluded_categories)) $excluded_categories = array($excluded_categories);
    	// get the variables from the URL
    	$option = JRequest::getCmd('option', '');
    	$view = JRequest::getCmd('view', '');
    	$id = JRequest::getVar('id', '');
    	if(strpos($id, ':')) $id = substr($id, 0, strpos($id, ':')); 
    	$catid = JRequest::getVar('catid', '');
    	if(strpos($catid, ':')) $catid = substr($catid, 0, strpos($catid, ':'));

		// find catid if it is not set in the URL
    	if($catid == '' && $option == 'com_content' && $view == 'article' && $id != '') {
    		$db = JFactory::getDBO();
    		$query = 'SELECT catid FROM #__content AS c WHERE c.id = ' . $id . ' LIMIT 1';		
       		// Set the query
    		$db->setQuery($query);
    		$results = $db->loadObjectList();
    		// get the new category ID
    		if(count($results) > 0) {
    			$catid = $results[0]->catid;
    		}
    	}
    	// excluded
    	$is_excluded = false;













		
    	// FB like
    	if($this->getParam('fb_like', '0') == 1 && !$is_excluded && !$this->browser->get('mobile')) {
    		// configure FB like
    		$fb_like_attributes = '';    		
    		// configure FB like
    		if($this->getParam('fb_like_send', 1) == 1) { $fb_like_attributes .= ' send="true"'; }
    		$fb_like_attributes .= ' layout="'.$this->getParam('fb_like_layout', 'standard').'"';
    		$fb_like_attributes .= ' show_faces="'.$this->getParam('fb_like_show_faces', 'true').'"';
    		$fb_like_attributes .= ' width="'.$this->getParam('fb_like_width', '500').'"';
    		$fb_like_attributes .= ' action="'.$this->getParam('fb_like_action', 'like').'"';
    		$fb_like_attributes .= ' font="'.$this->getParam('fb_like_font', 'arial').'"';
    		$fb_like_attributes .= ' colorscheme="'.$this->getParam('fb_like_colorscheme', 'light').'"';
    		
    		GKParser::$customRules['/GK_FB_LIKE_SETTINGS/'] = $fb_like_attributes;
    	} else {
    		// clear FB like
    		GKParser::$customRules['/<gavern:social><fb:like(.*?)fb:like><\/gavern:social>/mi'] = '';
    	}
        // G+
    	if($this->getParam('google_plus', '1') == 1 && !$is_excluded && !$this->browser->get('mobile')) {
    		// configure FB like
    		$google_plus_attributes = '';    		
    		// configure FB like
    		if($this->getParam('google_plus_count', 1) == 0) { 
    			$google_plus_attributes .= ' count="false"'; 
    		}
     		
    		if($this->getParam('google_plus_size', 'medium') != 'standard') { 
    			$google_plus_attributes .= ' size="'.$this->getParam('google_plus_size', 'medium').'"'; 
    		}
    		
    		GKParser::$customRules['/GK_GOOGLE_PLUS_SETTINGS/'] = $google_plus_attributes;
    	} else {
    		// clear G+ button
    		GKParser::$customRules['/<gavern:social><g:plusone(.*?)g:plusone><\/gavern:social>/mi'] = '';
    	}
    	// Twitter
    	if($this->getParam('tweet_btn', '0') == 1 && !$is_excluded && !$this->browser->get('mobile') && $option == 'com_content' && $view == 'article') {
    		// add Twitter JS
    		$this->addJS('http://platform.twitter.com/widgets.js');
    		// configure Twitter buttons    		  
    		$tweet_btn_attributes = '';
    		$tweet_btn_attributes .= ' data-count="'.$this->getParam('tweet_btn_data_count', 'vertical').'"';
    		if($this->getParam('tweet_btn_data_via', '') != '') $tweet_btn_attributes .= ' data-via="'.$this->getParam('tweet_btn_data_via', '').'"'; 
    		$tweet_btn_attributes .= ' data-lang="'.$this->getParam('tweet_btn_data_lang', 'en').'"';
    		  
    		GKParser::$customRules['/GK_TWEET_BTN_SETTINGS/'] = $tweet_btn_attributes;
    	} else {
    		// clear Twitter buttons
    		GKParser::$customRules['/<gavern:social><a href="http:\/\/twitter.com\/share"(.*?)\/a><\/gavern:social>/mi'] = '';
    	}
    	// Digg
    	if($this->getParam('digg_btn', '0') == 1 && !$is_excluded && !$this->browser->get('mobile')) {
    		// configure Twitter buttons    		  
    		$digg_btn_attributes = $this->getParam('digg_btn_style', 'DiggWide');
    		GKParser::$customRules['/GK_DIGG_SETTINGS/'] = $digg_btn_attributes;
    	} else {
    		// clear Twitter buttons
    		GKParser::$customRules['/<gavern:social><a class="DiggThisButton(.*?)\/a><\/gavern:social>/mi'] = '';
    	}
    	// Delicious
    	if($this->getParam('delicious_btn', '0') != 1 || $is_excluded || $this->browser->get('mobile')) {
    		// clear Delicious buttons
    		GKParser::$customRules['/<gavern:social><a href="http:\/\/www.delicious.com\/save"(.*?)\/a><\/gavern:social>/mi'] = '';
    	}
    	// Instapaper
    	if($this->getParam('instapaper_btn', '0') != 1 || $is_excluded || $this->browser->get('mobile')) {
    		// clear Delicious buttons
    		GKParser::$customRules['/<gavern:social><a href="http:\/\/www.instapaper.com\/hello2(.*?)\/a><\/gavern:social>/mi'] = '';
    	}


    	// check the excluded article IDs and category IDs
    	if(($option == 'com_content' && $view == 'article' && in_array($id, $excluded_articles, false)) ||
    		($catid != '' && $option == 'com_content' && $view == 'article' && in_array($catid, $excluded_categories, false)) || $embed_mode) {
    		$is_excluded = true;
            // clear SocialAPI div
    		GKParser::$customRules['/<gavern:social(.*?)gavern:social>/mis'] = '';
    		GKParser::$customRules['/<gavern:socialAPI(.*?)gavern:socialAPI>/mis'] = '';
    	} else {
            GKParser::$customRules['/<gavern:social>/mi'] = '';
            GKParser::$customRules['/<\/gavern:social>/mi'] = '';
            GKParser::$customRules['/<gavern:socialAPI>/mi'] = '';
            GKParser::$customRules['/<\/gavern:socialAPI>/mi'] = '';
        }
    	GKParser::$customRules['/<meta name="og:/'] = '<meta property="og:';
    }
    

    function mobileParser() {
    	if($this->browser->get('mobile')) {
    		// clear desktop elements
    		GKParser::$customRules['/<gavern:desktop(.*?)gavern:desktop>/mis'] = '';
    		GKParser::$customRules['/<gavern:mobile>/mis'] = '';
    		GKParser::$customRules['/<\/gavern:mobile>/mis'] = '';
    		
    		if(($this->browser->get('browser') == 'iphone' || $this->browser->get('browser') == 'android') &&
    		    $this->getParam('mobile_collapsible', '0') == '1') {
    			GKParser::$customRules['/<gavern:gk_collapsible\/>/mis'] = ' class="gkCollapsible"';
    			GKParser::$customRules['/<gavern:gk_collapsible_button\/>/mis'] = '<span class="gkToggle show">Toggle</span>';
    		} else {
    			GKParser::$customRules['/<gavern:gk_collapsible\/>/mis'] = ' class="gkFeaturedItemTitle"';
    			GKParser::$customRules['/<gavern:gk_collapsible_button\/>/mis'] = '';
    		}
    	} else {
    		// clear mobile elements
    		GKParser::$customRules['/<gavern:mobile(.*?)gavern:mobile>/mis'] = '';
    		GKParser::$customRules['/<gavern:desktop>/mis'] = '';
    		GKParser::$customRules['/<\/gavern:desktop>/mis'] = '';
    		GKParser::$customRules['/<gavern:gk_collapsible\/>/mis'] = '';
    		GKParser::$customRules['/<gavern:gk_collapsible_button\/>/mis'] = '';
    	}
    }
    
	function googleAnalyticsParser(){
		$data = $this->getParam('google_analytics','');
		$exploded_data = explode("\r\n", $data);    	
		$script_code = '';
		
		if(count($exploded_data) >= 1) {
			for ($i = 0; $i < count($exploded_data); $i++) {
			    if(isset($exploded_data[$i])) {
			        $key = $exploded_data[$i];
			        if(preg_match('/UA(.*)/i', $key)) {
			        	$script_code .= '<script type="text/javascript">var _gaq = _gaq || []; _gaq.push([\'_setAccount\', \'' .$key. '\']); _gaq.push([\'_trackPageview\']);(function() { var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s); })();</script>';
			        }
			    }
			}
		}
		
		return $script_code;
	}
    
    //
    //
    //
    // Other functions
    //
    //
    //

    public function overrideArrayParse($data)
    {
        $results = array();
        // exploding settings
        $exploded_data = explode("\r\n", $data);
        // parsing
        for ($i = 0; $i < count($exploded_data); $i++) {
            if(isset($exploded_data[$i])) {
	            // preparing pair key-value
	            $pair = explode('=', trim($exploded_data[$i]));
	            // extracting key and value from pair
	            if(count($pair) == 2){
	            	$key = $pair[0];
	            	$value = $pair[1];
	            	// checking existing of key in config array
	            	if (!isset($this->results[$key])) {
	            	    // setting value for key
	            	    $results[$key] = $value;
	            	}
	            }
            }
        }

        // return results array
        return $results;
    }

    //
    //
    // Function for CSS/JS compression
    //
    //

     function useCache($cache_css, $overwrite = false)
    {
        $document = &JFactory::getDocument();

        $scripts = array();
        $css_urls = array();

        if ($cache_css) {
            foreach ($document->_styleSheets as $strSrc => $strAttr) {
                if (!preg_match('/\?.{1,}$/', $strSrc)) {
                    $srcurl = $this->cleanUrl($strSrc);
                    if (!$srcurl) continue;
                    //remove this css and add later
                    
                    if($srcurl != 'components/com_community/templates/gk_style/css/style.css') {
                     unset($document->_styleSheets[$strSrc]);
                     $path = str_replace('/', DS, $srcurl);
                     $css_urls[] = array(JPATH_SITE . DS . $path, JURI::base(true) . '/' . $srcurl);
                    }
                }
            }
        }

        if ($cache_css) {
            $url = $this->optimizecss($css_urls, $overwrite);
            if ($url) {
                $document->addStylesheet($url);
            } else {
                foreach ($css_urls as $urls) $document->addStylesheet($url[1]); //re-add stylesheet to head
            }
        }
    }
	
    function cleanUrl($strSrc) {
        if (preg_match('/^https?\:/', $strSrc)) {
            if (!preg_match('#^' . preg_quote(JURI::base()) . '#', $strSrc)) return false; //external css
            $strSrc = str_replace(JURI::base(), '', $strSrc);
        } else {
            if (preg_match('/^\//', $strSrc)) {
                if (!preg_match('#^' . preg_quote(JURI::base(true)) . '#', $strSrc)) return false; //same server, but outsite website
                $strSrc = preg_replace('#^' . preg_quote(JURI::base(true)) . '#', '', $strSrc);
            }
		}
        $strSrc = str_replace('//', '/', $strSrc);
        $strSrc = preg_replace('/^\//', '', $strSrc);
        return $strSrc;
    }

    function optimizecss($css_urls, $overwrite = false) {
        $content = '';
        $files = '';
        jimport('joomla.filesystem.file');
        foreach ($css_urls as $url) {
            $files .= $url[1];
            //join css files into one file
            $content .= "/* FILE: {$url[1]} */\n" . $this->compresscss(@JFile::read($url[0]), $url[1]) . "\n\n";
        }

        $file = md5($files) . '.css';
        $url = $this->store_file($content, $file, $overwrite);
        return $url;
    }

    function compresscss($data, $url) {
        global $current_css_url;
        $current_css_url = $url;
        /* remove comments */
        $data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);
        /* remove tabs, spaces, new lines, etc. */
        $data = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $data);
        /* remove unnecessary spaces */
        $data = preg_replace('/[ ]+([{};,:])/', '\1', $data);
        $data = preg_replace('/([{};,:])[ ]+/', '\1', $data);
        /* remove empty class */
        $data = preg_replace('/(\}([^\}]*\{\})+)/', '}', $data);
		 /* remove PHP code */
        $data = preg_replace('/<\?(.*?)\?>/mix', '', $data);
        /* replace url*/
        $data = preg_replace_callback('/url\(([^\)]*)\)/', array('GKTemplate', 'replaceurl'), $data);
        return $data;
    }

    function replaceurl($matches) {
        $url = str_replace(array('"', '\''), '', $matches[1]);
        global $current_css_url;
        $url = GKTemplate::converturl($url, $current_css_url);
        return "url('$url')";
    }

    function converturl($url, $cssurl) {
        $base = dirname($cssurl);
        if (preg_match('/^(\/|http)/', $url))
            return $url;
        /*absolute or root*/
        while (preg_match('/^\.\.\//', $url)) {
            $base = dirname($base);
            $url = substr($url, 3);
        }

        $url = $base . '/' . $url;
        return $url;
    }

    function store_file($data, $filename, $overwrite = false) {
        $path = JPATH_SITE . DS . 'cache' . DS . 'gk';
        if (!is_dir($path)) @JFolder::create($path);
        $path = $path . DS . $filename;
        $url = JURI::base(true) . '/cache/gk/' . $filename;
        if (is_file($path) && !$overwrite) return $url;
        @file_put_contents($path, $data);
        return is_file($path) ? $url : false;
    }

    //
    //
    //
    // Override of the Joomla API functions
    //
    //
    //

    public function addCSS($url) { 
        $this->API->addStyleSheet($url);
    }
    
    public function addJS($url) {
        $this->API->addScript($url);
    }
    
    public function addCSSRule($code) {
        $this->API->addStyleDeclaration($code);
    }
    
    public function addJSFragment($code) { 
    	$this->API->addScriptDeclaration($code); 
    }

    public function getParam($key, $default) {
        return $this->API->params->get($key, $default);
    }
    
    public function modules($rule) {
        return $this->API->countModules($rule);
    }
    
    public function URLbase() {
        return JURI::base();
    }
    
    public function URLtemplate() {
        return JURI::base() . "templates/" . $this->API->template;
    }
    
    public function URLpath() {
        return JPATH_SITE;
    }
    
    public function URLtemplatepath() {
        return $this->URLpath() . DS . "templates" . DS . $this->API->template;
    }
    
    public function getPageName() {
        $config = new JConfig();
        return $config->sitename;
    }
}

if(!function_exists('GKParserPlugin')){
	function GKParserPlugin(){
		$parser = new GKParser();
	}
}