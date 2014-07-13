<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * Shipment plugin for general, rules-based shipments, like regular postal services with complex shipping cost structures
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage Plugins - shipment
 * @copyright Copyright (C) 2004-2012 VirtueMart Team - All rights reserved.
 * @copyright Copyright (C) 2013 Reinhold Kainhofer, office@open-tools.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 * @author Reinhold Kainhofer, based on the weight_countries shipping plugin by Valerie Isaksen
 *
 */
if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

if (!class_exists ('plgVmShipmentRules_Shipping_Base')) {
	require (dirname(__FILE__).DS.'rules_shipping_base.php');
}

/** Shipping costs according to general rules.
 *  Supported Variables: Weight, ZIP, Amount, Products (1 for each product, even if multiple ordered), Articles 
 *  Assignable variables: Shipping, Name
 */
class plgVmShipmentRules_Shipping extends plgVmShipmentRules_Shipping_Base {
	function __construct (& $subject, $config) {
		parent::__construct ($subject, $config);
	}

}

// No closing tag
