<?php
/**
 *
 * Handle the orders view
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 6383 2012-08-27 16:53:06Z alatak $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');


/**
 * Handle the orders view
 */
class VirtuemartViewOrders extends VmView {

	public function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$task = JRequest::getWord('task', 'list');

		$layoutName = JRequest::getWord('layout', 'list');

		$this->setLayout($layoutName);

		$_currentUser = JFactory::getUser();
		$document = JFactory::getDocument();

		if(!empty($tpl)){
			$format = $tpl;
		} else {
			$format = JRequest::getWord('format', 'html');
		}
		$this->assignRef('format', $format);

		if($format=='pdf'){
			$document->setTitle( JText::_('COM_VIRTUEMART_INVOICE') );

			//PDF needs more RAM than usual
			VmConfig::ensureMemoryLimit(96);

		} else {
		    if ($layoutName == 'details') {
			$document->setTitle( JText::_('COM_VIRTUEMART_ACC_ORDER_INFO') );
			$pathway->additem(JText::_('COM_VIRTUEMART_ACC_ORDER_INFO'));
		    } else {
			$document->setTitle( JText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE') );
			$pathway->additem(JText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE'));
		    }
		}

		$orderModel = VmModel::getModel('orders');

		if ($layoutName == 'details') {
			$order_list_link = FALSE;

			$order_list_link = JRoute::_('index.php?option=com_virtuemart&view=orders&layout=list', FALSE);

			$this->assignRef('order_list_link', $order_list_link);

			$orderDetails = $orderModel ->getMyOrderDetails();

			if(!$orderDetails or empty($orderDetails['details'])){
				echo JText::_('COM_VIRTUEMART_ORDER_NOTFOUND');
				return;
			}

			$userFieldsModel = VmModel::getModel('userfields');
			$_userFields = $userFieldsModel->getUserFields(
				 'account'
			, array('captcha' => true, 'delimiters' => true) // Ignore these types
			, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
			);
			$orderbt = $orderDetails['details']['BT'];
			$orderst = (array_key_exists('ST', $orderDetails['details'])) ? $orderDetails['details']['ST'] : $orderbt;
			$userfields = $userFieldsModel->getUserFieldsFilled(
			$_userFields
			,$orderbt
			);
			$_userFields = $userFieldsModel->getUserFields(
				 'shipment'
			, array() // Default switches
			, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
			);

			$shipmentfields = $userFieldsModel->getUserFieldsFilled(
			$_userFields
			,$orderst
			);

			$shipment_name='';
			if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmshipment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEShipment',array(  $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_shipmentmethod_id, &$shipment_name));

			$payment_name='';
			if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEPayment',array( $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_paymentmethod_id,  &$payment_name));

			if($format=='pdf'){
				$invoiceNumberDate = array();
				$return = $orderModel->createInvoiceNumber($orderDetails['details']['BT'], $invoiceNumberDate );
				if(empty($invoiceNumberDate)){
					$invoiceNumberDate[0] = 'no invoice number accessible';
					$invoiceNumberDate[1] = 'no invoice date accessible';
				}
				$this->assignRef('invoiceNumber', $invoiceNumberDate[0]);
				$this->assignRef('invoiceDate', $invoiceNumberDate[1]);
			}

			$this->assignRef('userfields', $userfields);
			$this->assignRef('shipmentfields', $shipmentfields);
			$this->assignRef('shipment_name', $shipment_name);
			$this->assignRef('payment_name', $payment_name);
			$this->assignRef('orderdetails', $orderDetails);

			if($_currentUser->guest){
				$details_url = juri::root().'index.php?option=com_virtuemart&view=orders&layout=details&tmpl=component&order_pass=' . JRequest::getString('order_pass',false) .'&order_number='.JRequest::getString('order_number',false);
			} else {
				$details_url = juri::root().'index.php?option=com_virtuemart&view=orders&layout=details&tmpl=component&virtuemart_order_id=' . $this->orderdetails['details']['BT']->virtuemart_order_id;
			}
			$this->assignRef('details_url', $details_url);

			$tmpl = JRequest::getWord('tmpl');
			$print = false;
			if($tmpl){
				$print = true;
			}
			$this->prepareVendor();
			$this->assignRef('print', $print);

			$vendorId = 1;
			$emailCurrencyId = $orderDetails['details']['BT']->user_currency_id;
			$exchangeRate = FALSE;
			if (!class_exists ('vmPSPlugin')) {
				require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			}
			JPluginHelper::importPlugin ('vmpayment');
			$dispatcher = JDispatcher::getInstance ();
			$dispatcher->trigger ('plgVmgetEmailCurrency', array($orderDetails['details']['BT']->virtuemart_paymentmethod_id, $orderDetails['details']['BT']->virtuemart_order_id, &$emailCurrencyId));
			if (!class_exists ('CurrencyDisplay')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
			}
			$currency = CurrencyDisplay::getInstance ($emailCurrencyId, $vendorId);
			if ($emailCurrencyId) {
				vmdebug ('exchangerate', $orderDetails['details']['BT']->user_currency_rate);
				$currency->exchangeRateShopper = $orderDetails['details']['BT']->user_currency_rate;
			}
			$this->assignRef ('currency', $currency);
			// Implement the Joomla panels. If we need a ShipTo tab, make it the active one.
			// In tmpl/edit.php, this is the 4th tab (0-based, so set to 3 above)
			// jimport('joomla.html.pane');
			// $pane = JPane::getInstance((__VM_ORDER_USE_SLIDERS?'Sliders':'Tabs'));
			// $this->assignRef('pane', $pane);
		} else { // 'list' -. default
			$useSSL = VmConfig::get('useSSL',0);
			$useXHTML = true;
			$this->assignRef('useSSL', $useSSL);
			$this->assignRef('useXHTML', $useXHTML);
			if ($_currentUser->get('id') == 0) {
				// getOrdersList() returns all orders when no userID is set (admin function),
				// so explicetly define an empty array when not logged in.
				$orderList = array();
			} else {
				$orderList = $orderModel->getOrdersList($_currentUser->get('id'), TRUE);
				foreach ($orderList as $order) {
					$vendorId = 1;
					$emailCurrencyId = $order->user_currency_id;
					$exchangeRate = FALSE;
					if (!class_exists ('vmPSPlugin')) {
						require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			}
					JPluginHelper::importPlugin ('vmpayment');
					$dispatcher = JDispatcher::getInstance ();
					$dispatcher->trigger ('plgVmgetEmailCurrency', array($order->virtuemart_paymentmethod_id, $order->virtuemart_order_id, &$emailCurrencyId));
					if (!class_exists ('CurrencyDisplay')) {
						require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
					}
					$currency = CurrencyDisplay::getInstance ($emailCurrencyId, $vendorId);
					$this->assignRef ('currency', $currency);
					if ($emailCurrencyId) {
						vmdebug ('exchangerate', $order->user_currency_rate);
						$currency->exchangeRateShopper = $order->user_currency_rate;
					}
					$order->currency = $currency;
				}
			}
			$this->assignRef('orderlist', $orderList);
		}
/*
		if (!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');

		$currency = CurrencyDisplay::getInstance();
		$this->assignRef('currency', $currency);
*/
		$orderStatusModel = VmModel::getModel('orderstatus');

		$_orderstatuses = $orderStatusModel->getOrderStatusList();
		$orderstatuses = array();
		foreach ($_orderstatuses as $_ordstat) {
			$orderstatuses[$_ordstat->order_status_code] = JText::_($_ordstat->order_status_name);
		}


		$this->assignRef('orderstatuses', $orderstatuses);

		if(!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

		$document = JFactory::getDocument();
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');

		// this is no setting in BE to change the layout !
		//shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		parent::display($tpl);
	}


	// add vendor for cart
	function prepareVendor(){

		$vendorModel = VmModel::getModel('vendor');
		$vendor = & $vendorModel->getVendor();
		$this->assignRef('vendor', $vendor);
		$vendorModel->addImages($this->vendor,1);

	}



}
