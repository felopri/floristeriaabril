<?php

// No direct access.
defined('_JEXEC') or die;

$right_column_middle_class = ' oneCol';

if($this->modules('right_left and right_right')) {
	$right_column_middle_class = ' twoCol';
}
if($this->getParam("cwidth_position", '') == 'style') {
   
    if($this->modules('header1 and header2')) {
         $gkHeaderModule1 = $this->getParam('header_column_width', '50'). '%';
         $gkHeaderModule2 = (97 - $this->getParam('header_column_width', '50')) . '%';
    }
    // right column
    if($this->modules('right_left and right_right')) {
         $gkRightLeft = $this->getParam('right2_column_width', '50'). '%';
         $gkRightRight = (100 - $this->getParam('right2_column_width', '50')) . '%';
    }
    // all columns
    $left_column = $this->modules('left_top + left_bottom + left_left + left_right');
    $right_column = $this->modules('right_top + right_bottom + right_left + right_right');
   
    if($left_column && $right_column) {
         $gkRight = $this->getParam('right_column_width', '20'). '%';
    }  elseif ( $right_column ) {
         $gkRight = $this->getParam('right_column_width', '20'). '%';
    }
}
?>


<?php if($this->modules('right_top + right_bottom + right_left + right_right')) : ?>
<div id="gkRight" class="gkMain gkCol <?php echo $this->generatePadding('gkRightColumn'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkRight;  ?>>
	<?php if($this->modules('right_top')) : ?>
	<div id="gkRightTop" class="gkMain <?php echo $this->generatePadding('gkRightTop'); ?>">
		<jdoc:include type="modules" name="right_top" style="<?php echo $this->module_styles['right_top']; ?>" />
	</div>
	<?php endif; ?>

	<?php if($this->modules('right_left + right_right')) : ?>
	<div id="gkRightMiddle" class="gkMain<?php echo $right_column_middle_class; ?> <?php echo $this->generatePadding('gkRightMiddle'); ?>">
		<?php if($this->modules('right_left')) : ?>
		<div id="gkRightLeft" class="gkMain gkCol <?php echo $this->generatePadding('gkRightLeft'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkRightLeft;  ?>>
			<jdoc:include type="modules" name="right_left" style="<?php echo $this->module_styles['right_left']; ?>" />
		</div>
		<?php endif; ?>	
		
		<?php if($this->modules('right_right')) : ?>
		<div id="gkRightRight" class="gkMain gkCol <?php echo $this->generatePadding('gkRightRight'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkRightRight;  ?>>
			<jdoc:include type="modules" name="right_right" style="<?php echo $this->module_styles['right_right']; ?>" />
		</div>
		<?php endif; ?>			
	</div>
	<?php endif; ?>	

	<?php if($this->modules('right_bottom')) : ?>
	<div id="gkRightBottom" class="gkMain <?php echo $this->generatePadding('gkRightBottom'); ?>">
		<jdoc:include type="modules" name="right_bottom" style="<?php echo $this->module_styles['right_bottom']; ?>" />
	</div>
	<?php endif; ?>	
</div>
<?php endif; ?>