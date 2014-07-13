<?php

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldLogo extends JFormFieldList
{
	public $type = 'Logo';

	protected function getOptions()
	{
		$options = array();

		$options[] = JHTML::_('select.option', 'template', '- '.JText::_('Use template logo').' -');
		$options[] = JHTML::_('select.option', 'module', '- '.JText::_('Use module with position of logo').' -');
		$options[] = JHTML::_('select.option', 'title', '- '.JText::_('Use site title').' -');

		$files = JFolder::files(JPATH_ROOT.DS.'images', '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$');

		foreach ($files as $file)
		{
			$options[] = JHTML::_('select.option', $file, $file);
		}

		return $options;
	}
}