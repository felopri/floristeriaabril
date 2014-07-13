<?php

defined ('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of 'paypal ':
 *
 * @author Max Milbers
 * @author Valérie Isaksen
 * @version $Id: paypal.php 5177 2011-12-28 18:44:10Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */
if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

class plgVmPaymentPaypal extends vmPSPlugin {

	// instance of class
	public static $_this = FALSE;

	function __construct (& $subject, $config) {

		//if (self::$_this)
		//   return self::$_this;
		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id'; //virtuemart_paypal_id';
		$this->_tableId = 'id'; //'virtuemart_paypal_id';
		$varsToPush = array('paypal_merchant_email'  => array('', 'char'),
		                    'paypal_verified_only'   => array('', 'int'),
		                    'payment_currency'       => array('', 'int'),
		                    'email_currency'         => array('', 'int'),
		                    'log_ipn'                => array('', 'int'),
		                    'sandbox'                => array(0, 'int'),
		                    'sandbox_merchant_email' => array('', 'char'),
		                    'payment_logos'          => array('', 'char'),
		                    'debug'                  => array(0, 'int'),
		                    'status_pending'         => array('', 'char'),
		                    'status_success'         => array('', 'char'),
		                    'status_canceled'        => array('', 'char'),
		                    'countries'              => array('', 'char'),
		                    'min_amount'             => array('', 'int'),
		                    'max_amount'             => array('', 'int'),
		                    'secure_post'            => array('', 'int'),
		                    'ipn_test'               => array('', 'int'),
		                    'no_shipping'            => array('', 'int'),
		                    'address_override'       => array('', 'int'),
		                    'cost_per_transaction'   => array('', 'int'),
		                    'cost_percent_total'     => array('', 'int'),
		                    'tax_id'                 => array(0, 'int')
		);

		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);

		//self::$_this = $this;
	}

	/**
	 * @return string
	 */
	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL ('Payment Paypal Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields () {

		$SQLfields = array(
			'id'                                     => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'                    => 'int(1) UNSIGNED',
			'order_number'                           => 'char(64)',
			'virtuemart_paymentmethod_id'            => 'mediumint(1) UNSIGNED',
			'payment_name'                           => 'varchar(5000)',
			'payment_order_total'                    => 'decimal(15,5) NOT NULL',
			'payment_currency'                       => 'smallint(1)',
			'email_currency'                         => 'smallint(1)',
			'cost_per_transaction'                   => 'decimal(10,2)',
			'cost_percent_total'                     => 'decimal(10,2)',
			'tax_id'                                 => 'smallint(1)',
			'paypal_custom'                          => 'varchar(255)',
			'paypal_response_mc_gross'               => 'decimal(10,2)',
			'paypal_response_mc_currency'            => 'char(10)',
			'paypal_response_invoice'                => 'char(32)',
			'paypal_response_protection_eligibility' => 'char(128)',
			'paypal_response_payer_id'               => 'char(13)',
			'paypal_response_tax'                    => 'decimal(10,2)',
			'paypal_response_payment_date'           => 'char(28)',
			'paypal_response_payment_status'         => 'char(50)',
			'paypal_response_pending_reason'         => 'char(50)',
			'paypal_response_mc_fee'                 => 'decimal(10,2)',
			'paypal_response_payer_email'            => 'char(128)',
			'paypal_response_last_name'              => 'char(64)',
			'paypal_response_first_name'             => 'char(64)',
			'paypal_response_business'               => 'char(128)',
			'paypal_response_receiver_email'         => 'char(128)',
			'paypal_response_transaction_subject'    => 'char(128)',
			'paypal_response_residence_country'      => 'char(2)',
			'paypal_response_txn_id'                 => 'char(32)',
			'paypal_response_txn_type'               => 'char(32)', //The kind of transaction for which the IPN message was sent
			'paypal_response_parent_txn_id'          => 'char(32)',
			'paypal_response_case_creation_date'     => 'char(32)',
			'paypal_response_case_id'                => 'char(32)',
			'paypal_response_case_type'              => 'char(32)',
			'paypal_response_reason_code'            => 'char(32)',
			'paypalresponse_raw'                     => 'varchar(512)',
		);
		return $SQLfields;
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool|null
	 */
	function plgVmConfirmedOrder ($cart, $order) {

		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		$this->_debug = $method->debug;
		$this->logInfo ('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');

		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
		}

		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

		if (!class_exists ('TableVendors')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'table' . DS . 'vendors.php');
		}
		$vendorModel = VmModel::getModel ('Vendor');
		$vendorModel->setId (1);
		$vendor = $vendorModel->getVendor ();
		$vendorModel->addImages ($vendor, 1);
		$this->getPaymentCurrency ($method);
		$email_currency = $this->getEmailCurrency ($method);
		$currency_code_3 = shopFunctions::getCurrencyByID ($method->payment_currency, 'currency_code_3');

		$paymentCurrency = CurrencyDisplay::getInstance ($method->payment_currency);
		$totalInPaymentCurrency = round ($paymentCurrency->convertCurrencyTo ($method->payment_currency, $order['details']['BT']->order_total, FALSE), 2);
		$cd = CurrencyDisplay::getInstance ($cart->pricesCurrency);
		if ($totalInPaymentCurrency <= 0) {
			vmInfo (JText::_ ('VMPAYMENT_PAYPAL_PAYMENT_AMOUNT_INCORRECT'));
			return FALSE;
		}
		$merchant_email = $this->_getMerchantEmail ($method);
		if (empty($merchant_email)) {
			vmInfo (JText::_ ('VMPAYMENT_PAYPAL_MERCHANT_EMAIL_NOT_SET'));
			return FALSE;
		}
		$quantity = 0;
		foreach ($cart->products as $key => $product) {
			$quantity = $quantity + $product->quantity;
		}
		$post_variables = Array(
			'cmd'              => '_ext-enter',
			'redirect_cmd'     => '_xclick',
			'upload'           => '1', //Indicates the use of third-party shopping cart
			'business'         => $merchant_email, //Email address or account ID of the payment recipient (i.e., the merchant).
			'receiver_email'   => $merchant_email, //Primary email address of the payment recipient (i.e., the merchant
			'order_number'     => $order['details']['BT']->order_number,
			"invoice"          => $order['details']['BT']->order_number,
			'custom'           => $return_context,
			'item_name'        => JText::_ ('VMPAYMENT_PAYPAL_ORDER_NUMBER') . ': ' . $order['details']['BT']->order_number,
			//'quantity'          => $quantity,
			"amount"           => $totalInPaymentCurrency,
			"currency_code"    => $currency_code_3,
			/*
					 * 1 – L'adresse spécifiée dans les variables pré-remplies remplace l'adresse de livraison enregistrée auprès de PayPal.
					 * Le payeur voit l'adresse qui est transmise mais ne peut pas la modifier.
					 * Aucune adresse n'est affichée si l'adresse n'est pas valable
					 * (par exemple si des champs requis, tel que le pays, sont manquants) ou pas incluse.
					 * Valeurs autorisées : 0, 1. Valeur par défaut : 0
					 */
			"address_override" => isset($method->address_override) ? $method->address_override : 0, // 0 ??   Paypal does not allow your country of residence to ship to the country you wish to
			"first_name"       => $address->first_name,
			"last_name"        => $address->last_name,
			"address1"         => $address->address_1,
			"address2"         => isset($address->address_2) ? $address->address_2 : '',
			"zip"              => $address->zip,
			"city"             => $address->city,
			"state"            => isset($address->virtuemart_state_id) ? ShopFunctions::getStateByID ($address->virtuemart_state_id) : '',
			"country"          => ShopFunctions::getCountryByID ($address->virtuemart_country_id, 'country_2_code'),
			"email"            => $order['details']['BT']->email,
			"night_phone_b"    => $address->phone_1,
			"return"           => JROUTE::_ (JURI::root () . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . JRequest::getInt ('Itemid')),
			// Keep this line, needed when testing
			//"return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
			"notify_url"       => JROUTE::_ (JURI::root () . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
			"cancel_return"    => JROUTE::_ (JURI::root () . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . JRequest::getInt ('Itemid')),
			//"undefined_quantity" => "0",
			"ipn_test"         => $method->debug,
			"rm"               => '2', // the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
			//"pal" => "NRUBJXESJTY24",
			"image_url"        => JURI::root () . $vendor->images[0]->file_url,
			"no_shipping"      => isset($method->no_shipping) ? $method->no_shipping : 0,
			"no_note"          => "1");

		/*
					  $i = 1;
					  foreach ($cart->products as $key => $product) {
					  $post_variables["item_name_" . $i] = substr(strip_tags($product->product_name), 0, 127);
					  $post_variables["item_number_" . $i] = $i;
					  $post_variables["amount_" . $i] = $cart->pricesUnformatted[$key]['salesPrice'];
					  $post_variables["quantity_" . $i] = $product->quantity;
					  $i++;
					  }
					  if ($cart->pricesUnformatted ['shipmentValue']) {
					  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_SHIPMENT_PRICE');
					  $post_variables["item_number_" . $i] = $i;
					  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['shipmentValue'];
					  $post_variables["quantity_" . $i] = 1;
					  $i++;
					  }
					  if ($cart->pricesUnformatted ['paymentValue']) {
					  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_PAYMENT_PRICE');
					  $post_variables["item_number_" . $i] = $i;
					  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['paymentValue'];
					  $post_variables["quantity_" . $i] = 1;
					  $i++;
					  }
					  if (!empty($order->cart->coupon)) {
					  $post_variables["discount_amount_cart"] = $cart->pricesUnformatted['discountAmount'];
					  }
					 */

		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName ($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['paypal_custom'] = $return_context;
		$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $method->cost_percent_total;
		$dbValues['payment_currency'] = $method->payment_currency;
		$dbValues['email_currency'] = $email_currency;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency;
		$dbValues['tax_id'] = $method->tax_id;
		$this->storePSPluginInternalData ($dbValues);

		$url = $this->_getPaypalUrlHttps ($method);

		// add spin image
		$html = '<html><head><title>Redirection</title></head><body><div style="margin: auto; text-align: center;">';
		$html .= '<form action="' . "https://" . $url . '" method="post" name="vm_paypal_form" >';
		$html .= '<input type="submit"  value="' . JText::_ ('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE') . '" />';
		foreach ($post_variables as $name => $value) {
			$html .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars ($value) . '" />';
		}
		$html .= '</form></div>';
		$html .= ' <script type="text/javascript">';
		$html .= ' document.vm_paypal_form.submit();';
		$html .= ' </script></body></html>';

		// 	2 = don't delete the cart, don't send email and don't redirect
		$cart->_confirmDone = FALSE;
		$cart->_dataValidated = FALSE;
		$cart->setCartIntoSession ();
		JRequest::setVar ('html', $html);

		/*

			  $qstring = '?';
			  foreach ($post_variables AS $k => $v) {
			  $qstring .= ( empty($qstring) ? '' : '&')
			  . urlencode($k) . '=' . urlencode($v);
			  }
			  // we can display the logo, or do the redirect
			  $mainframe = JFactory::getApplication();
			  $mainframe->redirect("https://" . $url . $qstring);


			  return false; // don't delete the cart, don't send email
			 */
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		$this->getPaymentCurrency ($method);
		$paymentCurrencyId = $method->payment_currency;
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetEmailCurrency ($virtuemart_paymentmethod_id, $virtuemart_order_id, &$emailCurrencyId) {

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		if (!($payments = $this->_getPaypalInternalData ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		if (empty($payments[0]->email_currency)) {
			$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
			$db = JFactory::getDBO ();
			$q = 'SELECT   `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
			$db->setQuery ($q);
			$emailCurrencyId = $db->loadResult ();
		} else {
			$emailCurrencyId = $payments[0]->email_currency;
		}

	}

	/**
	 * @param $html
	 * @return bool|null|string
	 */
	function plgVmOnPaymentResponseReceived (&$html) {

		if (!class_exists ('VirtueMartCart')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists ('shopFunctionsF')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		//vmdebug('PAYPAL plgVmOnPaymentResponseReceived', $paypal_data);
		// the payment itself should send the parameter needed.
		$virtuemart_paymentmethod_id = JRequest::getInt ('pm', 0);
		$order_number = JRequest::getString ('on', 0);
		$vendorId = 0;
		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}
		if (!($paymentTable = $this->getDataByOrderId ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$payment_name = $this->renderPluginName ($method);
		$html = $this->_getPaymentResponseHtml ($paymentTable, $payment_name);

		//We delete the old stuff
		// get the correct cart / session
		$cart = VirtueMartCart::getCart ();
		$cart->emptyCart ();
		return TRUE;
	}

	/**
	 * @return bool|null
	 */
	function plgVmOnUserPaymentCancel () {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		$order_number = JRequest::getString ('on', '');
		$virtuemart_paymentmethod_id = JRequest::getInt ('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId ($virtuemart_paymentmethod_id)) {
			return NULL;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}
		if (!($paymentTable = $this->getDataByOrderId ($virtuemart_order_id))) {
			return NULL;
		}

		VmInfo (Jtext::_ ('VMPAYMENT_PAYPAL_PAYMENT_CANCELLED'));
		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		if (strcmp ($paymentTable->paypal_custom, $return_context) === 0) {
			$this->handlePaymentUserCancel ($virtuemart_order_id);
		}
		return TRUE;
	}

	/*
		 *   plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
		 * Return:
		 * Parameters:
		 *  None
		 *  @author Valerie Isaksen
		 */

	/**
	 * @return bool|null
	 */
	function plgVmOnPaymentNotification () {

		//$this->_debug = true;
		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}
		$paypal_data = JRequest::get ('post');
		if (!isset($paypal_data['invoice'])) {
			return FALSE;
		}

		$order_number = $paypal_data['invoice'];
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($paypal_data['invoice']))) {
			return FALSE;
		}

		$vendorId = 0;
		if (!($payments = $this->getDatasByOrderId ($virtuemart_order_id))) {
			return FALSE;
		}

		$method = $this->getVmPluginMethod ($payments[0]->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		$this->_debug = $method->debug;

		$this->logInfo ('paypal_data ' . implode ('   ', $paypal_data), 'message');
		// _processIPN checks that  $res== "VERIFIED"
		if (!$this->_processIPN ($paypal_data, $method)) {
			$this->logInfo ('paypal_data _processIPN FALSE', 'message');
			return FALSE;
		}

		//$this->_storePaypalInternalData ($method, $paypal_data, $virtuemart_order_id, $payment->virtuemart_paymentmethod_id);
		$modelOrder = VmModel::getModel ('orders');
		$order = array();

		/*
		 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
		 * The status of the payment:
		 * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
		 * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
		 * Created: A German ELV payment is made using Express Checkout.
		 * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
		 * Expired: This authorization has expired and cannot be captured.
		 * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
		 * Pending: The payment is pending. See pending_reason for more information.
		 * Refunded: You refunded the payment.
		 * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
		 * Processed: A payment has been accepted.
		 * Voided: This authorization has been voided.
		 *
		 */

		$lang = JFactory::getLanguage ();
		$order['customer_notified'] = 1;

		// 1. check the payment_status is Completed
		if (strcmp ($paypal_data['payment_status'], 'Completed') == 0) {
			// 2. check that txn_id has not been previously processed
			if ($this->_check_txn_id_already_processed ($payments, $paypal_data['txn_id'], $method)) {
				return FALSE;
			}
			// 3. check email and amount currency is correct
			if (!$this->_check_email_amount_currency ($payments, $this->_getMerchantEmail ($method), $paypal_data)) {
				return FALSE;
			}
			// now we can process the payment
			$order['order_status'] = $method->status_success;
			$order['comments'] = JText::sprintf ('VMPAYMENT_PAYPAL_PAYMENT_STATUS_CONFIRMED', $order_number);
		} elseif (strcmp ($paypal_data['payment_status'], 'Pending') == 0) {
			$key = 'VMPAYMENT_PAYPAL_PENDING_REASON_FE_' . strtoupper ($paypal_data['pending_reason']);
			if (!$lang->hasKey ($key)) {
				$key = 'VMPAYMENT_PAYPAL_PENDING_REASON_FE_DEFAULT';
			}
			$order['comments'] = JText::sprintf ('VMPAYMENT_PAYPAL_PAYMENT_STATUS_PENDING', $order_number) . JText::_ ($key);
			$order['order_status'] = $method->status_pending;
		} elseif (isset ($paypal_data['payment_status'])) {
			$order['order_status'] = $method->status_canceled;
		} else {
			/*
			* a notification was received that concerns one of the payment (since $paypal_data['invoice'] is found in our table),
			* but the IPN notification has no $paypal_data['payment_status']
			* We just log the info in the order, and do not change the status, do not notify the customer
			*/
			$order['comments'] = JText::_ ('VMPAYMENT_PAYPAL_IPN_NOTIFICATION_RECEIVED');
			$order['customer_notified'] = 0;
		}
		$this->_storePaypalInternalData ($method, $paypal_data, $virtuemart_order_id, $payments[0]->virtuemart_paymentmethod_id);
		$this->logInfo ('plgVmOnPaymentNotification return new_status:' . $order['order_status'], 'message');

		$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
		//// remove vmcart
		if (isset($paypal_data['custom'])) {
			$this->emptyCart ($paypal_data['custom'], $order_number);
		}
		//die();
	}

	function logIpn () {

		$file = JPATH_ROOT . "/logs/paypal-ipn.log";
		$date = JFactory::getDate ();

		$fp = fopen ($file, 'a');
		fwrite ($fp, "\n\n" . $date->toFormat ('%Y-%m-%d %H:%M:%S'));
		fwrite ($fp, "\n" . var_export ($_POST, TRUE));
		fclose ($fp);
	}

	/**
	 * @param $method
	 * @param $paypal_data
	 * @param $virtuemart_order_id
	 */
	function _storePaypalInternalData ($method, $paypal_data, $virtuemart_order_id, $virtuemart_paymentmethod_id) {

		// get all know columns of the table
		$db = JFactory::getDBO ();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery ($query);
		$columns = $db->loadResultArray (0);
		$post_msg = '';
		foreach ($paypal_data as $key => $value) {
			$post_msg .= $key . "=" . $value . "<br />";
			$table_key = 'paypal_response_' . $key;
			if (in_array ($table_key, $columns)) {
				$response_fields[$table_key] = $value;
			}
		}

		//$response_fields[$this->_tablepkey] = $this->_getTablepkeyValue($virtuemart_order_id);
		$response_fields['payment_name'] = $this->renderPluginName ($method);
		$response_fields['paypalresponse_raw'] = $post_msg;
		$response_fields['order_number'] = $paypal_data['invoice'];
		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		$response_fields['virtuemart_paymentmethod_id'] = $virtuemart_paymentmethod_id;
		$response_fields['paypal_custom'] = $paypal_data['custom'];

		//$preload=true   preload the data here too preserve not updated data
		$this->storePSPluginInternalData ($response_fields);
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId ($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($payments = $this->_getPaypalInternalData ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist" width="50%">' . "\n";
		$html .= $this->getHtmlHeaderBE ();
		$code = "paypal_response_";
		$first = TRUE;
		foreach ($payments as $payment) {
			$html .= '<tr class="row1"><td>' . JText::_ ('VMPAYMENT_PAYPAL_DATE') . '</td><td align="left">' . $payment->created_on . '</td></tr>';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->getHtmlRowBE ('PAYPAL_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if ($payment->payment_order_total and  $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE ('PAYPAL_PAYMENT_ORDER_TOTAL', $payment->payment_order_total . " " . shopFunctions::getCurrencyByID ($payment->payment_currency, 'currency_code_3'));
				}
				if ($payment->email_currency and  $payment->email_currency != 0) {
					$html .= $this->getHtmlRowBE ('PAYPAL_PAYMENT_EMAIL_CURRENCY', shopFunctions::getCurrencyByID ($payment->email_currency, 'currency_code_3'));
				}
				$first = FALSE;
			}
			foreach ($payment as $key => $value) {
				// only displays if there is a value or the value is different from 0.00 and the value
				if ($value) {
					if (substr ($key, 0, strlen ($code)) == $code) {
						$html .= $this->getHtmlRowBE ($key, $value);
					}
				}
			}

		}
		$html .= '</table>' . "\n";
		return $html;
	}

	/**
	 * @param        $virtuemart_order_id
	 * @param string $order_number
	 * @return mixed|string
	 */
	function _getPaypalInternalData ($virtuemart_order_id, $order_number = '') {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery ($q);
		if (!($payments = $db->loadObjectList ())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $payments;
	}

	/**
	 * Get ipn data, send verification to PayPal, run corresponding handler
	 *
	 * @param array $data
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	private function _processIPN ($paypal_data, $method) {

		// check that the remote IP is from Paypal.
		if (!$this->checkPaypalIps ($paypal_data['ipn_test'], $paypal_data['invoice'], $method)) {
			$this->logInfo ('_processIPN checkPaypalIps FALSE', 'message');
			return FALSE;
		}
		// Paypal wants to open the socket in SSL
		$port = 443;
		$protocol = 'ssl://';
		$paypal_url = $this->_getPaypalURL ($method);
		/*
		 * Before we can trust the contents of the message, we must first verify that the message came from PayPal.
		 * To verify the message, we must send back the contents in the exact order they
		*  were received and precede it with the command _notify-validate,
		 */
		$post_msg = 'cmd=_notify-validate';
		foreach ($paypal_data as $key => $value) {
			if ($key != 'view' && $key != 'layout') {
				$value = urlencode ($value);
				$post_msg .= "&$key=$value";
			}
		}
/*
				$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= "Content-Length: " . strlen ($post_msg) . "\r\n\r\n";
*/
		 $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "User-Agent: PHP/" . phpversion () . "\r\n";
		$header .= "Referer: " . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . @$_SERVER['QUERY_STRING'] . "\r\n";
		$header .= "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\r\n";
		$header .= "Host: "  . $this->_getPaypalUrl ($method) . ":" . $port . "\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen ($post_msg) . "\r\n";
		$header .= "Accept: */*\r\n\r\n";

		$fps = fsockopen ($protocol . $paypal_url, $port, $errno, $errstr, 30);

		$valid_ipn = false;
		if (!$fps) {
			$this->sendEmailToVendorAndAdmins ("error with paypal", JText::sprintf ('VMPAYMENT_PAYPAL_ERROR_POSTING_IPN', $errstr, $errno));
			$this->logInfo ('_processIPN fsockopen FALSE', 'message');
		} else {
			fputs ($fps, $header . $post_msg);
			$this->logInfo ('_processIPN Fputs: ' . $header . $post_msg, 'message');
			$res = '';
			while (!feof ($fps)) {
				$res .= fgets ($fps, 1024);
			}
			fclose ($fps);

			$this->logInfo ('_processIPN FROM IPN VALIDATION:' . $res, 'message');
			// Inspect IPN validation result and act accordingly
			$valid_ipn = strstr ($res, "VERIFIED");
			if (!$valid_ipn) {
				if (strstr ($res, "INVALID")) {
					$emailBody = "Hello,\n\nerror with paypal IPN NOTIFICATION" . " " . $res . "\n";
					// If 'INVALID', send an email. TODO: Log for manual investigation.
					foreach ($paypal_data as $key => $value) {
						$emailBody .= $key . " = " . $value . "\n";
					}
					$this->sendEmailToVendorAndAdmins (JText::_ ('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . " " . $res, $emailBody);
					$this->logInfo ('_processIPN INVALID', 'message');
				} else {
					$emailBody = "Hello,
                An error occured while processing a paypal transaction.";
					$this->sendEmailToVendorAndAdmins (JText::_ ('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . " " . $res, $emailBody);
					$this->logInfo ('_processIPN NO ANSWER FROM PAYPAL', 'message');
				}
			}
		}

		$this->logInfo ('_processIPN valid_ipn:' . $valid_ipn, 'message');
		return $valid_ipn;
	}

	function _check_txn_id_already_processed ($payments, $txn_id, $method) {

		$virtuemart_order_id = $payments[0]->virtuemart_order_id;
		$orderModel = VmModel::getModel ('orders');
		$order = $orderModel->getOrder ($virtuemart_order_id);

		if ($order['details']['BT']->order_status == $method->status_success) {
			foreach ($payments as $payment) {
				if ($payment->paypal_response_txn_id == $txn_id) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	function _check_email_amount_currency ($payments, $email, $paypal_data) {

		/*
		 * TODO Not checking yet because config do not have primary email address
		* Primary email address of the payment recipient (that is, the merchant).
		* If the payment is sent to a non-primary email address on your PayPal account,
		* the receiver_email is still your primary email.
		*/
		/*
		if ($payments[0]->payment_order_total==$email) {
			return true;
		}
		*/
		$currency_code_3 = shopFunctions::getCurrencyByID ($payments[0]->payment_currency, 'currency_code_3');
		if (($payments[0]->payment_order_total == $paypal_data['mc_gross']) and ($currency_code_3 == $paypal_data['mc_currency'])) {
			return TRUE;
		}

		$mailsubject = "PayPal Transaction";
		$mailbody = "Hello,
		An IPN notification was received with an invalid amount or currency
		----------------------------------
		IPN Notification content:
		";
		foreach ($paypal_data as $key => $value) {
			$mailbody .= $key . " = " . $value . "\n\n";
		}
		$this->sendEmailToVendorAndAdmins ($mailsubject, $mailbody);

		return FALSE;
	}

	/**
	 * @param $method
	 * @return mixed
	 */
	function _getMerchantEmail ($method) {

		return $method->sandbox ? $method->sandbox_merchant_email : $method->paypal_merchant_email;
	}

	/**
	 * @param $method
	 * @return string
	 */
	function _getPaypalUrl ($method) {

		$url = $method->sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		return $url;
	}

	/**
	 * @param $method
	 * @return string
	 */
	function _getPaypalUrlHttps ($method) {

		$url = $this->_getPaypalUrl ($method);
		$url = $url . '/cgi-bin/webscr';

		return $url;
	}

	/*
		 * CheckPaypalIPs
		 * Cannot be checked with Sandbox
		 * From VM1.1
		 */

	/**
	 * @param $test_ipn
	 * @return mixed
	 */
	function checkPaypalIps ($test_ipn, $order_number, $method) {

		// Get the list of IP addresses for www.paypal.com and notify.paypal.com
		if ($method->sandbox) {
			$paypal_iplist = gethostbynamel ('ipn.sandbox.paypal.com');
			$paypal_iplist = (array)$paypal_iplist;
		} else {
			$paypal_iplist1 = gethostbynamel ('www.paypal.com');
			$paypal_iplist2 = gethostbynamel ('notify.paypal.com');
			$paypal_iplist = array_merge ($paypal_iplist1, $paypal_iplist2);
		}
		$this->logInfo ('checkPaypalIps: ' . implode (",", $paypal_iplist) . " server is:" . $_SERVER['REMOTE_ADDR'], 'message');
		$hostname = $this->_getPaypalUrl ($method);
		//  test if the remote IP connected here is a valid IP address
		if (!in_array ($_SERVER['REMOTE_ADDR'], $paypal_iplist)) {
			$mail_subject = "PayPal IPN Transaction on your site: Possible fraud";
			$mail_body = "Error code 506. Possible fraud. Error with REMOTE IP ADDRESS = " . $_SERVER['REMOTE_ADDR'] . ".
                        The remote address of the script posting to this notify script does not match a valid PayPal ip address\n
            These are the valid IP Addresses: " . implode (",", $paypal_iplist) .
				"The Order ID received was: " . $order_number;
			$this->sendEmailToVendorAndAdmins ($mail_subject, $mail_body);
			return FALSE;
		}
		/*
				if (!($method->sandbox && $test_ipn == 1)) {
					$res = "FAILED";
					$mailsubject = "PayPal Sandbox Transaction";
					$mailbody = "Hello,
				A fatal error occurred while processing a paypal transaction.
				----------------------------------
				Hostname: $hostname
				URI:" . $_SERVER["REMOTE_ADDR"] .
						" A Paypal transaction was made using the sandbox without your site in Paypal-Debug-Mode";
					//vmMail($mosConfig_mailfrom, $mosConfig_fromname, $debug_email_address, $mailsubject, $mailbody );
					$this->sendEmailToVendorAndAdmins ($mailsubject, $mailbody);
					return FALSE;
				}
		*/
		$this->logInfo ('checkPaypalIps:  OK', 'message');

		return TRUE;
	}

	/**
	 * @param $paypalTable
	 * @param $payment_name
	 * @return string
	 */
	function _getPaymentResponseHtml ($paypalTable, $payment_name) {

		$html = '<table>' . "\n";
		$html .= $this->getHtmlRow ('PAYPAL_PAYMENT_NAME', $payment_name);
		if (!empty($paypalTable)) {
			$html .= $this->getHtmlRow ('PAYPAL_ORDER_NUMBER', $paypalTable->order_number);
			//$html .= $this->getHtmlRow('PAYPAL_AMOUNT', $paypalTable->payment_order_total. " " . $paypalTable->payment_currency);
		}
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

		if (preg_match ('/%$/', $method->cost_percent_total)) {
			$cost_percent_total = substr ($method->cost_percent_total, 0, -1);
		} else {
			$cost_percent_total = $method->cost_percent_total;
		}
		return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
	}

	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 *
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {

		$this->convert ($method);

		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $cart_prices['salesPrice'];
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));

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
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		if (in_array ($address['virtuemart_country_id'], $countries) || count ($countries) == 0) {
			if ($amount_cond) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param $method
	 */
	function convert ($method) {

		$method->min_amount = (float)$method->min_amount;
		$method->max_amount = (float)$method->max_amount;
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 */

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {

		return $this->onStoreInstallPluginTable ($jplugin_id);
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Max Milbers
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart, &$msg) {

		return $this->OnSelectCheck ($cart);
	}

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on succes, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE ($cart, $selected, $htmlIn);
	}

	/*
		 * plgVmonSelectedCalculatePricePayment
		 * Calculate the price (value, tax_id) of the selected method
		 * It is called by the calculator
		 * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
		 * @author Valerie Isaksen
		 * @cart: VirtueMartCart the current cart
		 * @cart_prices: array the new cart prices
		 * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
		 *
		 *
		 */

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmonSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $paymentCounter);
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		$this->onShowOrderFE ($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

	/**
	 * This event is fired during the checkout process. It can be used to validate the
	 * method data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	 * @author Max Milbers

	public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
	return null;
	}
	 */

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrintPayment ($order_number, $method_id) {

		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	/**
	 * Save updated order data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 * @author Oscar van Eijk

	public function plgVmOnUpdateOrderPayment(  $_formData) {
	return null;
	}
	 */
	/**
	 * Save updated orderline data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 * @author Oscar van Eijk

	public function plgVmOnUpdateOrderLine(  $_formData) {
	return null;
	}
	 */
	/**
	 * plgVmOnEditOrderLineBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk

	public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
	return null;
	}
	 */

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk

	public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
	return null;
	}
	 */
	function plgVmDeclarePluginParamsPayment ($name, $id, &$data) {

		return $this->declarePluginParams ('payment', $name, $id, $data);
	}

	/**
	 * @param $name
	 * @param $id
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {

		return $this->setOnTablePluginParams ($name, $id, $table);
	}

}

// No closing tag
