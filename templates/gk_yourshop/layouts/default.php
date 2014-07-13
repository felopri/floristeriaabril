<?php

/**
 *
 * Default view
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;

if($this->getParam("cwidth_position", 'head') == 'head') {
$this->generateColumnsWidth();
}
$this->addCSSRule('#bgWrapLeft, #bgWrapRight, #gkWrap1, #gkWrap2, #gkWrap3,#gkTop { width: ' . $this->getParam('template_width','980px') . '; }');

$tpl_page_suffix = '';

if($this->page_suffix != '') {
	$tpl_page_suffix = ' class="'.$this->page_suffix.'"';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
	  xmlns:og="http://ogp.me/ns#" 
	  xmlns:fb="http://www.facebook.com/2008/fbml" 
	  xml:lang="pl" lang="pl">
<head>
    <jdoc:include type="head" />
    <?php $this->loadBlock('head'); ?>
</head>
<body<?php echo $tpl_page_suffix; ?>>
	<?php if($this->browser->get('browser') == 'ie6' && $this->getParam('ie6bar', '1') == 1) : ?>
	<div id="gkInfobar"><a href="http://browsehappy.com"><?php echo JText::_('TPL_GK_GAVERN_IE6_BAR'); ?></a></div>
	<?php endif; ?>
    
	<?php $this->messages('message-position-1'); ?>	
    <div id="gkTop">
    <?php if(isset($_COOKIE['gkGavernMobile'.JText::_('TPL_GK_LANG_NAME')]) &&
    $_COOKIE['gkGavernMobile'.JText::_('TPL_GK_LANG_NAME')] == 'desktop') : ?>
    <div class="mobileSwitch gkWrap">
    <a href="javascript:setCookie('gkGavernMobile<?php echo JText::_('TPL_GK_LANG_NAME'); ?>', 'mobile', 365);window.location.reload();"><?php echo JText::_('TPL_GK_LANG_SWITCH_TO_MOBILE'); ?></a>
    </div>
    <?php endif; ?>
	<?php $this->loadBlock('logo'); ?>
    </div>
    </div>
<div id="bgWrapLeft">
  <div id="bgWrapRight">

	<div id="gkBg">   
		<div id="gkWrap1">

			<?php $this->loadBlock('nav'); ?>
			
			<?php $this->loadBlock('header'); ?>
		</div>
    
    <?php $this->messages('message-position-2'); ?>
    
    <div id="gkWrap2">	
    	<?php $this->loadBlock('top'); ?>
    	
    	<?php $this->loadBlock('main'); ?>
    	
    	<?php $this->loadBlock('user'); ?>
    </div>
    
    <div id="gkWrap3">
    	<?php $this->loadBlock('bottom'); ?>
    	<?php $this->loadBlock('footer'); ?>
    </div>
    </div>
    
    <?php // $this->loadBlock('popup'); ?>
	<?php $this->loadBlock('social'); ?>
  </div>
</div>	
	<jdoc:include type="modules" name="debug" />
</body>
</html>