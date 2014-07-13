<?php // $Id: datetime.php 19 2010-08-03 01:24:09Z jeremy $
defined('_JEXEC') or die('Restricted access');

class JElementSettings extends JElement
{
	var	$_name = 'Settings';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$doc = JRegistry::getInstance();
		print_r($doc);

		$template = $_GET['cid'][0];
		$doc->addScript(str_replace('/administrator/', '/', JURI::base()).'templates/'.$template.'/wright/parameters/assets/jscolor/jscolor.js');

		$size = ( $node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '' );
        $value = htmlspecialchars_decode($value, ENT_QUOTES);

		$html = '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" class="color" '.$size.' /> ';

		return $html;
	}
}