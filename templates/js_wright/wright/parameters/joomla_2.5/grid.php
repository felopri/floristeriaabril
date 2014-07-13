<?php // $Id: datetime.php 19 2010-08-03 01:24:09Z jeremy $
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldGrid extends JFormFieldList
{
	public $type = 'Grid';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$size = ($this->element['size']) ? $this->element['size'] : 12;

		$options = array ();
		for ($i=1; $i <= $size; $i++)
		{
			$val	= $i;
			$text	= $i;
			$options[] = JHTML::_('select.option', $val, JText::_($text));
		}

		return $options;
	}
}