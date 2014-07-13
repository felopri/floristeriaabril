<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * Shipment plugin for general, rules-based shipments, like regular postal services with complex shipping cost structures
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage Plugins - shipment
 * @copyright Copyright (C) 2004-2012 VirtueMart Team - All rights reserved.
 * @copyright Copyright (C) 2013 Reinhold Kainhofer, reinhold@kainhofer.com
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
// Only declare the class once...
if (class_exists ('plgVmShipmentRules_Shipping_Base')) {
	return;
}


function is_equal($a, $b) {
	if (is_array($a) && is_array($b)) {
		return !array_diff($a, $b) && !array_diff($b, $a);
	} elseif (is_string($a) && is_string($b)) {
		return strcmp($a,$b) == 0;
	} else {
		return $a == $b;
	}
}
/** Shipping costs according to general rules.
 *  Supported Variables: Weight, ZIP, Amount, Products (1 for each product, even if multiple ordered), Articles
 *  Assignable variables: Shipping, Name
 */
class plgVmShipmentRules_Shipping_Base extends vmPSPlugin {

	/**
	 * @param object $subject
	 * @param array  $config
	 */
	function __construct (& $subject, $config) {
		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 *
	 * @author Valérie Isaksen
	 */
	public function getVmPluginCreateTableSQL () {
		return $this->createTableSQL ('Shipment Rules Table');
	}
	
	public function printWarning($message) {
		// Keep track of warning messages, so we don't print them twice:
		global $printed_warnings;
		if (!isset($printed_warnings))
			$printed_warnings = array();
		if (!in_array($message, $printed_warnings)) {
			JFactory::getApplication()->enqueueMessage($message, 'error');
			$printed_warnings[] = $message;
		}
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
			'rule_name'                    => 'varchar(500)',
			'order_weight'                 => 'decimal(10,4)',
			'order_articles'               => 'int(1)',
			'order_products'               => 'int(1)',
			'shipment_weight_unit'         => 'char(3) DEFAULT \'KG\'',
			'shipment_cost'                => 'decimal(10,2)',
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
		$values['rule_name'] = $method->rule_name;
		$values['order_weight'] = $this->getOrderWeight ($cart, $method->weight_unit);
		$values['order_articles'] = $this->getOrderArticles ($cart);
		$values['order_products'] = $this->getOrderProducts ($cart);
		$values['shipment_weight_unit'] = $method->weight_unit;
		$values['shipment_cost'] = $method->cost;
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
		$html .= $this->getHtmlRowBE ('RULES_SHIPPING_NAME', $shipinfo->shipment_name);
		$html .= $this->getHtmlRowBE ('RULES_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit ($shipinfo->shipment_weight_unit));
		$html .= $this->getHtmlRowBE ('RULES_ARTICLES', $shipinfo->order_articles . '/' . $shipinfo->order_products);
		$html .= $this->getHtmlRowBE ('RULES_COST', $currency->priceDisplay ($shipinfo->shipment_cost));
		$html .= $this->getHtmlRowBE ('RULES_TAX', $taxDisplay);
		$html .= '</table>' . "\n";

		return $html;
	}
	
	/** Include the rule name in the shipment name */
	protected function renderPluginName ($plugin) {
		$return = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';
		// 		$params = new JParameter($plugin->$plugin_params);
		// 		$logo = $params->get($this->_psType . '_logos');
		$logosFieldName = $this->_psType . '_logos';
		$logos = $plugin->$logosFieldName;
		if (!empty($logos)) {
			$return = $this->displayLogos ($logos) . ' ';
		}
		if (!empty($plugin->$plugin_desc)) {
			$description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc . '</span>';
		}
		$rulename='';
		if (!empty($plugin->rule_name)) {
			$rulename=" (".htmlspecialchars($plugin->rule_name).")";
		}
		$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . $rulename.'</span>' . $description;
		return $pluginName;
	}



	protected function findMatchingRule (&$cartvals, $method) {
		$result = array("rule"=>Null, "rule_name"=>"", "modifiers"=>array());
		// TODO: Handle modifiers
		foreach ($method->rules as $r) {
			// If the rule is a variable definition, it will NOT match, but modify the $cartvals array for the next rules
			if ($r->matches($cartvals)) {
				$result["rule"] = $r;
				$result["rule_name"] = $r->getRuleName($cartvals);
				return $result;
			}
		}
		// None of the rules matched, so return NULL;
		return NULL;
	}

	/**
	 * @param \VirtueMartCart $cart
	 * @param int             $method
	 * @param array           $cart_prices
	 * @return bool
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {
		if (!isset($method->rules)) $this->parseMethodRules($method);

		$cartvals = $this->getCartValues ($cart, $method, $cart_prices);
		$match = $this->findMatchingRule ($cartvals, $method);
		if ($match) {
			$method->matched_rule = $match["rule"];
			$method->rule_name = $match["rule_name"];
			// If NoShipping is set, this method should NOT offer any shipping at all, so return FALSE, otherwise TRUE
			// If the rule has a name, print it as warning (otherwise don't print anything)
			if ($method->matched_rule->isNoShipping()) {
				if (!empty($method->rule_name))
					$this->printWarning(JText::sprintf('VMSHIPMENT_RULES_NOSHIPPING_MESSAGE', $method->rule_name));
				vmdebug('checkConditions '.$method->shipment_name.' indicates NoShipping for rule "'.$method->rule_name.'" ('.$method->matched_rule->rulestring.').');
				return FALSE;
			} else {
				return TRUE;
			}
		}
		vmdebug('checkConditions '.$method->shipment_name.' does not fit');
		return FALSE;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
	function getCosts (VirtueMartCart $cart, $method, $cart_prices) {
		if (!isset($method->rules)) $this->parseMethodRules($method);
		$cartvals = $this->getCartValues ($cart, $method, $cart_prices);
		$match = $this->findMatchingRule ($cartvals, $method);
		if ($match) {
			$r = $match["rule"];
			$rulename = $match["rule_name"];
			vmdebug('Rule '.$rulename.' ('.$r->rulestring.') matched.');
			$method->tax_id = $r->tax_id;
			$method->matched_rule = $r;
			$method->rule_name = $rulename;
			$method->cost = $r->getShippingCosts($cartvals);
			$method->includes_tax = $r->includes_tax;
			return $method->cost;
		}
		
		vmdebug('getCosts '.$method->name.' does not return shipping costs');
		return 0;
	}

	/**
	 * update the plugin cart_prices (
	 *
	 * @author Valérie Isaksen (original), Reinhold Kainhofer (tax calculations from shippingWithTax)
	 *
	 * @param $cart_prices: $cart_prices['salesPricePayment'] and $cart_prices['paymentTax'] updated. Displayed in the cart.
	 * @param $value :   fee
	 * @param $tax_id :  tax id
	 */

	function setCartPrices (VirtueMartCart $cart, &$cart_prices, $method) {


		if (!class_exists ('calculationHelper')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		}
		$_psType = ucfirst ($this->_psType);
		$calculator = calculationHelper::getInstance ();

		$cart_prices[$this->_psType . 'Value'] = $calculator->roundInternal ($this->getCosts ($cart, $method, $cart_prices), 'salesPrice');

		if($this->_psType=='payment'){
			$cartTotalAmountOrig=$this->getCartAmount($cart_prices);
			$cartTotalAmount=($cartTotalAmountOrig + $method->cost_per_transaction) / (1 -($method->cost_percent_total * 0.01));
			$cart_prices[$this->_psType . 'Value'] = $cartTotalAmount - $cartTotalAmountOrig;
		}


		$taxrules = array();
		if(isset($method->tax_id) and (int)$method->tax_id === -1){

		} else if (!empty($method->tax_id)) {
			$cart_prices[$this->_psType . '_calc_id'] = $method->tax_id;

			$db = JFactory::getDBO ();
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $method->tax_id . '" ';
			$db->setQuery ($q);
			$taxrules = $db->loadAssocList ();

			if(!empty($taxrules) ){
				foreach($taxrules as &$rule){
					if(!isset($rule['subTotal'])) $rule['subTotal'] = 0;
					if(!isset($rule['taxAmount'])) $rule['taxAmount'] = 0;
					$rule['subTotalOld'] = $rule['subTotal'];
					$rule['taxAmountOld'] = $rule['taxAmount'];
					$rule['taxAmount'] = 0;
					$rule['subTotal'] = $cart_prices[$this->_psType . 'Value'];
				}
			}
		} else {
			$taxrules = array_merge($calculator->_cartData['VatTax'],$calculator->_cartData['taxRulesBill']);

			if(!empty($taxrules) ){
				$denominator = 0.0;
				foreach($taxrules as &$rule){
					//$rule['numerator'] = $rule['calc_value']/100.0 * $rule['subTotal'];
					if(!isset($rule['subTotal'])) $rule['subTotal'] = 0;
					if(!isset($rule['taxAmount'])) $rule['taxAmount'] = 0;
					$denominator += ($rule['subTotal']-$rule['taxAmount']);
					$rule['subTotalOld'] = $rule['subTotal'];
					$rule['subTotal'] = 0;
					$rule['taxAmountOld'] = $rule['taxAmount'];
					$rule['taxAmount'] = 0;
					//$rule['subTotal'] = $cart_prices[$this->_psType . 'Value'];
				}
				if(empty($denominator)){
					$denominator = 1;
				}

				foreach($taxrules as &$rule){
					$frac = ($rule['subTotalOld']-$rule['taxAmountOld'])/$denominator;
					$rule['subTotal'] = $cart_prices[$this->_psType . 'Value'] * $frac;
					vmdebug('Part $denominator '.$denominator.' $frac '.$frac,$rule['subTotal']);
				}
			}
		}


		if(empty($method->cost_per_transaction)) $method->cost_per_transaction = 0.0;
		if(empty($method->cost_percent_total)) $method->cost_percent_total = 0.0;

		if (count ($taxrules) > 0 ) {

			// BEGIN_RK_CHANGES
			if ($method->includes_tax) {

				$cart_prices['salesPrice' . $_psType] = $calculator->roundInternal ($cart_prices[$this->_psType . 'Value'], 'salesPrice');
				// Calculate the tax from the final sales price:
				$calculator->setRevert (true);
				$cart_prices[$this->_psType . 'Value'] = $calculator->roundInternal ($calculator->executeCalculation($taxrules, $cart_prices[$this->_psType . 'Value'], true));
				$cart_prices[$this->_psType . 'Tax'] = $cart_prices['salesPrice' . $_psType] - $cart_prices[$this->_psType . 'Value'];
				$calculator->setRevert (false);
			} else {
			// END_RK_CHANGES
			$cart_prices['salesPrice' . $_psType] = $calculator->roundInternal ($calculator->executeCalculation ($taxrules, $cart_prices[$this->_psType . 'Value'],true,false), 'salesPrice');
			//vmdebug('I am in '.get_class($this).' and have this rules now',$taxrules,$cart_prices[$this->_psType . 'Value'],$cart_prices['salesPrice' . $_psType]);
			$cart_prices[$this->_psType . 'Tax'] = $calculator->roundInternal (($cart_prices['salesPrice' . $_psType] -  $cart_prices[$this->_psType . 'Value']), 'salesPrice');
			// BEGIN_RK_CHANGES
			}
			// END_RK_CHANGES
			reset($taxrules);
			$taxrule =  current($taxrules);
			$cart_prices[$this->_psType . '_calc_id'] = $taxrule['virtuemart_calc_id'];

			foreach($taxrules as &$rule){
				if(isset($rule['subTotalOld'])) $rule['subTotal'] += $rule['subTotalOld'];
				if(isset($rule['taxAmountOld'])) $rule['taxAmount'] += $rule['taxAmountOld'];
			}

		} else {
			$cart_prices['salesPrice' . $_psType] = $cart_prices[$this->_psType . 'Value'];
			$cart_prices[$this->_psType . 'Tax'] = 0;
			$cart_prices[$this->_psType . '_calc_id'] = 0;
		}


		return $cart_prices['salesPrice' . $_psType];

	}

	protected function createMethodRule ($r, $countries, $tax) {
		return new ShippingRule($r, $countries, $tax);
	}

	private function parseMethodRule ($rulestring, $countries, $tax, &$method) {
		$rules1 = preg_split("/(\r\n|\n|\r)/", $rulestring);
		foreach ($rules1 as $r) {
			// Ignore empty lines
			if (empty($r)) continue;
			$method->rules[] = $this->createMethodRule ($r, $countries, $tax);
		}
	}
	
	protected function parseMethodRules (&$method) {
		if (!isset($method->rules)) $method->rules = array();
		$this->parseMethodRule ($method->rules1, $method->countries1, $method->tax_id1, $method);
		$this->parseMethodRule ($method->rules2, $method->countries2, $method->tax_id2, $method);
		$this->parseMethodRule ($method->rules3, $method->countries3, $method->tax_id3, $method);
		$this->parseMethodRule ($method->rules4, $method->countries4, $method->tax_id4, $method);
		$this->parseMethodRule ($method->rules5, $method->countries5, $method->tax_id5, $method);
		$this->parseMethodRule ($method->rules6, $method->countries6, $method->tax_id6, $method);
		$this->parseMethodRule ($method->rules7, $method->countries7, $method->tax_id7, $method);
		$this->parseMethodRule ($method->rules8, $method->countries8, $method->tax_id8, $method);
	}

	protected function getOrderArticles (VirtueMartCart $cart) {
		/* Cache the value in a static variable and calculate it only once! */
		static $articles = 0;
		if(empty($articles) and count($cart->products)>0){
			foreach ($cart->products as $product) {
				$articles += $product->quantity;
			}
		}
		return $articles;
	}

	protected function getOrderProducts (VirtueMartCart $cart) {
		/* Cache the value in a static variable and calculate it only once! */
		static $products = 0;
		if(empty($products) and count($cart->products)>0){
			$products = count($cart->products);
		}
		return $products;
	}

	protected function getOrderDimensions (VirtueMartCart $cart, $length_dimension) {
		/* Cache the value in a static variable and calculate it only once! */
		static $calculated = 0;
		static $dimensions=array(
			'volume' => 0,
			'maxvolume' => 0, 'minvolume' => 9999999999,
			'maxlength' => 0, 'minlength' => 9999999999, 'totallength' => 0,
			'maxwidth'  => 0, 'minwidth' => 9999999999,  'totalwidth'  => 0,
			'maxheight' => 0, 'minheight' => 9999999999, 'totalheight' => 0,
			'maxpackaging' => 0, 'minpackaging' => 9999999999, 'totalpackaging' => 0,
		);
		if ($calculated==0) {
			$calculated=1;
			foreach ($cart->products as $product) {
	
				$l = ShopFunctions::convertDimensionUnit ($product->product_length, $product->product_lwh_uom, $length_dimension);
				$w = ShopFunctions::convertDimensionUnit ($product->product_width, $product->product_lwh_uom, $length_dimension);
				$h = ShopFunctions::convertDimensionUnit ($product->product_height, $product->product_lwh_uom, $length_dimension);

				$volume = $l * $w * $h;
				$dimensions['volume'] += $volume * $product->quantity;
				$dimensions['maxvolume'] = max ($dimensions['maxvolume'], $volume);
				$dimensions['minvolume'] = min ($dimensions['minvolume'], $volume);
				
				$dimensions['totallength'] += $l * $product->quantity;
				$dimensions['maxlength'] = max ($dimensions['maxlength'], $l);
				$dimensions['minlength'] = min ($dimensions['minlength'], $l);
				$dimensions['totalwidth'] += $w * $product->quantity;
				$dimensions['maxwidth'] = max ($dimensions['maxwidth'], $w);
				$dimensions['minwidth'] = min ($dimensions['minwidth'], $w);
				$dimensions['totalheight'] += $h * $product->quantity;
				$dimensions['maxheight'] = max ($dimensions['maxheight'], $h);
				$dimensions['minheight'] = min ($dimensions['minheight'], $h);
				$dimensions['totalpackaging'] += $product->packaging * $product->quantity;
				$dimensions['maxpackaging'] = max ($dimensions['maxpackaging'], $product->packaging);
				$dimensions['minpackaging'] = min ($dimensions['minpackaging'], $product->packaging);
			}
		}

		return $dimensions;
	}
	
	function getOrderWeights (VirtueMartCart $cart, $weight_unit) {
		static $calculated = 0;
		static $dimensions=array(
			'weight' => 0,
			'maxweight' => 0, 'minweight' => 9999999999,
		);
		if ($calculated==0 && count($cart->products)>0) {
			$calculated = 1;
			foreach ($cart->products as $product) {
				$w = ShopFunctions::convertWeigthUnit ($product->product_weight, $product->product_weight_uom, $weight_unit);
				$dimensions['maxweight'] = max ($dimensions['maxweight'], $w);
				$dimensions['minweight'] = min ($dimensions['minweight'], $w);
				$dimensions['weight'] += $w * $product->quantity;
			}
		}
		return $dimensions;
	}
	
	function getOrderListProperties (VirtueMartCart $cart) {
		$categories = array();
		$vendors = array();
		$skus = array();
		$manufacturers = array();
		foreach ($cart->products as $product) {
			$skus[] = $product->product_sku;
			$categories = array_merge ($categories, $product->categories);
			$vendors[] = $product->virtuemart_vendor_id;
			if ($product->virtuemart_manufacturer_id) {
				$manufacturers[] = $product->virtuemart_manufacturer_id;
			}
		}
		$categories = array_unique($categories);
		$vendors = array_unique($vendors);
		return array ('skus'=>$skus, 
			      'categories'=>$categories,
			      'vendors'=>$vendors,
			      'manufacturers'=>$manufacturers,
		);
	}
	
	function getOrderCountryState (VirtueMartCart $cart, $address) {
		$data = array (
			'countryid' => 0, 'country' => '', 'country2' => '', 'country3' => '',
			'stateid'   => 0, 'state'   => '', 'state2'   => '', 'state3'   => '',
		);
		
		$countriesModel = VmModel::getModel('country');
		if (isset($address['virtuemart_country_id'])) {
			$data['countryid'] = $address['virtuemart_country_id'];
			$countriesModel->setId($address['virtuemart_country_id']);
			$country = $countriesModel->getData();
			if (!empty($country)) {
				$data['country'] = $country->country_name;
				$data['country2'] = $country->country_2_code;
				$data['country3'] = $country->country_3_code;
			}
		}
		
		$statesModel = VmModel::getModel('state');
		if (isset($address['virtuemart_state_id'])) {
			$data['stateid'] = $address['virtuemart_state_id'];
			$statesModel->setId($address['virtuemart_state_id']);
			$state = $statesModel->getData();
			if (!empty($state)) {
				$data['state'] = $state->state_name;
				$data['state2'] = $state->state_2_code;
				$data['state3'] = $state->state_3_code;
			}
		}
		
		return $data;

	}
	
	/** Allow child classes to add additional variables for the rules
	 */
	protected function addCustomCartValues (VirtueMartCart $cart, $cart_prices, &$values) {
	}
	protected function getCartValues (VirtueMartCart $cart, $method, $cart_prices) {
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
		$zip = isset($address['zip'])?trim($address['zip']):'';
		$cartvals = array('zip'=>$zip,
				  'zip1'=>substr($zip,0,1),
				  'zip2'=>substr($zip,0,2),
				  'zip3'=>substr($zip,0,3),
				  'zip4'=>substr($zip,0,4),
				  'zip5'=>substr($zip,0,5),
				  'zip6'=>substr($zip,0,6),
				  'city'=>isset($address['city'])?trim($address['city']):'',
				  'articles'=>$this->getOrderArticles($cart),
				  'products'=>$this->getOrderProducts($cart),
				  'amount'=>$cart_prices['salesPrice'],
				  'amountwithtax'=>$cart_prices['salesPrice'],
				  'amountwithouttax'=>$cart_prices['priceWithoutTax'],

				  'baseprice'=>$cart_prices['basePrice'],
				  'basepricewithtax'=>$cart_prices['basePriceWithTax'],
				  'discountedpricewithouttax'=>$cart_prices['discountedPriceWithoutTax'],
				  'salesprice'=>$cart_prices['salesPrice'],
				  'taxamount'=>$cart_prices['taxAmount'],
				  'salespricewithdiscount'=>$cart_prices['salesPriceWithDiscount'],
				  'discountamount'=>$cart_prices['discountAmount'],
				  'pricewithouttax'=>$cart_prices['priceWithoutTax'],
			);
		
		// Add 'skus', 'categories', 'vendors' variables:
		$cartvals = array_merge ($cartvals, $this->getOrderListProperties ($cart));
		// Add country / state variables:
		$cartvals = array_merge ($cartvals, $this->getOrderCountryState ($cart, $address));
		// Add Total/Min/Max weight and dimension variables:
		$cartvals = array_merge ($cartvals, $this->getOrderWeights ($cart, $method->weight_unit));
		$cartvals = array_merge ($cartvals, $this->getOrderDimensions ($cart, $method->length_unit));
		// Let child classes update the $cartvals array, or add new variables
		$this->addCustomCartValues($cart, $cart_prices, $cartvals);
		// Add the whole list of cart value to the values, so we can print them out as a debug statement!
		$cartvals['values_debug'] = print_r($cartvals,1);
		$cartvals['values'] = $cartvals;
// JFactory::getApplication()->enqueueMessage("<pre>cart values: ".print_r($cartvals,1)."</pre>", 'error');
		return $cartvals;
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

	/* This function is needed in VM 2.0.14 etc. because otherwise the params are not saved */
	function plgVmSetOnTablePluginParamsShipment ($name, $id, &$table) {

		return $this->setOnTablePluginParams ($name, $id, $table);
	}

	function plgVmSetOnTablePluginShipment(&$data,&$table){

		$name = $data['shipment_element'];
		$id = $data['shipment_jplugin_id'];

		if (!empty($this->_psType) and !$this->selectedThis ($this->_psType, $name, $id)) {
			return FALSE;
		}
		if (isset($data['rules1'])) {
			// Try to parse all rules (and spit out error) to inform the user:
			$method = new StdClass ();
			$this->parseMethodRule ($data['rules1'], isset($data['countries1'])?$data['countries1']:array(), $data['tax_id1'], $method);
			$this->parseMethodRule ($data['rules2'], isset($data['countries2'])?$data['countries2']:array(), $data['tax_id2'], $method);
			$this->parseMethodRule ($data['rules3'], isset($data['countries3'])?$data['countries3']:array(), $data['tax_id3'], $method);
			$this->parseMethodRule ($data['rules4'], isset($data['countries4'])?$data['countries4']:array(), $data['tax_id4'], $method);
			$this->parseMethodRule ($data['rules5'], isset($data['countries5'])?$data['countries5']:array(), $data['tax_id5'], $method);
			$this->parseMethodRule ($data['rules6'], isset($data['countries6'])?$data['countries6']:array(), $data['tax_id6'], $method);
			$this->parseMethodRule ($data['rules7'], isset($data['countries7'])?$data['countries7']:array(), $data['tax_id7'], $method);
			$this->parseMethodRule ($data['rules8'], isset($data['countries8'])?$data['countries8']:array(), $data['tax_id8'], $method);
		}
		$ret=$this->setOnTablePluginParams ($name, $id, $table);
		return $ret;
	}

}

if (class_exists ('ShippingRule')) {
	return;
}

class ShippingRule {
	var $rulestring = '';
	var $countries = array();
	var $tax_id = 0;
	var $conditions = array();
	var $shipping = 0;
	var $includes_tax = 0;
	var $name = '';
	var $is_definition = 0;
	
	function __construct ($rule, $countries, $tax_id) {
		if (is_array($countries)) {
			$this->countries = $countries;
		} elseif (!empty($countries)) {
			$this->countries[0] = $countries;
		}
		$this->tax_id = $tax_id;
		$this->rulestring = $rule;
		$this->parseRule($rule);
	}
	
	function parseRule($rule) {
		$ruleparts=explode(';', $rule);
		foreach ($ruleparts as $p) {
			$this->parseRulePart($p);
		}
	}
	
	function handleAssignment ($var, $value, $rulepart) {
		switch (strtolower($var)) {
			case 'name': $this->name = $value; break;
			case 'shipping': $this->shipping = $value; $this->includes_tax = False; break;
			case 'shippingwithtax': $this->shipping = $value; $this->includes_tax = True; break;
			case 'variable':   // Variable=... is the same as Definition=...
			case 'definition': $this->name = strtolower($value); $this->is_definition = True; break;
			case 'value': $this->shipping = $value; break; // definition values are also stored in the shipping member!
			case 'comment': break; // Completely ignore all comments!
			case 'condition': $this->conditions[] = $value; break;
			default: JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_UNKNOWN_VARIABLE', $var, $rulepart), 'error');
		}
	}
	
	
	function tokenize_expression ($expression) {
		// First, extract all strings, delimited by quotes, then all text operators 
		// (OR, AND, in; but make sure we don't capture parts of words, so we need to 
		// use lookbehind/lookahead patterns to exclude OR following another letter 
		// or followed by another letter) and then all arithmetic operators
		$re = '/\s*("[^"]*"|\'[^\']*\'|<=|=>|>=|=<|<>|!=|==|<|=|>)\s*/i';
		$atoms = preg_split($re, $expression, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		// JFactory::getApplication()->enqueueMessage("TOKENIZING '$expression' returns: <pre>".print_r($atoms,1)."</pre>", 'error');
		return $atoms;
	}
	
	function parseRulePart($rulepart) {
		/* In the basic version, we only split at the comparison operators and assume each term on the LHS and RHS is one variable or constant */
		/* In the advanced version, all conditions and costs can be given as a full mathematical expression */
		/* Both versions create an expression tree, which can be easily evaluated in evaluateTerm */
		$rulepart = trim($rulepart);
		if (empty($rulepart)) return;

		
		// Special-case the name assignment, where we don't want to interpret the value as an arithmetic expression!
		if (preg_match('/^\s*(name|variable|definition)\s*=\s*(["\']?)(.*)\2\s*$/i', $rulepart, $matches)) {
			$this->handleAssignment ($matches[1], $matches[3], $rulepart);
			return;
		}

		// Split at all operators:
		$atoms = $this->tokenize_expression ($rulepart);
		
		/* TODO: Starting from here, the advanced plugin is different! */
		$operators = array('<', '<=', '=', '>', '>=', '=>', '=<', '<>', '!=', '==');
		if (count($atoms)==1) {
			$this->shipping = $this->parseShippingTerm($atoms[0]);
		} elseif ($atoms[1]=='=') {
			$this->handleAssignment ($atoms[0], $atoms[2], $rulepart);
		} else {
			// Conditions, need at least three atoms!
			while (count($atoms)>1) {
				if (in_array ($atoms[1], $operators)) {
					$this->conditions[] = array($atoms[1], $this->parseShippingTerm($atoms[0]), $this->parseShippingTerm($atoms[2]));
					array_shift($atoms);
					array_shift($atoms);
				} else {
					JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_UNKNOWN_OPERATOR', $atoms[1], $rulepart), 'error');
					$atoms = array();
				}
			}
		}
	}

	function parseShippingTerm($expr) {
		/* In the advanced version, shipping cost can be given as a full mathematical expression */
		// If the shipping term starts with a double quote, it is a string, so don't turn it into lowercase.
		// All other expressions need to be turned into lowercase, because variable names are case-insensitive!
		if (substr($expr, 0, 1) === '"') {
			return $expr;
		} else {
			return strtolower($expr);
		}
	}
	
	function evaluateComparison ($terms, $vals) {
		while (count($terms)>2) {
			$res = false;
			switch ($terms[1]) {
				case '<':  $res = ($terms[0] < $terms[2]);  break;
				case '<=':
				case '=<': $res = ($terms[0] <= $terms[2]); break;
				case '==': $res = is_equal($terms[0], $terms[2]); break;
				case '!=':
				case '<>': $res = ($terms[0] != $terms[2]); break;
				case '>=':
				case '=>': $res = ($terms[0] >= $terms[2]); break;
				case '>':  $res = ($terms[0] >  $terms[2]);  break;
				case '~':
					$l=min(strlen($terms[0]), strlen($terms[2]));
					$res = (strncmp ($terms[0], $terms[2], $l) == 0);
					break;
				default:
					JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_UNKNOWN_OPERATOR', $terms[1], $this->rulestring), 'error');
					$res = false;
			}

			if ($res==false) return false;
			// Remove the first operand and the operator from the comparison:
			array_shift($terms);
			array_shift($terms);
		}
		if (count($terms)>1) {
			// We do not have the correct number of terms for chained comparisons, i.e. two terms leftover instead of one!
			JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_UNKNOWN_ERROR', $this->rulestring), 'error');
			return false;
		}
		// All conditions were fulfilled, so we can return true
		return true;
	}
	
	function evaluateListFunction ($function, $args) {
		# First make sure that all arguments are actually lists:
		$allarrays = True;
		foreach ($args as $a) {
			$allarrays = $allarrays && is_array($a);
		}
		if (!$allarrays) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_LISTFUNCTION_ARGS', $function, $this->rulestring), 'error');
			return false;
			
		}
		switch ($function) {
			case "length":		return count($args[0]); break;
			case "union": 
			case "join":		return call_user_func_array( "array_merge" , $args); break;
			case "complement":	return call_user_func_array( "array_diff" , $args); break;
			case "intersection":	return call_user_func_array( "array_intersect" , $args); break;
			case "issubset":	# Remove all of superset's elements to see if anything else is left: 
						return !array_diff($args[0], $args[1]); break;
			case "contains":	# Remove all of superset's elements to see if anything else is left: 
						# Notice the different argument order compared to issubset!
						return !array_diff($args[1], $args[0]); break;
			case "list_equal":	return array_unique($args[0])==array_unique($args[1]); break;
			default: 
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_LISTFUNCTION_UNKNOWN', $function, $this->rulestring), 'error');
				return false;
		}
	}
	
	function evaluateListContainmentFunction ($function, $args) {
		# First make sure that the first argument is a list:
		if (!is_array($args[0])) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_LISTFUNCTION_CONTAIN_ARGS', $function, $this->rulestring), 'error');
			return false;
		}
		// Extract the array from the args, the $args varialbe will now only contain the elements to be checked:
		$array = array_shift($args);
		switch ($function) {
			case "contains_any": 
					foreach ($args as $a) { 
						if (in_array($a, $array)) 
							return true; 
					}
					return false;
			
			case "contains_all":
					foreach ($args as $a) { 
						if (!in_array($a, $array)) 
							return false; 
					}
					return true;
			default: 
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_LISTFUNCTION_UNKNOWN', $function, $this->rulestring), 'error');
				return false;
		}
	}
	
	function evaluateFunction ($function, $args) {
		$func = strtolower($function);
		// Functions with no argument:
		if (count($args) == 0) {
			$dt = getdate();
			switch ($func) {
				case "second": return $dt['seconds']; break;
				case "minute": return $dt['minutes']; break;
				case "hour":   return $dt['hours']; break;
				case "day":    return $dt['mday']; break;
				case "weekday":return $dt['wday']; break;
				case "month":  return $dt['mon']; break;
				case "year":   return $dt['year']; break;
				case "yearday":return $dt['yday']; break;
			}
		}
		// Functions with exactly one argument:
		if (count($args) == 1) {
			switch ($func) {
				case "round": return round($args[0]); break;
				case "ceil":  return ceil ($args[0]); break;
				case "floor": return floor($args[0]); break;
				case "abs":   return abs($args[0]); break;
				case "not":   return !$args[0]; break;
				case "print_r": return print_r($args[0],1); break; 
			}
		}
		if (count($args) == 2) {
			switch ($func) {
				case "digit": return substr($args[0], $args[1]-1, 1); break;
				case "round": return round($args[0]/$args[1])*$args[1]; break;
				case "ceil":  return ceil($args[0]/$args[1])*$args[1]; break;
				case "floor": return floor($args[0]/$args[1])*$args[1]; break;
			}
		}
		if (count($args) == 3) {
			switch ($func) {
				case "substring": return substr($args[0], $args[1]-1, $args[2]); break;
			}
		}
		// Functions with variable number of args
		switch ($func) {
			case "max": 
					return max($args); break;
			case "min": 
					return min($args); break;
			case "list": 
			case "array": 
					return $args; break;
			// List functions:
		    case "length":
		    case "complement":
		    case "issubset":
		    case "contains":
		    case "union":
		    case "join":
		    case "intersection":
		    case "list_equal":
					return $this->evaluateListFunction ($func, $args); break;
			case "contains_any": 
			case "contains_all":
					return $this->evaluateListContainmentFunction($func, $args); break;
		}
		// No known function matches => print an error, return 0
		JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_UNKNOWN_FUNCTION', $function, $this->rulestring), 'error');
		return 0;
	}
	
	function evaluateTerm ($expr, $vals) {
		if (is_null($expr)) {
			return $expr;
		} elseif (is_numeric ($expr)) {
			return $expr;
		} elseif (is_string ($expr)) {
			// Explicit strings are delimited by '...' or "..."
			if (($expr[0]=='\'' || $expr[0]=='"') && ($expr[0]==substr($expr,-1)) ) {
				return substr($expr,1,-1);
			} elseif (array_key_exists(strtolower($expr), $vals)) {
				return $vals[strtolower($expr)];
			} else {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_UNKNOWN_VALUE', $expr, $this->rulestring), 'error');
				return null;
			}
		} elseif (is_array($expr)) {
			// Operator
			$op = array_shift($expr);
			$args = array();
			$evaluate = true;
			if ($op == "FUNCTION") {
				$evaluate = false;
			}
			foreach ($expr as $e) {
				$term = $evaluate ? ($this->evaluateTerm($e, $vals)) : $e;
				if ($op == 'COMPARISON') {
					// For comparisons, we only evaluate every other term (the operators are NOT evaluated!)
					$evaluate = !$evaluate;
				}
				if ($op == "FUNCTION") {
					$evaluate = true;
				}
				if (is_null($term)) return null;
				$args[] = $term;
			}
			$res = false;
			switch ($op) {
				// Logical operators:
				case 'OR':  foreach ($args as $a) { $res = ($res || $a); }; break;
				case '&&':
				case 'AND':  $res = true; foreach ($args as $a) { $res = ($res && $a); }; break;
				case 'IN': $res = in_array($args[0], $args[1]);  break;
				
				// Comparisons:
				case '<':
				case '<=':
				case '=<':
				case '==':
				case '!=':
				case '<>':
				case '>=':
				case '=>':
				case '>':
				case '~':
					$res = $this->evaluateComparison(array($args[0], $op, $args[1]), $vals); break;
				case 'COMPARISON':
					$res = $this->evaluateComparison($args, $vals); break;
				
				// Unary operators:
				case '.-': $res = -$args[0]; break;
				case '.+': $res = $args[0]; break;
				
				// Binary operators
				case "+":  $res = ($args[0] +  $args[1]); break;
				case "-":  $res = ($args[0] -  $args[1]); break;
				case "*":  $res = ($args[0] *  $args[1]); break;
				case "/":  $res = ($args[0] /  $args[1]); break;
				case "%":  $res = (fmod($args[0],  $args[1])); break;
				case "^":  $res = ($args[0] ^  $args[1]); break;
				
				// Functions:
				case "FUNCTION": $func = array_shift($args); $res = $this->evaluateFunction($func, $args); break;
				
				default:   $res = false;
			}
			
// 			JFactory::getApplication()->enqueueMessage("<pre>Result of ".print_r($expr,1)." is $res.</pre>", 'error');
			return $res;
		} else {
			// Neither string nor numeric, nor operator...
			JFactory::getApplication()->enqueueMessage(JText::sprintf('VMSHIPMENT_RULES_EVALUATE_UNKNOWN_VALUE', $expr, $this->rulestring), 'error');
			return null;
		}
	}

	function calculateShipping ($vals) {
		return $this->evaluateTerm($this->shipping, $vals);
	}

	function matches(&$vals) {
		// First, check the country, if any conditions are given:
		if (count ($this->countries) > 0 && !in_array ($vals['countryid'], $this->countries)) {
// 			vmdebug('Rule::matches: Country check failed: countryid='.print_r($vals['countryid'],1).', countries are: '.print_r($this->countries,1).'...');
			return False;
		}

		foreach ($this->conditions as $c) {
			// All conditions have to match!
			$ret = $this->evaluateTerm($c, $vals);

			if (is_null($ret) || (!$ret)) {
				return false;
			}
		}
		// All conditions match, so return true for rules; For definitions add the variable to the vals 
		if ($this->is_definition) {
			$vals[$this->name] = $this->evaluateTerm($this->shipping, $vals);
			// This rule does not specify shipping costs (just modify the cart values!), so return false
			return false;
		} else {
			return true;
		}
	}

	function getRuleName($vals) {
		// Replace all {variable} tags in the name by the variables from $vals
		$matches=array();
		$name=JText::_($this->name);
		preg_match_all('/{([A-Za-z0-9_]+)}/', $name, $matches);
		
		foreach ($matches[1] as $m) {
			$var=strtolower($m);
			if (isset($vals[$var])) {
				$name = str_replace("{".$m."}", strval($vals[$var]), $name);
			}
		}
		return $name;
	}
	
	function getShippingCosts($vals) {
		return $this->calculateShipping($vals);
	}
	
	function isNoShipping() {
		// NoShipping is set, so if the rule matches, this method should not offer any shipping at all
		return (is_string($this->shipping) && (strtolower($this->shipping)=="noshipping"));
	}
	function isDefinition() {
		return $this->is_definition;
	}

}

// No closing tag
