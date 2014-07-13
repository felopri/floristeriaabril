<?php

// No direct access.
defined('_JEXEC') or die;

$bottom_1_6_columns = $this->generateColumnsBlock(6, 'bottom', 'bottom1_6', 1);
$bottom_7_12_columns = $this->generateColumnsBlock(6, 'bottom', 'bottom7_12', 7);

?>

<?php if($this->modules('bottom1 + bottom2 + bottom3 + bottom4 + bottom5 + bottom6') && $bottom_1_6_columns !== null) : ?>
<div id="gkBottom1" class="gkMain <?php echo $this->generatePadding('gkBottom1'); ?>">
	<?php foreach($bottom_1_6_columns as $column) : ?>
	<?php if($column !== null) : ?>	
	<div id="gkbottom<?php echo $column['name']; ?>" class="gkCol <?php echo $column['class']; ?>">
		<?php $this->addCSSRule('#gkbottom'.$column['name'].' { width: ' . $column['width'] . '%; }'); ?>
		<jdoc:include type="modules" name="<?php echo $column['name']; ?>" style="<?php echo $this->module_styles[$column['name']]; ?>" />
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($this->modules('bottom7 + bottom8 + bottom9 + bottom10 + bottom11 + bottom12') && $bottom_7_12_columns !== null) : ?>
<div id="gkBottom2" class="gkMain <?php echo $this->generatePadding('gkBottom2'); ?>">
	<?php foreach($bottom_7_12_columns as $column) : ?>
	<?php if($column !== null) : ?>	
	<div id="gkbottom<?php echo $column['name']; ?>" class="gkCol <?php echo $column['class']; ?>">
		<?php $this->addCSSRule('#gkbottom'.$column['name'].' { width: ' . $column['width'] . '%; }'); ?>
		<jdoc:include type="modules" name="<?php echo $column['name']; ?>" style="<?php echo $this->module_styles[$column['name']]; ?>" />
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>