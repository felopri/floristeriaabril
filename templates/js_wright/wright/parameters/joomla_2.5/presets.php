<?php

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldPresets extends JFormFieldList
{

	var	$_name = 'Presets';

	protected function getOptions()
	{
		$doc = JFactory::getDocument();
		$template = $this->form->getValue('template');
		$doc->addScript(str_replace('/administrator/', '/', JURI::base()).'templates/'.$template.'/wright/parameters/assets/presets/presets_1.6.js');
		
		$file = simplexml_load_file(JPATH_ROOT.DS.'templates'.DS.$template.DS.'presets.xml');

		$json = str_replace('@attributes', 'attributes', json_encode($file));

		$doc->addScriptDeclaration('var presets = '.$json);

		$options = array ();

		foreach ($file->xpath('//preset') as $preset)
		{
			$options[] = JHTML::_('select.option', $preset['name'], $preset['title']);
		}

		return $options;
	}
}
