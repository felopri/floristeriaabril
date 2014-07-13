<?php
/**
 * @version $Id: getklarna.php 6501 2012-10-04 13:16:05Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined ('JPATH_BASE') or die();

/**
 * Renders a label element
 */
if (JVM_VERSION === 2) {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
	if (!class_exists ('KlarnaHandler')) {
		require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
	}
} else {
	require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
	if (!class_exists ('KlarnaHandler')) {
		require (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
	}
}

class JElementGetKlarna extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'getKlarna';

	function fetchElement ($name, $value, &$node, $control_name) {

		$jlang = JFactory::getLanguage ();
		$lang = $jlang->getTag ();
		$langArray = explode ("-", $lang);
		$lang = strtolower ($langArray[1]);
		$countriesData = KlarnaHandler::countriesData ();
		$signLang = "en";
		foreach ($countriesData as $countryData) {
			if ($countryData['country_code'] == $lang) {
				$signLang = $lang;
				break;
			}
		}
		/*
		$logo = '<a href="https://merchants.klarna.com/signup?locale=' . $signLang . '&partner_id=7829355537eae268a17667c199e7c7662d3391f7" target="_blank">
	             <img src="' . JURI::root () . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/get_klarna_now.png" /></a> ';
*/
		$logo = '<img src="' . JURI::root () . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/get_klarna_now.jpg" />';
		$html = '<a href="#" id="klarna_getklarna_link" ">' . $logo . '</a>';

		$html .= '<div id="klarna_getklarna_show_hide" >';
		$url = "https://merchants.klarna.com/signup/choose_region?locale=' . $signLang . '&partner_id=7829355537eae268a17667c199e7c7662d3391f7";
		$js = '
		jQuery(document).ready(function( $ ) {
			$("#klarna_getklarna_show_hide").hide();
			jQuery("#klarna_getklarna_link").click( function() {
				 if ( $("#klarna_getklarna_show_hide").is(":visible") ) {
				  $("#klarna_getklarna_show_hide").hide("slow");
			        $("#klarna_getklarna_link").html("' . addslashes ($logo) . '");
				} else {
				 $("#klarna_getklarna_show_hide").show("slow");
			       $("#klarna_getklarna_link").html("' . addslashes (JText::_ ('VMPAYMENT_KLARNA_GET_KLARNA_HIDE')) . '");
			    }
		    });
		});
';

		$doc = JFactory::getDocument ();
		$doc->addScriptDeclaration ($js);

		$html .= '<iframe src="' . $url . '" scrolling="yes" style="x-overflow: none;" frameborder="0" height="600px" width="850px"></iframe>';
		$html .= '</div>';
		return $html;
	}

}