<?php

// No direct access.
defined('_JEXEC') or die;
if($this->getParam("cwidth_position", '') == 'style') {
// main column
    if($this->modules('inset1 and inset2')) {
         $gkInset1 = $this->getParam('inset_column_width', '20'). '%';
         $gkInset2 = $this->getParam('inset2_column_width', '20'). '%';
         $gkComponentWrap = (100 - ($this->getParam('inset_column_width', '20') + $this->getParam('inset2_column_width', '20'))) . '%';
    } elseif($this->modules('inset1 or inset2')) {
         if($this->modules('inset1')) {
              $gkInset1 = $this->getParam('inset_column_width', '20'). '%';
              $gkComponentWrap = (100 - $this->getParam('inset_column_width', '20')) . '%';
         } else {
              $gkInset2 = $this->getParam('inset2_column_width', '20'). '%';
              $gkComponentWrap = (100 - $this->getParam('inset2_column_width', '20')) . '%';
         }
    }
   
    // all columns
    $left_column = $this->modules('left_top + left_bottom + left_left + left_right');
    $right_column = $this->modules('right_top + right_bottom + right_left + right_right');

    if($left_column && $right_column) {
        $gkContent = (100 - ($this->getParam('left_column_width', '20') + $this->getParam('right_column_width', '20'))). '%';
    } elseif ( $left_column ) {
        $gkContent = (100 - $this->getParam('left_column_width', '20')). '%';
    } elseif ( $right_column ) {
        $gkContent = (100 - $this->getParam('right_column_width', '20')) . '%';
    }

}
?>

<?php if($this->mainExists('all')) : ?>
<div id="gkMain">
	<div id="gkMainBlock" class="gkMain">
		<?php $this->loadBlock('left'); ?>
	
		<?php if($this->mainExists('content')) : ?>
		<div id="gkContent" class="gkMain gkCol <?php echo $this->generatePadding('gkContentColumn'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkContent;  ?>>
			<?php if($this->modules('top')) : ?>
			<div id="gkContentTop" class="gkMain <?php echo $this->generatePadding('gkContentTop'); ?>">
				<jdoc:include type="modules" name="top" style="<?php echo $this->module_styles['top']; ?>" />
			</div>
			<?php endif; ?>
			
			<?php if($this->mainExists('content_mainbody')) : ?>
			<div id="gkContentMainbody" class="gkMain <?php echo $this->generatePadding('gkContentMainbody'); ?>">
				<?php if($this->modules('inset1')) : ?>
				<div id="gkInset1" class="gkMain gkCol <?php echo $this->generatePadding('gkInset1'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkInset1;  ?>>
					<jdoc:include type="modules" name="inset1" style="<?php echo $this->module_styles['inset1']; ?>" />
				</div>
				<?php endif; ?>			
				
				<?php if($this->mainExists('component_wrap')) : ?>
					<?php 
						$is_column = ($this->modules('inset1 + inset2')) ? 'gkCol' : '';
					?>
					
				<div id="gkComponentWrap" class="gkMain <?php echo $is_column; ?> <?php echo $this->generatePadding('gkComponentWrap'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkComponentWrap;  ?>>	
					<?php if($this->modules('mainbody_top')) : ?>
					<div id="gkMainbodyTop" class="gkMain <?php echo $this->generatePadding('gkMainbodyTop'); ?>">
						<jdoc:include type="modules" name="mainbody_top" style="<?php echo $this->module_styles['mainbody_top']; ?>" />
					</div>
					<?php endif; ?>	
					
					<?php $this->messages('message-position-3'); ?>
					
					<?php if($this->mainExists('component')) : ?>
					<div id="gkMainbody" class="gkMain <?php echo $this->generatePadding('gkMainbody'); ?>">
						<?php if($this->modules('breadcrumb') || $this->getToolsOverride()) : ?>
						<div id="gkBreadcrumb">
							<?php if($this->modules('breadcrumb')) : ?>
							<jdoc:include type="modules" name="breadcrumb" style="<?php echo $this->module_styles['breadcrumb']; ?>" />
							<?php endif; ?>
							
							<?php //if($this->getToolsOverride()) : ?>
								<?php //$this->loadBlock('tools/tools'); ?>
							<?php //endif; ?>
						</div>
						<?php endif; ?>
						
						<?php if($this->isFrontpage()) : ?>
							<?php if($this->getParam('mainbody_frontpage', 'only_component') == 'only_component') : ?>	
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<?php elseif($this->getParam('mainbody_frontpage', 'only_component') == 'only_mainbody') : ?>
							<jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
							<?php elseif($this->getParam('mainbody_frontpage', 'only_component') == 'mainbody_before_component') : ?>
							<jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<?php else : ?>
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
							<?php endif; ?>
						<?php else : ?>
							<?php if($this->getParam('mainbody_subpage', 'only_component') == 'only_component') : ?>	
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<?php elseif($this->getParam('mainbody_subpage', 'only_component') == 'mainbody_before_component') : ?>
							<jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<?php else : ?>
							<div id="gkComponent">
								<jdoc:include type="component" />
							</div>
							<jdoc:include type="modules" name="mainbody" style="<?php echo $this->module_styles['mainbody']; ?>" />
							<?php endif; ?>					
						<?php endif; ?>
					</div>
					<?php endif; ?>
					
					<?php if($this->modules('mainbody_bottom')) : ?>
					<div id="gkMainbodyBottom" class="gkMain <?php echo $this->generatePadding('gkMainbodyBottom'); ?>">
						<jdoc:include type="modules" name="mainbody_bottom" style="<?php echo $this->module_styles['mainbody_bottom']; ?>" />
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
					
				<?php if($this->modules('inset2')) : ?>
				<div id="gkInset2" class="gkMain gkCol <?php echo $this->generatePadding('gkInset2'); ?>" <?php if($this->getParam("cwidth_position", '') == 'style') echo "style=width:".$gkInset2;  ?>>
					<jdoc:include type="modules" name="inset2" style="<?php echo $this->module_styles['inset2']; ?>" />
				</div>
				<?php endif; ?>	
			</div>
			<?php endif; ?>
			
			<?php if($this->modules('bottom')) : ?>
			<div id="gkContentBottom" class="gkMain <?php echo $this->generatePadding('gkContentBottom'); ?>">
				<jdoc:include type="modules" name="bottom" style="<?php echo $this->module_styles['bottom']; ?>" />
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	
		<?php $this->loadBlock('right'); ?>
	</div>
</div>
<?php endif; ?>