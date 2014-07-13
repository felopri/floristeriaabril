<?php
/**
 * @version $Id: klarnacountrylogo.php 6369 2012-08-22 14:33:46Z alatak $
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

class JElementKlarnaCountryLogo extends JElement {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'KlarnacountryLogo';

	function fetchElement ($name, $value, &$node, $control_name) {

		$flagImg = JURI::root (TRUE) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower ($value) . '.png';
		return '<strong>'.JText::_ ('VMPAYMENT_KLARNA_CONF_SETTINGS_' . $value) . '</strong><img style="margin-left: 5px;margin-top: 15px;" src="' . $flagImg
			. '" />';

	}
}