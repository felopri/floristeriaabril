<?php
// This is the code which will be placed in the head section
// No direct access.
defined('_JEXEC') or die;
$favicon_image = $this->getParam('favicon_image', '');
if($favicon_image == '') {
	$favicon_image = $this->URLtemplate() . '/images/favicon.ico';
} else {
	$favicon_image = $this->URLbase() . $favicon_image;
}
$this->API->addFavicon($favicon_image);
?>
<?php if($this->getParam("chrome_frame_support", '0') == '1') : ?>
<meta http-equiv="X-UA-Compatible" content="chrome=1"/>
<?php endif; ?>

<meta http-equiv="X-UA-Compatible" content="IE=9" />
<?php
if($this->browser->get('browser') != 'ie6') {
	// check the color version
	$template_style = '';
	if($this->getParam("stylearea", 1)) $template_style = (isset($_COOKIE['gk2_style']) ? $_COOKIE['gk2_style'] : $this->getParam("template_color", 1));
	else $template_style = $this->getParam("template_color", 1);
	// load the CSS files
	if($this->getParam('reset_css', '') != '') {
		$this->addCSS($this->URLtemplate() . '/css/reset/'.$this->getParam('reset_css', '').'.css');
	}
	$this->addCSS($this->URLtemplate() . '/css/k2.css');
	$this->addCSS($this->URLtemplate() . '/css/layout.css');
	$this->addCSS($this->URLtemplate() . '/css/joomla.css');
	$this->addCSS($this->URLtemplate() . '/css/template.css');
	$this->addCSS($this->URLtemplate() . '/css/menu.css');
	$this->addCSS($this->URLtemplate() . '/css/gk.stuff.css');
	$this->addCSS($this->URLtemplate() . '/css/vm.css');
	if($this->getParam('typography', '1') == '1') {
		$this->addCSS($this->URLtemplate() . '/css/typography.style'.$template_style.'.css');
		if($this->getParam('typo_iconset1', '1') == '1') $this->addCSS($this->URLtemplate() . '/css/typography.iconset.1.css');	
	}
	$this->addCSS($this->URLtemplate() . '/css/style'.$template_style.'.css');
	if($this->getParam("css_override", '0')) {
		$this->addCSS($this->URLtemplate() . '/css/override.css');
	}
	$this->useCache($this->getParam('css_compression', '0'), $this->getParam('css_cache', '0'));
	// include fonts
	$font_iter = 1;
	while($this->getParam('font_name_group'.$font_iter, 'gkFontNull') !== 'gkFontNull') {
		$font_data = explode(';', $this->getParam('font_name_group'.$font_iter, ''));
		if(isset($font_data) && count($font_data) == 2) {
			$font_type = $font_data[0];
			$font_name = $font_data[1];
			if($font_type == 'standard') {
				$this->addCSSRule($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . '; }'."\n");
			} elseif($font_type == 'google') {
				echo '<link href="http://fonts.googleapis.com/css?family='.$font_name.'" rel="stylesheet" type="text/css" />';
				$gfont = $font_name;
	            if(stripos($gfont, ':') !== FALSE) {
	                $gfont_cut = stripos($gfont, ':');
	                $gfont = substr($gfont, 0, $gfont_cut);
	            }
				$this->addCSSRule($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: '.str_replace('+', ' ', $gfont). ', Arial, sans-serif; }'."\n");
			} elseif($font_type == 'squirrel') {
				echo '<link href="'. $this->URLtemplate() . '/fonts/' . $font_name . '/stylesheet.css" rel="stylesheet" type="text/css" />';
				$this->addCSSRule($this->getParam('font_rules_group'.$font_iter, '') . ' { font-family: ' . $font_name . ', Arial, sans-serif; }'."\n");
			}
		}
		$font_iter++;
	}
	// include JavaScript
	$this->addJS($this->URLtemplate() . '/js/gk.scripts.js');
	if($this->browser->get('browser') == 'ie7') {
		$this->addJS($this->URLtemplate() . '/js/ie7.equal.columns.js');
	}
	if($this->getParam('selectivizr', '0') == 1 && ($this->browser->get('browser') == 'ie7' || $this->browser->get('browser') == 'ie8')) {
		$this->addJS($this->URLtemplate() . '/js/selectivizr.js');
	}
	if($this->getParam('menu_type', 'gk_menu') == 'gk_menu') {
		$this->addJSFragment(' $GKMenu = { height:'.($this->getParam('menu_height','0') == 1 ? 'true' : 'false') .', width:'.($this->getParam('menu_width','0') == 1 ? 'true' : 'false') .', duration: '.($this->getParam('menu_duration', '500')).' };');
	}
	$this->addJSFragment('$GK_TMPL_URL = "' . $this->URLtemplate() . '";');
?>
	<!--[if IE 8.0]><link rel="stylesheet" href="<?php echo $this->URLtemplate(); ?>/css/ie8.css" type="text/css" /><![endif]-->
	<!--[if IE 7.0]><link rel="stylesheet" href="<?php echo $this->URLtemplate(); ?>/css/ie7.css" type="text/css" /><![endif]-->
<?php
} else {
	// IE6 code
	$this->addCSS( $this->URLtemplate(). '/css/ie6.css');
}