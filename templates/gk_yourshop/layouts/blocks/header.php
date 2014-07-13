<?php

// No direct access.
defined('_JEXEC') or die;

?>

<?php if( $this->modules('banner1')) : ?>
<div id="gkBanner1" class="clear clearfix">
      <jdoc:include type="modules" name="banner1" style="<?php echo $this->module_styles['banner1']; ?>" />
</div>
<?php endif; ?>

<?php if($this->modules('header1 + header2')) : ?>
<div id="gkHeader" class="gkMain">
	<?php if($this->modules('header1')) : ?>
	<div id="gkHeaderModule1">
		<jdoc:include type="modules" name="header1" style="<?php echo $this->module_styles['header1']; ?>" />
	</div>
	<?php endif; ?>
	
	<?php if($this->modules('header2')) : ?>
	<div id="gkHeaderModule2">
		<jdoc:include type="modules" name="header2" style="<?php echo $this->module_styles['header2']; ?>" />
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if( $this->modules('banner2')) : ?>
<div id="gkBanner2" class="clear clearfix">
      <jdoc:include type="modules" name="banner2" style="<?php echo $this->module_styles['banner2']; ?>" />
</div>
<?php endif; ?>