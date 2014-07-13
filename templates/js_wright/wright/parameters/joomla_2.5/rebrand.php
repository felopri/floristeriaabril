<?php // $Id: datetime.php 19 2010-08-03 01:24:09Z jeremy $
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldRebrand extends JFormField
{
	protected $type = 'Rebrand';

	protected function getInput()
	{
		$html = array();

		$class = $this->element['class'] ? ' class="radio '.(string) $this->element['class'].'"' : ' class="radio"';

		$html[] = '<fieldset id="'.$this->id.'"'.$class.'>';

		// Get the field options.
		$options = array();
		$options[] = JHTML::_('select.option', 'no', JText::_('No'));
		$options[] = JHTML::_('select.option', 'yes', JText::_('Yes'));

		// Build the radio field output.
		foreach ($options as $i => $option) {

			// Initialize some option attributes.
			$checked	= ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
			$class		= !empty($option->class) ? ' class="'.$option->class.'"' : '';
			$disabled	= !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			$onclick	= !empty($option->onclick) ? ' onclick="'.$option->onclick.'"' : '';

			$html[] = '<input type="radio" id="'.$this->id.$i.'" name="'.$this->name.'"' .
					' value="'.htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8').'"'
					.$checked.$class.$onclick.$disabled.'/>';

			$html[] = '<label for="'.$this->id.$i.'"'.$class.'>'.JText::_($option->text).'</label>';
		}

		$doc = JFactory::getDocument();
		$template = $this->form->getValue('template');
		$author = simplexml_load_file(JPATH_ROOT.DS.'templates'.DS.$template.DS.'templateDetails.xml')->author;
		if (stripos($author, 'shack'))
			$html[] = '&nbsp;<a href="http://www.joomlashack.com/licensing-center" target="_blank">Rebranding requires a license, learn more.</a>';

		// End the radio field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

}