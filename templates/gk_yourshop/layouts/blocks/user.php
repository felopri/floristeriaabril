<?php

// No direct access.
defined('_JEXEC') or die;

$user_1_6_columns = $this->generateColumnsBlock(6, 'user', 'user1_6', 1);
$user_7_12_columns = $this->generateColumnsBlock(6, 'user', 'user7_12', 7);

?>

<?php if($this->modules('user1 + user2 + user3 + user4 + user5 + user6') && $user_1_6_columns !== null) : ?>
<div id="gkUser1" class="gkMain <?php echo $this->generatePadding('gkUser1'); ?>">
	<?php foreach($user_1_6_columns as $column) : ?>
	<?php if($column !== null) : ?>	
	<div id="gkuser<?php echo $column['name']; ?>" class="gkCol <?php echo $column['class']; ?>">
		<?php $this->addCSSRule('#gkuser'.$column['name'].' { width: ' . $column['width'] . '%; }'); ?>
		<jdoc:include type="modules" name="<?php echo $column['name']; ?>" style="<?php echo $this->module_styles[$column['name']]; ?>" />
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($this->modules('user7 + user8 + user9 + user10 + user11 + user12') && $user_7_12_columns !== null) : ?>
<div id="gkUser2" class="gkMain <?php echo $this->generatePadding('gkUser2'); ?>">
	<?php foreach($user_7_12_columns as $column) : ?>
	<?php if($column !== null) : ?>	
	<div id="gkuser<?php echo $column['name']; ?>" class="gkCol <?php echo $column['class']; ?>">
		<?php $this->addCSSRule('#gkuser'.$column['name'].' { width: ' . $column['width'] . '%; }'); ?>
		<jdoc:include type="modules" name="<?php echo $column['name']; ?>" style="<?php echo $this->module_styles[$column['name']]; ?>" />
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>