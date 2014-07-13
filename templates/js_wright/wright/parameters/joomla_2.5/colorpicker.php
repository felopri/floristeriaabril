<?php // $Id: datetime.php 19 2010-08-03 01:24:09Z jeremy $
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldColorpicker extends JFormField
{
	protected $type = 'Colorpicker';

	protected function getInput()
	{
		$doc = JFactory::getDocument();
		$template = $this->form->getValue('template');
		$doc->addScript(str_replace('/administrator/', '/', JURI::base()).'templates/'.$template.'/wright/parameters/assets/jscolor/jscolor.js');

		$size = ( $this->element['size'] ? 'size="'.$this->element['size'].'"' : '' );
        $value = htmlspecialchars_decode($this->value, ENT_QUOTES);

		$html = '<input type="text" name="'.$this->name.'" id="'.$this->name.'" value="'.$value.'" class="color" '.$size.' /> ';

		return $html;
	}
}