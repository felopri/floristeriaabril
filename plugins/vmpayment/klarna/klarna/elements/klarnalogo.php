<?php
/**
 * @version $Id: klarnalogo.php 6501 2012-10-04 13:16:05Z alatak $
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
defined('JPATH_BASE') or die();


/**
 * Renders a label element
 */
if (JVM_VERSION === 2) {
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
    if (!class_exists('KlarnaHandler'))
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
} else {
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
    if (!class_exists('KlarnaHandler'))
    require ( JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
}

class JElementKlarnaLogo extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'KlarnaLogo';

	function fetchElement($name, $value, &$node, $control_name) {
		$countriesData = KlarnaHandler::countriesData();
		$logo = '<a href="https://www.klarna.com" target="_blank"><img src="https://cdn.klarna.com/public/images/SE/logos/v1/basic/SE_basic_logo_std_blue-black.png?width=100&" /></a> ';
		$flagImgHtml='';
		foreach ($countriesData as $countryData) {
			$flagImg = JURI::root(true) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower($countryData['language_code']) . '.png';
			$flagImgHtml.='<img style="margin-right: 5px;margin-top: 15px;" src="' . $flagImg . '"  alt="' . JText::_('VMPAYMENT_KLARNA_CONF_SETTINGS_' . $countryData['language_code']) . '"/>';
		}
		return $logo . $flagImgHtml;



	}
}