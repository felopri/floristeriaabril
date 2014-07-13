<?php
defined('_JEXEC') or die();
/**
 *
 * @package	VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen, Reinhold Kainhofer
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
/*
 * This class is used by VirtueMart Payment or Shipment Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
 
 
if (!class_exists('VmConfig'))
    require(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');

if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');

class JElementVmLengthUnit extends JElement {

    /**
     * Element name
     * @access	protected
     * @var		string
     */
    var $_name = 'LengthUnit';

    function fetchElement($nm, $selected, &$node, $control_name) {
		// For now, this is a modified copy of ShopFunctions::renderLWHUnitList to use JHTML::_ instead of VmHTML 
		// (which would NOT remove the [ and ] from the ID and thus break the javascript magic!)
		if (!class_exists ('VmHTML')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
		}
		$name = $control_name . '[' . $nm . ']';

		$lwh_unit_default = array('M' => JText::_ ('COM_VIRTUEMART_UNIT_NAME_M')
		, 'CM'                        => JText::_ ('COM_VIRTUEMART_UNIT_NAME_CM')
		, 'MM'                        => JText::_ ('COM_VIRTUEMART_UNIT_NAME_MM')
		, 'YD'                        => JText::_ ('COM_VIRTUEMART_UNIT_NAME_YARD')
		, 'FT'                        => JText::_ ('COM_VIRTUEMART_UNIT_NAME_FOOT')
		, 'IN'                        => JText::_ ('COM_VIRTUEMART_UNIT_NAME_INCH')
		);
		foreach ($lwh_unit_default as  $key => $value) {
			$lu_list[] = JHTML::_ ('select.option', $key, $value, $name);
		}
		$listHTML = JHTML::_ ('Select.genericlist', $lu_list, $name, '', $name, 'text', $selected);
		return $listHTML;
    }

}