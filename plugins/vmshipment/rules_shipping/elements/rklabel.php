<?php
/*------------------------------------------------------------------------
# rklabel.php - simple param element to display text
# ------------------------------------------------------------------------
# author    Reinhold Kainhofer, The Open Tools Association
# copyright Copyright (C) 2013 open-tools.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl.html GNU/GPL
# Websites: http://www.open-tools.net/
# Technical Support:  Forum - http://www.open-tools.net/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

 // Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * A label/header element, displayed left-aligned, spanning the whole width. This can be used for section headers as well as for explanatory text
 */

class JElementRKLabel extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'rklabel';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		return '<label for="'.$name.'"'.$class.'>'.JText::_($value).'</label>';
	}


	/**
	 * Method to render an xml element
	 *
	 * @param   string  &$xmlElement   Name of the element
	 * @param   string  $value         Value of the element
	 * @param   string  $control_name  Name of the control
	 *
	 * @return  array  Attributes of an element
	 *
	 * @deprecated    12.1
	 * @since   11.1
	 */
	public function render(&$xmlElement, $value, $control_name = 'params')
	{
		// Deprecation warning.
// 		jimport( 'joomla.error.log' );
// 		JLog::add('JElement::render is deprecated.', JLog::WARNING, 'deprecated');

		$name = $xmlElement->attributes('name');
		$label = $xmlElement->attributes('label');
		$descr = $xmlElement->attributes('description');

		//make sure we have a valid label
		$label = $label ? $label : $name;
		// Set to NULL so that the virtuemart table layout code will make the element span two columns:
		$result[0] = NULL;
// 		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
		$result[2] = $descr;
		$result[3] = $label;
		$result[4] = $value;
		$result[5] = $name;

		return $result;
	}

}