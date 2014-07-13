<?php

/**
 *
 * Android view
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;
$mobile_logo_image = $this->getParam('mobile_logo_image', '');

if($mobile_logo_image == '') {
     $mobile_logo_image = $this->URLtemplate() . '/images/mobile/header_logo.png';
} else {
     $mobile_logo_image = $this->URLbase() . $mobile_logo_image;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
    <jdoc:include type="head" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
    <?php $this->loadBlock('mobile' . DS . 'head.android'); ?>
</head>
<body>
    <div id="gkWrap">
    	<div id="gkTopWrap">
    		<?php if($this->getParam('mobile_logo_type', 'image') == 'css') : ?>
            <h1 id="gkHeader" class="cssLogo">
                   <a href="./"><?php echo $this->getPageName(); ?></a>    
            <?php else : ?>
            <h1 id="gkHeader">
                <a href="./"><img src="<?php echo $mobile_logo_image; ?>" alt="<?php echo $this->getPageName(); ?>" /></a>    
            <?php endif; ?>
            </h1>
    	
    		<?php if($this->getParam('mobile_desktop', 0) == '1') : ?>
    		<a href="#" id="gk-btn-switch" ><span><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_DESKTOP'); ?></span></a>
    		<?php endif; ?>
    		
    		<?php if($this->getParam('mobile_register', 0) == '1') : ?>
    		<a href="<?php echo $this->URLbase(); ?>index.php?option=com_users&view=registration" id="gk-btn-register" ><span><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_REGISTER'); ?></span></a>
    		<?php endif; ?>
    		
    		<?php if($this->getParam('mobile_login', 0) == '1') : ?>
    		<a href="<?php echo $this->URLbase(); ?>index.php?option=com_users&amp;view=login" id="gk-btn-login" ><span><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_LOGIN'); ?></span></a>
    		<?php endif; ?>
    	</div>
    	
    	<div id="gkNav">
    		<div id="gkNavContent">
    			<a href="#" id="gk-btn-menu" ><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_MENU'); ?></a>
    			<?php if($this->getParam('mobile_search', 0) == '1') : ?>
    			<a href="#" id="gk-btn-search" ><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_SEARCH'); ?></a>
    			<?php endif; ?>
    			<a href="#" id="gk-btn-nav-prev" class="button"><span><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_BACK'); ?></span></a>
    			<a href="#" id="gk-btn-nav-close" class="button"><span><?php echo JText::_('TPL_GK_LANG_GK_MOBILE_CLOSE'); ?></span></a>
    		</div>
    		
    		<div id="gkMenuContent">
    		<?php
    			$this->menu->loadMenu($this->getParam('mobile_menu_name','mainmenu')); 
    		    $this->menu->genMenu(0, -1);
    		?>	
    		</div>
    	</div>

		<?php if($this->getParam('mobile_search', 0) == '1') : ?>
		<div id="gkSearch">
			<form method="post" action="index.php">
				<p>
					<input type="text" class="inputbox" id="mod-search-searchword" name="searchword">	
					<input type="hidden" value="search" name="task">
					<input type="hidden" value="com_search" name="option">
					<input type="hidden" value="435" name="Itemid">
				</p>
			</form>
		</div>
		<?php endif; ?>
    	
    	<div id="gkContent">
	    	<?php if($this->modules('mobile_top')) : ?>
	    	<div id="gkTop">
	    		<jdoc:include type="modules" name="mobile_top" style="gk_mobile" />
	    	</div>
	    	<?php endif; ?>
	    	
	    	<div id="gkMain">
	    		<jdoc:include type="message" />
	    		<jdoc:include type="component" />
	    	</div>
	    	
	    	<?php if($this->modules('mobile_bottom')) : ?>
	    	<div id="gkBottom">
	    		<jdoc:include type="modules" name="mobile_bottom" style="gk_mobile" />
	    	</div>
	    	<?php endif; ?>
	 
	    	<div id="gkFooter">
	    		<p id="gkCopyrights"><?php echo $this->getParam('copyrights', ''); ?></p>
	    		
	    		<p id="gkOptions">
	    			<a href="#gkHeader"><?php echo JText::_('TPL_GK_LANG_MOBILE_TOP'); ?></a>
	    			<a href="javascript:setCookie('gkGavernMobile<?php echo JText::_('TPL_GK_LANG_NAME'); ?>', 'desktop', 365);window.location.reload();"><?php echo JText::_('TPL_GK_LANG_MOBILE_SWITCH_DESKTOP'); ?></a>
	    		</p>
	    	</div>
    	</div>
	</div>
	
	<div id="gkTranslations">
		<span id="translation-name"><?php echo JText::_('TPL_GK_LANG_NAME'); ?></span>
		<span id="translation-confirm"><?php echo JText::_('TPL_GK_LANG_MOBILE_SWITCH_AGREE'); ?></span>
	</div>
	
	<?php 
		// put Google Analytics code
		$this->googleAnalyticsParser(); 
	?>
</body>
