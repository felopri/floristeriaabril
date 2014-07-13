<?php // $Id: datetime.php 19 2010-08-03 01:24:09Z jeremy $
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldStyles extends JFormFieldList
{
	public $type = 'Styles';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$styles = JFolder::files(JPATH_ROOT.DS.'templates'.DS.$this->form->getValue('template').DS.'css', 'style-(.*)?\.css');

        if (!count($styles)) return array(JHTML::_('select.option', '', JText::_('No styles are provided for this template'), true));

		foreach ($styles as $style)
		{
			$item = substr($style, 6, strpos($style, '.css') - 6);
			$val	= $item;
			$text	= ucfirst($item);
			$options[] = JHTML::_('select.option', $val, JText::_($text));
		}

		return $options;
    }
}