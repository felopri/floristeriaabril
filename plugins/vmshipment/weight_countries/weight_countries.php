<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * Shipment plugin for weight_countries shipments, like regular postal services
 *
 * @version $Id: weight_countries.php 6542 2012-10-15 17:55:59Z alatak $
 * @package VirtueMart
 * @subpackage Plugins - shipment
 * @copyright Copyright (C) 2004-2012 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 * @author Valerie Isaksen
 *
 */
if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

/**
 *
 */
class plgVmShipmentWeight_countries extends vmPSPlugin {

	// instance of class
	public static $_this = FALSE;

	/**
	 * @param object $subject
	 * @param array  $config
	 */
	function __construct (& $subject, $config) {

		//if (self::$_this)
		//   return self::$_this;
		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);

		// 		self::$_this
		//$this->createPluginTable($this->_tablename);
		//self::$_this = $this;
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 *
	 * @author Valérie Isaksen
	 */
	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL ('Shipment Weight Countries Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields () {

		$SQLfields = array(
			'id'                           => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'          => 'int(11) UNSIGNED',
			'order_number'                 => 'char(32)',
			'virtuemart_shipmentmethod_id' => 'mediumint(1) UNSIGNED',
			'shipment_name'                => 'varchar(5000)',
			'order_weight'                 => 'decimal(10,4)',
			'shipment_weight_unit'         => 'char(3) DEFAULT \'KG\'',
			'shipment_cost'                => 'decimal(10,2)',
			'shipment_package_fee'         => 'decimal(10,2)',
			'tax_id'                       => 'smallint(1)'
		);
		return $SQLfields;
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the shipment-specific data.
	 *
	 * @param integer $virtuemart_order_id The order ID
	 * @param integer $virtuemart_shipmentmethod_id The selected shipment method id
	 * @param string  $shipment_name Shipment Name
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmOnShowOrderFEShipment ($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name) {

		$this->onShowOrderFE ($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
	}

	/**
	 * This event is fired after the order has been stored; it gets the shipment method-
	 * specific data.
	 *
	 * @param int    $order_id The order_id being processed
	 * @param object $cart  the cart
	 * @param array  $order The actual order saved in the DB
	 * @return mixed Null when this method was not selected, otherwise true
	 * @author Valerie Isaksen
	 */
	function plgVmConfirmedOrder (VirtueMartCart $cart, $order) {

	
		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_shipmentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->shipment_element)) {
			return FALSE;
		}
		$values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$values['order_number'] = $order['details']['BT']->order_number;
		$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
		$values['shipment_name'] = $this->renderPluginName ($method);
		$values['order_weight'] = $this->getOrderWeight ($cart, $method->weight_unit);
		$values['shipment_weight_unit'] = $method->weight_unit;
		$values['shipment_cost'] = $method->cost;
		$values['shipment_package_fee'] = $method->package_fee;
		$values['tax_id'] = $method->tax_id;
		$this->storePSPluginInternalData ($values);

		return TRUE;
	}

