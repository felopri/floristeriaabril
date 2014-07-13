<?php



// No direct access.

defined('_JEXEC') or die;



?>

<div id="gkFooter" class="gkMain">
	<?php if($this->modules('footer_nav')) : ?>
	<div id="gkFooterNav">
		<jdoc:include type="modules" name="footer_nav" style="<?php echo $this->module_styles['footer_nav']; ?>" />
	</div>
	<?php endif; ?>
	
	<?php if($this->getParam('stylearea', '0') == '1') : ?>
	<p id="gkStyleArea">
		<a href="#" class="gkStyle" id="gkStyle1">Green</a>
		<a href="#" class="gkStyle" id="gkStyle2">Red</a>
		<a href="#" class="gkStyle" id="gkStyle3">Blue</a>
	</p>
	<?php endif; ?>
	
	<?php if($this->getParam('copyrights', '') !== '') : ?>
	<p id="gkCopyrights">
		<?php echo $this->getParam('copyrights', ''); ?>
	</p>
	<?php endif; ?>
	
	<?php if(isset($_COOKIE['gkGavernMobile'.JText::_('TPL_GK_PENGUINMAIL_NAME')]) && 
		$_COOKIE['gkGavernMobile'.JText::_('TPL_GK_PENGUINMAIL_NAME')] == 'desktop') : ?>
		<a href="javascript:setCookie('gkGavernMobile<?php echo JText::_('TPL_GK_PENGUINMAIL_NAME'); ?>', 'mobile', 365);window.location.reload();"><?php echo JText::_('TPL_GK_PENGUINMAIL_SWITCH_TO_MOBILE'); ?></a>
	<?php endif; ?>
</div>

<?php if($this->getParam('framework_logo', '0') == '1') : ?>
<div id="gkFrameworkLogo">Framework logo</div>
<?php endif; ?>