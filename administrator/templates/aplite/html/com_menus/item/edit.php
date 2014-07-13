<?php
/**
 * @version		$Id: edit.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHTML::_('behavior.modal');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task, type)
	{
		if (task == 'item.setType' || task == 'item.setMenuType') {
			if(task == 'item.setType') {
				document.id('item-form').elements['jform[type]'].value = type;
				document.getElementById('fieldtype').value = 'type';
			} else {
				document.id('item-form').elements['jform[menutype]'].value = type;
			}
			Joomla.submitform('item.setType', document.getElementById('item-form'));
		} else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			// special case for modal popups validation response
			$$('#item-form .modal-value.invalid').each(function(field){
				var idReversed = field.id.split("").reverse().join("");
				var separatorLocation = idReversed.indexOf('_');
				var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
				$(name).addClass('invalid');
			});
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

<div class="width-100">
	<?php echo JHtml::_('tabs.start','module-tabs'); ?>
	<?php echo JHtml::_('tabs.panel',JText::_('COM_MENUS_DETAILS'), 'menu-details'); ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MENUS_ITEM_DETAILS');?></legend>
			<ul class="adminformlist">


				<li><?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?></li>

				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<?php if ($this->item->type =='url'): ?>
					<?php $this->form->setFieldAttribute('link','readonly','false');?>
					<li><?php echo $this->form->getLabel('link'); ?>
					<?php echo $this->form->getInput('link'); ?></li>
				<?php endif ?>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('note'); ?>
				<?php echo $this->form->getInput('note'); ?></li>

				<?php if ($this->item->type !=='url'): ?>
					<li><?php echo $this->form->getLabel('link'); ?>
					<?php echo $this->form->getInput('link'); ?></li>
				<?php endif ?>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('menutype'); ?>
				<?php echo $this->form->getInput('menutype'); ?></li>

				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>

				<li><?php echo $this->form->getLabel('browserNav'); ?>
				<?php echo $this->form->getInput('browserNav'); ?></li>

				<?php if ($this->item->type == 'component') : ?>
					<li><?php echo $this->form->getLabel('home'); ?>
					<?php echo $this->form->getInput('home'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('template_style_id'); ?>
				<?php echo $this->form->getInput('template_style_id'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
		</ul>

	</fieldset>
	<?php echo JHtml::_('tabs.panel',JText::_('COM_MENUS_OPTIONS'), 'menu-options'); ?>
	<?php echo JHtml::_('sliders.start','menu-sliders-'.$this->item->id); ?>
	<?php //Load  parameters.
		echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

		

	<?php echo JHtml::_('sliders.end'); ?>
	
	<?php if (!empty($this->modules)) : ?>
		<?php echo JHtml::_('tabs.panel',JText::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT'), 'module-options'); ?>
		<fieldset>
			<?php echo $this->loadTemplate('modules'); ?>
		</fieldset>
	<?php endif; ?>
	
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<?php echo JHtml::_('tabs.end'); ?>
</div>
<input type="hidden" id="fieldtype" name="fieldtype" value="" />
</form>