	/**
	 * This method is fired when showing the order details in the backend.
	 * It displays the shipment-specific data.
	 * NOTE, this plugin should NOT be used to display form fields, since it's called outside
	 * a form! Use plgVmOnUpdateOrderBE() instead!
	 *
	 * @param integer $virtuemart_order_id The order ID
	 * @param integer $virtuemart_shipmentmethod_id The order shipment method ID
	 * @param object  $_shipInfo Object with the properties 'shipment' and 'name'
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderBEShipment ($virtuemart_order_id, $virtuemart_shipmentmethod_id) {

		if (!($this->selectedThisByMethodId ($virtuemart_shipmentmethod_id))) {
			return NULL;
		}
		$html = $this->getOrderShipmentHtml ($virtuemart_order_id);
		return $html;
	}

	/**
	 * @param $virtuemart_order_id
	 * @return string
	 */
	function getOrderShipmentHtml ($virtuemart_order_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($q);
		if (!($shipinfo = $db->loadObject ())) {
			vmWarn (500, $q . " " . $db->getErrorMsg ());
			return '';
		}

		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		$currency = CurrencyDisplay::getInstance ();
		$tax = ShopFunctions::getTaxByID ($shipinfo->tax_id);
		$taxDisplay = is_array ($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
		$taxDisplay = ($taxDisplay == -1) ? JText::_ ('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

		$html = '<table class="adminlist">' . "\n";
		$html .= $this->getHtmlHeaderBE ();
		$html .= $this->getHtmlRowBE ('WEIGHT_COUNTRIES_SHIPPING_NAME', $shipinfo->shipment_name);
		$html .= $this->getHtmlRowBE ('WEIGHT_COUNTRIES_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit ($shipinfo->shipment_weight_unit));
		$html .= $this->getHtmlRowBE ('WEIGHT_COUNTRIES_COST', $currency->priceDisplay ($shipinfo->shipment_cost));
		$html .= $this->getHtmlRowBE ('WEIGHT_COUNTRIES_PACKAGE_FEE', $currency->priceDisplay ($shipinfo->shipment_package_fee));
		$html .= $this->getHtmlRowBE ('WEIGHT_COUNTRIES_TAX', $taxDisplay);
		$html .= '</table>' . "\n";

		return $html;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
	function getCosts (VirtueMartCart $cart, $method, $cart_prices) {

		if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment) {
			return 0;
		} else {
			return $method->cost + $method->package_fee;
		}
	}

	/**
	 * @param \VirtueMartCart $cart
	 * @param int             $method
	 * @param array           $cart_prices
	 * @return bool
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {

		$this->convert ($method);

		$orderWeight = $this->getOrderWeight ($cart, $method->weight_unit);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		$type = (($cart->ST == 0) ? 'BT' : 'ST');
		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array ($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array ($address)) {
			// there are some address dependant conditions, redirect then
			/*
			if ($method->zip_start or $method->zip_stop or empty($countries)) {
				$mainframe = JFactory::getApplication ();
				$redirectMsg = JText::_ ('VMSHIPMENT_WEIGHT_COUNTRIES_ADDRESS_FIRST');
				//vmWarn($redirectMsg);
				$mainframe->redirect (JRoute::_ ('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT'), $redirectMsg);
			}
			*/
			$address = array();
			$address['zip'] = 0;
			$address['virtuemart_country_id'] = 0;
		}

		$weight_cond = $this->testRange($orderWeight,$method,'weight_start','weight_stop','weight');
		$nbproducts_cond = $this->_nbproductsCond ($cart, $method);
		if(isset($cart_prices['salesPrice'])){
			$orderamount_cond = $this->testRange($cart_prices['salesPrice'],$method,'orderamount_start','orderamount_stop','order amount');
		} else {
			$orderamount_cond = FALSE;
		}

		$userFieldsModel =VmModel::getModel('Userfields');
		if ($userFieldsModel->fieldPublished('zip', $type)){
			if (isset($address['zip'])) {

				$zip_cond = $this->testRange($address['zip'],$method,'zip_start','zip_stop','zip');
			} else {

				$zip_cond = false;
			}
		} else {
			$zip_cond = true;
		}

		if ($userFieldsModel->fieldPublished('virtuemart_country_id', $type)){

			if (!isset($address['virtuemart_country_id'])) {
				$address['virtuemart_country_id'] = 0;
			}


			if (in_array ($address['virtuemart_country_id'], $countries) || count ($countries) == 0) {

				//vmdebug('checkConditions '.$method->shipment_name.' fit ',$weight_cond,(int)$zip_cond,$nbproducts_cond,$orderamount_cond);
				vmdebug('shipmentmethod '.$method->shipment_name.' = TRUE for variable virtuemart_country_id = '.implode($countries,', ').', Reason: Country in rule or none set');
				$country_cond = true;
			}
			else{
				vmdebug('shipmentmethod '.$method->shipment_name.' = FALSE for variable virtuemart_country_id = '.implode($countries,', ').', Reason: Country does not fit');
				$country_cond = false;
			}
		} else {
			vmdebug('shipmentmethod '.$method->shipment_name.' = TRUE for variable virtuemart_country_id, Reason: no boundary conditions set');
			$country_cond = true;
		}

		$allconditions = (int) $weight_cond + (int)$zip_cond + (int)$nbproducts_cond + (int)$orderamount_cond + (int)$country_cond;
		if($allconditions === 5){
			return TRUE;
		} else {

			return FALSE;
		}

		vmdebug('checkConditions '.$method->name.' does not fit');
		return FALSE;
	}

	/**
	 * @param $method
	 */
	function convert (&$method) {

		//$method->weight_start = (float) $method->weight_start;
		//$method->weight_stop = (float) $method->weight_stop;
		$method->orderamount_start = (float)$method->orderamount_start;
		$method->orderamount_stop = (float)$method->orderamount_stop;
		$method->zip_start = (int)$method->zip_start;
		$method->zip_stop = (int)$method->zip_stop;
		$method->nbproducts_start = (int)$method->nbproducts_start;
		$method->nbproducts_stop = (int)$method->nbproducts_stop;
		$method->free_shipment = (float)$method->free_shipment;
	}

	/**
	 * @param $cart
	 * @param $method
	 * @return bool
	 */
	private function _nbproductsCond ($cart, $method) {

		if (!isset($method->nbproducts_start) and !isset($method->nbproducts_stop)) {
			vmdebug('_nbproductsCond',$method);
			return true;
		}

		$nbproducts = 0;
		foreach ($cart->products as $product) {
			$nbproducts += $product->quantity;
		}

		if ($nbproducts) {

			$nbproducts_cond = $this->testRange($nbproducts,$method,'nbproducts_start','nbproducts_stop','products quantity');

		} else {
			$nbproducts_cond = false;
		}

		return $nbproducts_cond;
	}


	private function testRange($value, $method, $floor, $ceiling,$name){

		$cond = true;
		if(!empty($method->$floor) and !empty($method->$ceiling)){
			$cond = (($value >= $method->$floor AND $value <= $method->$ceiling));
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is NOT within Range of the condition from '.$method->$floor.' to '.$method->$ceiling;
			} else {
				$result = 'TRUE';
				$reason = 'is within Range of the condition from '.$method->$floor.' to '.$method->$ceiling;
			}
		} else if(!empty($method->$floor)){
			$cond = ($value >= $method->$floor);
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is not at least '.$method->$floor;
			} else {
				$result = 'TRUE';
				$reason = 'is over min limit '.$method->$floor;
			}
		} else if(!empty($method->$ceiling)){
			$cond = ($value <= $method->$ceiling);
			if(!$cond){
				$result = 'FALSE';
				$reason = 'is over '.$method->$ceiling;
			} else {
				$result = 'TRUE';
				$reason = 'is lower than the set '.$method->$ceiling;
			}
		} else {
			$result = 'TRUE';
			$reason = 'no boundary conditions set';
		}

		vmdebug('shipmentmethod '.$method->shipment_name.' = '.$result.' for variable '.$name.' = '.$value.' Reason: '.$reason);
		return $cond;
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallShipmentPluginTable ($jplugin_id) {

		return $this->onStoreInstallPluginTable ($jplugin_id);
	}

	/**
	 * @param VirtueMartCart $cart
	 * @return null
	 */
	public function plgVmOnSelectCheckShipment (VirtueMartCart &$cart) {

		return $this->OnSelectCheck ($cart);
	}

	/**
	 * plgVmDisplayListFE
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmDisplayListFEShipment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE ($cart, $selected, $htmlIn);
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmOnSelectedCalculatePriceShipment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * plgVmOnCheckAutomaticSelected
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedShipment (VirtueMartCart $cart, array $cart_prices = array(), &$shipCounter) {

		if ($shipCounter > 1) {
			return 0;
		}

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $shipCounter);
	}

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrint ($order_number, $method_id) {

		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	function plgVmDeclarePluginParamsShipment ($name, $id, &$data) {

		return $this->declarePluginParams ('shipment', $name, $id, $data);
	}


	/**
	 * @author Max Milbers
	 * @param $data
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginShipment(&$data,&$table){

		$name = $data['shipment_element'];
		$id = $data['shipment_jplugin_id'];

		if (!empty($this->_psType) and !$this->selectedThis ($this->_psType, $name, $id)) {
			return FALSE;
		} else {
			$toConvert = array('weight_start','weight_stop','orderamount_start','orderamount_stop');
			foreach($toConvert as $field){

				if(!empty($data[$field])){
					$data[$field] = str_replace(array(',',' '),array('.',''),$data[$field]);
				} else {
					unset($data[$field]);
				}
			}

			$data['nbproducts_start'] = (int) $data['nbproducts_start'];
			$data['nbproducts_stop'] = (int) $data['nbproducts_stop'];
			//I dont see a reason for it
			/*$toConvert = array('zip_start','zip_stop','nbproducts_start' , 'nbproducts_stop');
			foreach($toConvert as $field){
				if(!empty($data[$field])){
					$data[$field] = str_replace( ' ','',$data[$field]);
				} else {
					unset($data[$field]);
				}
				if (preg_match ("/[^0-9]/", $data[$field])) {
					vmWarn( JText::sprintf('VMSHIPMENT_WEIGHT_COUNTRIES_NUMERIC', JText::_('VMSHIPMENT_WEIGHT_COUNTRIES_'.$field) ) );
				}
			}*/
			//Reasonable tests:
			if(!empty($data['zip_start']) and !empty($data['zip_stop']) and (int)$data['zip_start']>=(int)$data['zip_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_ZIP_CONDITION_WRONG');
			}
			if(!empty($data['weight_start']) and !empty($data['weight_stop']) and (float)$data['weight_start']>=(float)$data['weight_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_WEIGHT_CONDITION_WRONG');
			}

			if(!empty($data['orderamount_start']) and !empty($data['orderamount_stop']) and (float)$data['orderamount_start']>=(float)$data['orderamount_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_AMOUNT_CONDITION_WRONG');
			}

			if(!empty($data['nbproducts_start']) and !empty($data['nbproducts_stop']) and (float)$data['nbproducts_start']>=(float)$data['nbproducts_stop']){
				vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_NBPRODUCTS_CONDITION_WRONG');
			}

			return $this->setOnTablePluginParams ($name, $id, $table);
		}
	}


}

// No closing tag
