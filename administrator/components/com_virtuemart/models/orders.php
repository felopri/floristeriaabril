<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author Patrick Kohl
 * @author Valerie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for VirtueMart Orders
 * WHY $this->db is never used in the model ?
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelOrders extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('orders');
		$this->addvalidOrderingFieldName(array('order_name','order_email','payment_method','virtuemart_order_id' ) );

	}

	/**
	 * This function gets the orderId, for anonymous users
	 * @author Max Milbers
	 */
	public function getOrderIdByOrderPass($orderNumber,$orderPass){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_pass`="'.$db->getEscaped($orderPass).'" AND `order_number`="'.$db->getEscaped($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();

// 		vmdebug('getOrderIdByOrderPass '.$orderId);
		return $orderId;

	}
	/**
	 * This function gets the orderId, for payment response
	 * author Valerie Isaksen
	 */
	public static function getOrderIdByOrderNumber($orderNumber){

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `order_number`="'.$db->getEscaped($orderNumber).'"';
		$db->setQuery($q);
		$orderId = $db->loadResult();
		return $orderId;

	}
	/**
	 * This function seems completly broken, JRequests are not allowed in the model, sql not escaped
	 * This function gets the secured order Number, to send with paiement
	 *
	 */
	public function getOrderNumber($virtuemart_order_id){

		$db = JFactory::getDBO();
		$q = 'SELECT `order_number` FROM `#__virtuemart_orders` WHERE virtuemart_order_id="'.(int)$virtuemart_order_id.'"  ';
		$db->setQuery($q);
		$OrderNumber = $db->loadResult();
		return $OrderNumber;

	}

	/**
	 * Was also broken, actually used?
	 *
	 * get next/previous order id
	 *
	 */

	public function getOrderId($order_id, $direction ='DESC') {

		if ($direction == 'ASC') {
			$arrow ='>';
		} else {
			$arrow ='<';
		}

		$db = JFactory::getDBO();
		$q = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders` WHERE `virtuemart_order_id`'.$arrow.(int)$order_id;
		$q.= ' ORDER BY `virtuemart_order_id` '.$direction ;
		$db->setQuery($q);

		if ($oderId = $db->loadResult()) {
			return $oderId ;
		}
		return 0 ;
	}

    /**
     * This is a proxy function to return an order safely, we may set the getOrder function to private
     * Maybe the right place would be the controller, cause there are JRequests in it. But for a fast solution,
     * still better than to have it 3-4 times in the view.html.php of the views.
     * @author Max Milbers
     *
     * @return array
     */
    public function getMyOrderDetails($orderID = 0, $orderNumber = false, $orderPass = false){

        $_currentUser = JFactory::getUser();
        $cuid = $_currentUser->get('id');

		$orderDetails = false;
        // If the user is not logged in, we will check the order number and order pass
        if(empty($orderID) and empty($cuid)){
            // If the user is not logged in, we will check the order number and order pass
            if ($orderPass = JRequest::getString('order_pass',$orderPass)){
                $orderNumber = JRequest::getString('order_number',$orderNumber);
                $orderId = $this->getOrderIdByOrderPass($orderNumber,$orderPass);
                if(empty($orderId)){
                    echo JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
                    return false;
                }
                $orderDetails = $this->getOrder($orderId);
            }
        }
        else {
            // If the user is logged in, we will check if the order belongs to him
            $virtuemart_order_id = JRequest::getInt('virtuemart_order_id',$orderID) ;
            if (!$virtuemart_order_id) {
                $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber(JRequest::getString('order_number'));
            }
            $orderDetails = $this->getOrder($virtuemart_order_id);

            if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
            if(!Permissions::getInstance()->check("admin")) {
                if(!isset($orderDetails['details']['BT']->virtuemart_user_id)){
                    $orderDetails['details']['BT']->virtuemart_user_id = 0;
                }
                //if(!empty($orderDetails['details']['BT']->virtuemart_user_id)){
                vmdebug('getMyOrderDetails',$cuid,$orderDetails['details']['BT']->virtuemart_user_id);
                if ($orderDetails['details']['BT']->virtuemart_user_id != $cuid) {
                    echo JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
                    return false;
                }
                //}
            }

        }
        return $orderDetails;
    }

	/**
	 * Load a single order, Attention, this function is not protected! Do the right manangment before, to be certain
     * we suggest to use getMyOrderDetails
	 */
	public function getOrder($virtuemart_order_id){

		//sanitize id
		$virtuemart_order_id = (int)$virtuemart_order_id;
		$db = JFactory::getDBO();
		$order = array();

		// Get the order details
		$q = "SELECT  u.*,o.*,
				s.order_status_name
			FROM #__virtuemart_orders o
			LEFT JOIN #__virtuemart_orderstates s
			ON s.order_status_code = o.order_status
			LEFT JOIN #__virtuemart_order_userinfos u
			ON u.virtuemart_order_id = o.virtuemart_order_id
			WHERE o.virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['details'] = $db->loadObjectList('address_type');

		// Get the order history
		$q = "SELECT *
			FROM #__virtuemart_order_histories
			WHERE virtuemart_order_id=".$virtuemart_order_id."
			ORDER BY virtuemart_order_history_id ASC";
		$db->setQuery($q);
		$order['history'] = $db->loadObjectList();

		// Get the order items
$q = 'SELECT virtuemart_order_item_id, product_quantity, order_item_name,
    order_item_sku, i.virtuemart_product_id, product_item_price,
    product_final_price, product_basePriceWithTax, product_discountedPriceWithoutTax, product_priceWithoutTax, product_subtotal_with_tax, product_subtotal_discount, product_tax, product_attribute, order_status, p.product_available_date, p.product_availability,
    intnotes, virtuemart_category_id
   FROM (#__virtuemart_order_items i
   LEFT JOIN #__virtuemart_products p
   ON p.virtuemart_product_id = i.virtuemart_product_id)
                        LEFT JOIN #__virtuemart_product_categories c
                        ON p.virtuemart_product_id = c.virtuemart_product_id
   WHERE `virtuemart_order_id`="'.$virtuemart_order_id.'" group by `virtuemart_order_item_id`';
//group by `virtuemart_order_id`'; Why ever we added this, it makes trouble, only one order item is shown then.
// without group by we get the product 3 times, when it is in 3 categories and similar, so we need a group by
//lets try group by `virtuemart_order_item_id`
		$db->setQuery($q);
		$order['items'] = $db->loadObjectList();
// Get the order items
		$q = "SELECT  *
			FROM #__virtuemart_order_calc_rules AS z
			WHERE  virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['calc_rules'] = $db->loadObjectList();
// 		vmdebug('getOrder my order',$order);
		return $order;
	}

	/**
	 * Select the products to list on the product list page
	 * @param $uid integer Optional user ID to get the orders of a single user
	 * @param $_ignorePagination boolean If true, ignore the Joomla pagination (for embedded use, default false)
	 */
	public function getOrdersList($uid = 0, $noLimit = false)
	{
// 		vmdebug('getOrdersList');
		$this->_noLimit = $noLimit;
		$select = " o.*, CONCAT_WS(' ',u.first_name,u.middle_name,u.last_name) AS order_name "
		.',u.email as order_email,pm.payment_name AS payment_method ';
		$from = $this->getOrdersListQuery();
		/*		$_filter = array();
		 if ($uid > 0) {
		$_filter[] = ('u.virtuemart_user_id = ' . (int)$uid);
		}*/

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		if(!Permissions::getInstance()->check('admin')){
			$myuser		=JFactory::getUser();
			$where[]= ' u.virtuemart_user_id = ' . (int)$myuser->id.' AND o.virtuemart_vendor_id = "1" ';
		} else {
			if(empty($uid)){
				$where[]= ' o.virtuemart_vendor_id = "1" ';
			} else {
				$where[]= ' u.virtuemart_user_id = ' . (int)$uid.' AND o.virtuemart_vendor_id = "1" ';
			}
		}


		if ($search = JRequest::getString('search', false)){

			$search = '"%' . $this->_db->getEscaped( $search, true ) . '%"' ;
			$search = str_replace(' ','%',$search);

			$searchFields = array();
			$searchFields[] = 'u.first_name';
			$searchFields[] = 'u.middle_name';
			$searchFields[] = 'u.last_name';
			$searchFields[] = 'o.order_number';
			$searchFields[] = 'u.company';
			$searchFields[] = 'u.email';
			$searchFields[] = 'u.phone_1';
			$searchFields[] = 'u.address_1';
			$searchFields[] = 'u.zip';
			$where[] = implode (' LIKE '.$search.' OR ', $searchFields) . ' LIKE '.$search.' ';
			//$where[] = ' ( u.first_name LIKE '.$search.' OR u.middle_name LIKE '.$search.' OR u.last_name LIKE '.$search.' OR `order_number` LIKE '.$search.')';
		}

		$order_status_code = JRequest::getString('order_status_code', false);
		if ($order_status_code and $order_status_code!=-1){
			$where[] = ' o.order_status = "'.$order_status_code.'" ';
		}

		if (count ($where) > 0) {
			$whereString = ' WHERE (' . implode (' AND ', $where) . ') ';
		}
		else {
			$whereString = '';
		}

		if ( JRequest::getCmd('view') == 'orders') {
			$ordering = $this->_getOrdering();
		} else {
			$ordering = ' order by o.modified_on DESC';
		}

		$this->_data = $this->exeSortSearchListQuery(0,$select,$from,$whereString,'',$ordering);


		return $this->_data ;
	}

	/**
	 * List of tables to include for the product query
	 * @author RolandD
	 */
	private function getOrdersListQuery()
	{
		return ' FROM #__virtuemart_orders as o
			LEFT JOIN #__virtuemart_order_userinfos as u
			ON u.virtuemart_order_id = o.virtuemart_order_id AND u.address_type="BT"
			LEFT JOIN #__virtuemart_paymentmethods_'.VMLANG.' as pm
			ON o.virtuemart_paymentmethod_id = pm.virtuemart_paymentmethod_id ';
	}


	/**
	 * Update an order item status
	 * @author Max Milbers
	 * @author Ondřej Spilka - used for item edit also
	 * @author Maik Künnemann
	 */
	public function updateSingleItem($virtuemart_order_item_id, &$orderdata, $orderUpdate = false)
	{
		//vmdebug('updateSingleItem',$virtuemart_order_item_id,$orderdata);
		$table = $this->getTable('order_items');
		$table->load($virtuemart_order_item_id);
		$oldOrderStatus = $table->order_status;

		if(empty($oldOrderStatus)){
			$oldOrderStatus = $orderdata->current_order_status;
			if($orderUpdate and empty($oldOrderStatus)){
				$oldOrderStatus = 'P';
			}
		}

// 		$table->order_status = $orderdata->orderstatus;

/*
// 		JPluginHelper::importPlugin('vmcustom');
// 		$_dispatcher = JDispatcher::getInstance();
// 		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateSingleItem',array($table,&$orderdata));
*/
		$dataT = get_object_vars($table);

//		$doUpdate = JRequest::getString('update_values');

		$orderdatacopy = $orderdata;
		$data = array_merge($dataT,(array)$orderdatacopy);
// 		$data['order_status'] = $orderdata->orderstatus;
		if (!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if ( $orderUpdate and !empty($data['virtuemart_order_item_id'])) {
			$this->_currencyDisplay = CurrencyDisplay::getInstance();
			$rounding = $this->_currencyDisplay->_priceConfig['salesPrice'][1];

			//get tax calc_value of product VatTax
			$db = JFactory::getDBO();
			$sql = "SELECT `calc_value` FROM `#__virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = ".$data['virtuemart_order_id']." AND `virtuemart_order_item_id` = ".$data['virtuemart_order_item_id']." AND `calc_kind` = 'VatTax' ";
			$db->setQuery($sql);
			$taxCalcValue = $db->loadResult();

			if($data['calculate_product_tax']) {
				if(!$taxCalcValue){
					//Could be a new item, missing the tax rules, we try to get one of another product.
					//get tax calc_value of product VatTax
					$db = JFactory::getDBO();
					$sql = "SELECT `calc_value` FROM `#__virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = ".$data['virtuemart_order_id']." AND `calc_kind` = 'VatTax' ";
					$db->setQuery($sql);
					$taxCalcValue = $db->loadResult();
				}

				if(empty($data['product_subtotal_discount']))$data['product_subtotal_discount'] = 0.0; // "",null,0,NULL, FALSE => 0.0

				//We do two cases, either we have the final amount and discount
				if(!empty($data['product_final_price']) and $data['product_final_price']!=0){

					if(empty($data['product_tax']) or $data['product_tax']==0){
						$data['product_tax'] = $data['product_final_price'] * $taxCalcValue / ($taxCalcValue + 100);
						//vmdebug($data['product_final_price'] .' * '.$taxCalcValue.' / '.($taxCalcValue + 100).' = '.$data['product_tax']);
					}

					if(empty($data['product_item_price']) or $data['product_item_price']==0){
						if(empty($data['product_tax']))$data['product_tax'] = 0.0;

						$data['product_item_price'] = round($data['product_final_price'], $rounding) - $data['product_tax'];
						$data['product_discountedPriceWithoutTax'] = 0.0;// round($data['product_final_price'], $rounding) ;
						$data['product_priceWithoutTax'] = 0.0;
						$data['product_basePriceWithTax'] =  round($data['product_final_price'], $rounding) - $data['product_subtotal_discount'];
					}

				} else
					//or we have the base price and a manually set discount.
					if(!empty($data['product_item_price']) and $data['product_item_price']!=0){
						if(empty($data['product_tax']) or $data['product_tax']==0){
							$data['product_tax'] = ($data['product_item_price']-$data['product_subtotal_discount']) * ($taxCalcValue/100.0);
						}
						$data['product_discountedPriceWithoutTax'] = 0.0;
						$data['product_priceWithoutTax'] = 0.0;
						$data['product_final_price'] = round($data['product_item_price'], $rounding) + $data['product_tax'] + $data['product_subtotal_discount'];
						$data['product_basePriceWithTax'] =  round($data['product_final_price'], $rounding) - $data['product_subtotal_discount'];
				}

			}
			//$data['product_subtotal_discount'] = (round($orderdata->product_final_price, $rounding) - round($data['product_basePriceWithTax'], $rounding)) * $orderdata->product_quantity;
			$data['product_subtotal_with_tax'] = round($data['product_final_price'], $rounding) * $orderdata->product_quantity;
		}

		$table->bindChecknStore($data);

		if ( $orderUpdate ) {
			if ( empty($data['order_item_sku']) )
			{
				//update product identification
				$db = JFactory::getDBO();
				$prolang = '#__virtuemart_products_' . VMLANG;
				$oi = " #__virtuemart_order_items";
				$protbl = "#__virtuemart_products";
				$sql = "UPDATE $oi, $protbl,  $prolang" .
					" SET $oi.order_item_sku=$protbl.product_sku, $oi.order_item_name=$prolang.product_name ".
					" WHERE $oi.virtuemart_product_id=$protbl.virtuemart_product_id " . 
					" and $oi.virtuemart_product_id=$prolang.virtuemart_product_id " .
					" and $oi.virtuemart_order_item_id=$virtuemart_order_item_id";
				$db->setQuery($sql);
				if ($db->query() === false) {
					vmError($db->getError());
				}	
			}
		}
			
		// Update the order item history
		//$this->_updateOrderItemHist($id, $order_status, $customer_notified, $comment);
		$errors = $table->getErrors();
		foreach($errors as $error){
			vmError( get_class( $this ).'::store '.$error);
		}


		//OSP update cartRules/shipment/payment
		//it would seem strange this is via item edit
		//but in general, shipment and payment would be tractated as another items of the order
		//in datas they are not, bu okay we have it here and functional
		//moreover we can compute all aggregate values here via one aggregate SQL
		if ( $orderUpdate )
		{
			$db = JFactory::getDBO();
			$ordid = $table->virtuemart_order_id;

			//cartRules
			$calc_rules = JRequest::getVar('calc_rules','', '', 'array');
			$calc_rules_amount = 0;
			$calc_rules_discount_amount = 0;
			$calc_rules_tax_amount = 0;

			if(!empty($calc_rules))
			{
				foreach($calc_rules as $calc_kind => $calc_rule) {
					foreach($calc_rule as $virtuemart_order_calc_rule_id => $calc_amount) {
						$sql = "UPDATE `#__virtuemart_order_calc_rules` SET `calc_amount`=$calc_amount WHERE `virtuemart_order_calc_rule_id`=$virtuemart_order_calc_rule_id";
						$db->setQuery($sql);
						if(isset($calc_amount)) $calc_rules_amount += $calc_amount;
						if ($calc_kind == 'DBTaxRulesBill' || $calc_kind == 'DATaxRulesBill') {
							$calc_rules_discount_amount += $calc_amount;
						}
						if ($calc_kind == 'taxRulesBill') {
							$calc_rules_tax_amount += $calc_amount;
						}
						if ($db->query() === false) {
							vmError($db->getError());
						}
					}
				}
			}

			//shipment
			$os = JRequest::getString('order_shipment');
			$ost = JRequest::getString('order_shipment_tax');

			if ( $os!="" )
			{
				$sql = "UPDATE `#__virtuemart_orders` SET `order_shipment`=$os,`order_shipment_tax`=$ost WHERE  `virtuemart_order_id`=$ordid";
				$db->setQuery($sql);
				if ($db->query() === false) {
					vmError($db->getError());
				}
			}

			//payment
			$op = JRequest::getString('order_payment');
			$opt = JRequest::getString('order_payment_tax');
			if ( $op!="" )
			{
				$sql = "UPDATE `#__virtuemart_orders` SET `order_payment`=$op,`order_payment_tax`=$opt WHERE  `virtuemart_order_id`=$ordid";
				$db->setQuery($sql);
				if ($db->query() === false) {
					vmError($db->getError());
				}
			}

			$sql = "
					UPDATE `#__virtuemart_orders` 
					SET 
					`order_total`=(SELECT sum(product_final_price*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid)+`order_shipment`+`order_shipment_tax`+`order_payment`+`order_payment_tax`+$calc_rules_amount,
					`order_discountAmount`=(SELECT sum(product_subtotal_discount) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid),
					`order_billDiscountAmount`=`order_discountAmount`+$calc_rules_discount_amount,
					`order_salesPrice`=(SELECT sum(product_final_price*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid),
					`order_tax`=(SELECT sum( product_tax*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid),
					`order_subtotal`=(SELECT sum(ROUND(product_item_price, ". $rounding .")*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid),";

			if(JRequest::getString('calculate_billTaxAmount')) {
				$sql .= "`order_billTaxAmount`=(SELECT sum( product_tax*product_quantity) FROM #__virtuemart_order_items where `virtuemart_order_id`=$ordid)+`order_shipment_tax`+`order_payment_tax`+$calc_rules_tax_amount";
			} else {
				$sql .= "`order_billTaxAmount`=".JRequest::getString('order_billTaxAmount');
			}

			$sql .= " WHERE  `virtuemart_order_id`=$ordid";

			$db->setQuery($sql); 
			if ($db->query() === false) {
				vmError('updateSingleItem '.$db->getError().' and '.$sql);
			}

		}


		$this->handleStockAfterStatusChangedPerProduct($orderdata->order_status, $oldOrderStatus, $table,$table->product_quantity);

// 		}

	}


	/**
	 * Strange name is just temporarly
	 *
	 * @param unknown_type $order_id
	 * @param unknown_type $order_status
         * @author Max Milbers
	 */
	var $useDefaultEmailOrderStatus = true;
	public function updateOrderStatus($orders=0, $order_id =0,$order_status=0){

		//General change of orderstatus
		$total = 1 ;
		if(empty($orders)){
			$orders = array();
			$orderslist = JRequest::getVar('orders',  array());
			$total = 0 ;
			// Get the list of orders in post to update
			foreach ($orderslist as $key => $order) {
				if ( $orderslist[$key]['order_status'] !== $orderslist[$key]['current_order_status'] ) {
					$orders[$key] =  $orderslist[$key];
					$total++;
				}
			}
		}

		if(!is_array($orders)){
			$orders = array($orders);
		}


		/* Process the orders to update */
		$updated = 0;
		$error = 0;
		if ($orders) {
			// $notify = JRequest::getVar('customer_notified', array()); // ???
			// $comments = JRequest::getVar('comments', array()); // ???
			foreach ($orders as $virtuemart_order_id => $order) {
				if  ($order_id >0) $virtuemart_order_id= $order_id;
				$this->useDefaultEmailOrderStatus = false;

				if($this->updateStatusForOneOrder($virtuemart_order_id,$order)){
					$updated ++;
				} else {
					$error++;
				}
			}
		}
		$result = array( 'updated' => $updated , 'error' =>$error , 'total' => $total ) ;
		return $result ;

	}

	// IMPORTANT: The $inputOrder can contain extra data by plugins			//also strange $useTriggers is always activated?
	function updateStatusForOneOrder($virtuemart_order_id,$inputOrder,$useTriggers=true){

// 		vmdebug('updateStatusForOneOrder', $inputOrder);
		/* Update the order */
		$data = $this->getTable('orders');
		$data->load($virtuemart_order_id);
		$old_order_status = $data->order_status;
		$data->bind($inputOrder);

		$cp_rm = VmConfig::get('cp_rm',array('C'));
		if(!is_array($cp_rm)) $cp_rm = array($cp_rm);

		if ( in_array((string) $data->order_status,$cp_rm) ){
			if (!empty($data->coupon_code)) {
				if (!class_exists('CouponHelper'))
					require(JPATH_VM_SITE . DS . 'helpers' . DS . 'coupon.php');
				CouponHelper::RemoveCoupon($data->coupon_code);
			}
		}
		//First we must call the payment, the payment manipulates the result of the order_status
		if($useTriggers){
				if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');
				// Payment decides what to do when order status is updated
				JPluginHelper::importPlugin('vmpayment');
				JPluginHelper::importPlugin('vmcalculation');
				JPluginHelper::importPlugin('vmcustom');
				$_dispatcher = JDispatcher::getInstance();											//Should we add this? $inputOrder
				$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderPayment',array(&$data,$old_order_status));
				foreach ($_returnValues as $_returnValue) {
					if ($_returnValue === true) {
						break; // Plugin was successfull
					} elseif ($_returnValue === false) {
						return false; // Plugin failed
					}
					// Ignore null status and look for the next returnValue
				}

			JPluginHelper::importPlugin('vmshipment');
			$_dispatcher = JDispatcher::getInstance();											//Should we add this? $inputOrder
			$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderShipment',array(&$data,$old_order_status));


			/**
			* If an order gets cancelled, fire a plugin event, perhaps
			* some authorization needs to be voided
			*/
			if ($data->order_status == "X") {
				JPluginHelper::importPlugin('vmpayment');			//Should we add this? $inputOrder
				JPluginHelper::importPlugin('vmcalculation');
				$_dispatcher = JDispatcher::getInstance();
				//Should be renamed to plgVmOnCancelOrder
				$_dispatcher->trigger('plgVmOnCancelPayment',array(&$data,$old_order_status));
			}
		}

		if(empty($data->delivery_date)){
			$del_date_type = VmConfig::get('del_date_type','m');
			if(strpos($del_date_type,'os')!==FALSE){	//for example osS
				$os = substr($del_date_type,2);
				if($data->order_status == $os){
					$date = JFactory::getDate();
					$data->delivery_date = $date->toMySQL();
				}
			} else {
				VmConfig::loadJLang('com_virtuemart_orders', true);
				$data->delivery_date = JText::_('COM_VIRTUEMART_DELDATE_INV');
			}
		}

		if ($data->store()) {

			$task= JRequest::getCmd('task',0);
			$view= JRequest::getWord('view',0);

			/*if($task=='edit'){
				$update_lines = JRequest::getInt('update_lines');
			} else /*/
			if ($task=='updatestatus' and $view=='orders') {
				$lines = JRequest::getVar('orders');
				$update_lines = $lines[$virtuemart_order_id]['update_lines'];
			} else {
				$update_lines = 1;
			}

			if($update_lines==1){
				vmdebug('$update_lines '.$update_lines);
				$q = 'SELECT virtuemart_order_item_id
												FROM #__virtuemart_order_items
												WHERE virtuemart_order_id="'.$virtuemart_order_id.'"';
				$db = JFactory::getDBO();
				$db->setQuery($q);
				$order_items = $db->loadObjectList();
				if ($order_items) {
// 				vmdebug('updateStatusForOneOrder',$data);
					foreach ($order_items as $order_item) {

						//$this->updateSingleItem($order_item->virtuemart_order_item_id, $data->order_status, $order['comments'] , $virtuemart_order_id, $data->order_pass);
						$this->updateSingleItem($order_item->virtuemart_order_item_id, $data);
					}
				}
			}


			/* Update the order history */
			$this->_updateOrderHist($virtuemart_order_id, $data->order_status, $inputOrder['customer_notified'], $inputOrder['comments']);

			// When the plugins did not already notified the user, do it here (the normal way)
			//Attention the ! prevents at the moment that an email is sent. But it should used that way.
// 			if (!$inputOrder['customer_notified']) {
			$this->notifyCustomer( $data->virtuemart_order_id , $inputOrder );
// 			}

			JPluginHelper::importPlugin('vmcoupon');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmCouponUpdateOrderStatus', array($data, $old_order_status));
			if(!empty($returnValues)){
				foreach ($returnValues as $returnValue) {
					if ($returnValue !== null  ) {
						return $returnValue;
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update an order status and send e-mail if needed
	 * @author RolandD
	 * @author Oscar van Eijk
	 * @deprecated
	 */
	public function updateStatus( $orders=null,$virtuemart_order_id =0){
		$this -> updateOrderStatus($orders,$virtuemart_order_id);
		return;
	}

	/**
	 * Get the information from the cart and create an order from it
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @return mixed The new ordernumber, false on errors
	 */
	public function createOrderFromCart($cart)
	{

		if ($cart === null) {
			vmError('createOrderFromCart() called without a cart - that\'s a programming bug','Can\'t create order, sorry.');
			return false;
		}

		$usr = JFactory::getUser();
		$prices = $cart->getCartPrices();
		if (($orderID = $this->_createOrder($cart, $usr, $prices)) == 0) {
			vmError('Couldn\'t create order','Couldn\'t create order');
			return false;
		}
		if (!$this->_createOrderLines($orderID, $cart)) {
			vmError('Couldn\'t create order items','Couldn\'t create order items');
			return false;
		}
		if (!$this-> _createOrderCalcRules($orderID, $cart) ) {
			vmError('Couldn\'t create order items','Couldn\'t create order items');
			return false;
		}
		$this->_updateOrderHist($orderID);
		if (!$this->_writeUserInfo($orderID, $usr, $cart)) {
			vmError('Couldn\'t create order history','Couldn\'t create order history');
			return false;
		}

		return $orderID;
	}

	/**
	 * Write the order header record
	 *
	 * @author Oscar van Eijk
	 * @param object $_cart The cart data
	 * @param object $_usr User object
	 * @param array $_prices Price data
	 * @return integer The new ordernumber
	 */
	private function _createOrder($_cart, $_usr, $_prices)
	{
		//		TODO We need tablefields for the new values:
		//		Shipment:
		//		$_prices['shipmentValue']		w/out tax
		//		$_prices['shipmentTax']			Tax
		//		$_prices['salesPriceShipment']	Total
		//
		//		Payment:
		//		$_prices['paymentValue']		w/out tax
		//		$_prices['paymentTax']			Tax
		//		$_prices['paymentDiscount']		Discount
		//		$_prices['salesPricePayment']	Total

		$_orderData = new stdClass();

		$_orderData->virtuemart_order_id = null;
		$_orderData->virtuemart_user_id = $_usr->get('id');
		$_orderData->virtuemart_vendor_id = $_cart->vendorId;
		$_orderData->customer_number = $_cart->customer_number;

		//Note as long we do not have an extra table only storing addresses, the virtuemart_userinfo_id is not needed.
		//The virtuemart_userinfo_id is just the id of a stored address and is only necessary in the user maintance view or for choosing addresses.
		//the saved order should be an snapshot with plain data written in it.
		//		$_orderData->virtuemart_userinfo_id = 'TODO'; // $_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
		$_orderData->order_total = $_prices['billTotal'];
		$_orderData->order_salesPrice = $_prices['salesPrice'];
		$_orderData->order_billTaxAmount = $_prices['billTaxAmount'];
		$_orderData->order_billDiscountAmount = $_prices['billDiscountAmount'];
		$_orderData->order_discountAmount = $_prices['discountAmount'];
		$_orderData->order_subtotal = $_prices['priceWithoutTax'];
		$_orderData->order_tax = $_prices['taxAmount'];
		$_orderData->order_shipment = $_prices['shipmentValue'];
		$_orderData->order_shipment_tax = $_prices['shipmentTax'];
		$_orderData->order_payment = $_prices['paymentValue'];
		$_orderData->order_payment_tax = $_prices['paymentTax'];

		if (!empty($_cart->cartData['VatTax'])) {
			$taxes = array();
			foreach($_cart->cartData['VatTax'] as $k=>$VatTax) {
				$taxes[$k]['virtuemart_calc_id'] = $k;
				$taxes[$k]['calc_name'] = $VatTax['calc_name'];
				$taxes[$k]['calc_value'] = $VatTax['calc_value'];
				$taxes[$k]['result'] = $VatTax['result'];
			}
			$_orderData->order_billTax = json_encode($taxes);
		}

		if (!empty($_cart->couponCode)) {
			$_orderData->coupon_code = $_cart->couponCode;
			$_orderData->coupon_discount = $_prices['salesPriceCoupon'];
		}
		$_orderData->order_discount = $_prices['discountAmount'];  // discount order_items


		$_orderData->order_status = 'P';
		$_orderData->order_currency = $this->getVendorCurrencyId($_orderData->virtuemart_vendor_id);

		if (isset($_cart->pricesCurrency)) {
			$_orderData->user_currency_id = $_cart->paymentCurrency ;//$this->getCurrencyIsoCode($_cart->pricesCurrency);
			$currency = CurrencyDisplay::getInstance($_orderData->user_currency_id);
			if($_orderData->user_currency_id != $_orderData->order_currency){
				$_orderData->user_currency_rate =   $currency->convertCurrencyTo($_orderData->user_currency_id ,1.0,false);
			} else {
				$_orderData->user_currency_rate=1.0;
			}
		}

		$_orderData->virtuemart_paymentmethod_id = $_cart->virtuemart_paymentmethod_id;
		$_orderData->virtuemart_shipmentmethod_id = $_cart->virtuemart_shipmentmethod_id;

		$_filter = JFilterInput::getInstance (array('br', 'i', 'em', 'b', 'strong'), array(), 0, 0, 1);
		$_orderData->customer_note = $_filter->clean($_cart->customer_comment);
		$_orderData->order_language = $_cart->order_language;
		$_orderData->ip_address = $_SERVER['REMOTE_ADDR'];

		$_orderData->order_number ='';
		JPluginHelper::importPlugin('vmshopper');
		$dispatcher = JDispatcher::getInstance();
		$plg_datas = $dispatcher->trigger('plgVmOnUserOrder',array(&$_orderData));
		foreach($plg_datas as $plg_data){
			// 				$data = array_merge($plg_data,$data);
		}
		if(empty($_orderData->order_number)){
			$_orderData->order_number = $this->generateOrderNumber($_usr->get('id'),4,$_orderData->virtuemart_vendor_id);
		}
		if(empty($_orderData->order_pass)){
			$_orderData->order_pass = 'p_'.substr( md5((string)time().rand(1,1000).$_orderData->order_number ), 0, 5);
		}

		$orderTable =  $this->getTable('orders');
		$orderTable -> bindChecknStore($_orderData);
		$errors = $orderTable->getErrors();
		foreach($errors as $error){
			vmError($error);
		}

		$db = JFactory::getDBO();
		$_orderID = $db->insertid();

		if (!empty($_cart->couponCode)) {
			//set the virtuemart_order_id in the Request for 3rd party coupon components (by Seyi and Max)
			JRequest::setVar ( 'virtuemart_order_id', $_orderData->virtuemart_order_id );
			// If a gift coupon was used, remove it now
			//CouponHelper::RemoveCoupon($_cart->couponCode);
			CouponHelper::setInUseCoupon($_cart->couponCode, true);
		}
		// the order number is saved into the session to make sure that the correct cart is emptied with the payment notification
		$_cart->order_number=$_orderData->order_number;
		$_cart->setCartIntoSession ();

		return $_orderID;
	}


	private function getVendorCurrencyId($vendorId){
		$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`="'.$vendorId.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$vendorCurrency =  $db->loadResult();
		return $vendorCurrency;
// 		return $this->getCurrencyIsoCode($vendorCurrency);
	}

	private function getCurrencyIsoCode($vmCode){
		$q = 'SELECT `currency_numeric_code` FROM  `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="'.$vmCode.'" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * Write the BillTo record, and if set, the ShipTo record
	 *
	 * @author Oscar van Eijk
	 * @param integer $_id Order ID
	 * @param object $_usr User object
	 * @param object $_cart Cart object
	 * @return boolean True on success
	 */
	private function _writeUserInfo($_id, &$_usr, $_cart)
	{
		$_userInfoData = array();

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');

		//if(!class_exists('shopFunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

		$_userFieldsModel = VmModel::getModel('userfields');
		$_userFieldsBT = $_userFieldsModel->getUserFields('account'
		, array('delimiters'=>true, 'captcha'=>true)
		, array('username', 'password', 'password2', 'user_is_vendor')
		);


		foreach ($_userFieldsBT as $_fld) {
			$_name = $_fld->name;
			if(!empty( $_cart->BT[$_name])){
				if (is_array( $_cart->BT[$_name])) {
					$_userInfoData[$_name] =  implode("|*|",$_cart->BT[$_name]);
				} else {
					$_userInfoData[$_name] = $_cart->BT[$_name];
				}

			}
		}

		$_userInfoData['virtuemart_order_id'] = $_id;
		$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
		$_userInfoData['address_type'] = 'BT';

		$order_userinfosTable = $this->getTable('order_userinfos');
		if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
			vmError($order_userinfosTable->getError());
			return false;
		}

		if ($_cart->ST) {
			$_userInfoData = array();
// 			$_userInfoData['virtuemart_order_userinfo_id'] = null; // Reset key to make sure it doesn't get overwritten by ST
			$_userFieldsST = $_userFieldsModel->getUserFields('shipment'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);
			foreach ($_userFieldsST as $_fld) {
				$_name = $_fld->name;
				if(!empty( $_cart->ST[$_name])){
					$_userInfoData[$_name] = $_cart->ST[$_name];
				}
			}

			$_userInfoData['virtuemart_order_id'] = $_id;
			$_userInfoData['virtuemart_user_id'] = $_usr->get('id');
			$_userInfoData['address_type'] = 'ST';
			$order_userinfosTable = $this->getTable('order_userinfos');
			if (!$order_userinfosTable->bindChecknStore($_userInfoData)){
				vmError($order_userinfosTable->getError());
				return false;
			}
		}
		return true;
	}


	function handleStockAfterStatusChangedPerProduct($newState, $oldState,$tableOrderItems, $quantity) {

		if($newState == $oldState) return;
		// $StatutWhiteList = array('P','C','X','R','S','N');
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM `#__virtuemart_orderstates` ');
		$StatutWhiteList = $db->loadAssocList('order_status_code');
		// new product is statut N
		$StatutWhiteList['N'] = Array ( 'order_status_id' => 0 , 'order_status_code' => 'N' , 'order_stock_handle' => 'A');
		if(!array_key_exists($oldState,$StatutWhiteList) or !array_key_exists($newState,$StatutWhiteList)) {
			vmError('The workflow for '.$newState.' or  '.$oldState.' is unknown, take a look on model/orders function handleStockAfterStatusChanged','Can\'t process workflow, contact the shopowner. Status is'.$newState);
			return ;
		}
		//vmdebug( 'updatestock qt :' , $quantity.' id :'.$productId);
		// P 	Pending
		// C 	Confirmed
		// X 	Cancelled
		// R 	Refunded
		// S 	Shipped
		// N 	New or coming from cart
		//  TO have no product setted as ordered when added to cart simply delete 'P' FROM array Reserved
		// don't set same values in the 2 arrays !!!
		// stockOut is in normal case shipped product
		//order_stock_handle
		// 'A' : stock Available
		// 'O' : stock Out
		// 'R' : stock reserved
		// the status decreasing real stock ?
		// $stockOut = array('S');
		if ($StatutWhiteList[$newState]['order_stock_handle'] == 'O') $isOut = 1;
		else $isOut = 0;
		if ($StatutWhiteList[$oldState]['order_stock_handle'] == 'O') $wasOut = 1;
		else $wasOut = 0;
		// $isOut = in_array($newState, $stockOut);
		// $wasOut= in_array($oldState, $stockOut);
		// Stock change ?
		if ($isOut && !$wasOut)     $product_in_stock = '-';
		else if ($wasOut && !$isOut ) $product_in_stock = '+';
		else $product_in_stock = '=';

		// the status increasing reserved stock(virtual Stock = product_in_stock - product_ordered)
		// $Reserved =  array('P','C');
		if ($StatutWhiteList[$newState]['order_stock_handle'] == 'R') $isReserved = 1;
		else $isReserved = 0;
		if ($StatutWhiteList[$oldState]['order_stock_handle'] == 'R') $wasReserved = 1;
		else $wasReserved = 0;
		// $isReserved = in_array($newState, $Reserved);
		// $wasReserved = in_array($oldState, $Reserved);
		// reserved stock must be change(all ordered product)
		if ($isReserved && !$wasReserved )     $product_ordered = '+';
		else if (!$isReserved && $wasReserved ) $product_ordered = '-';
		else $product_ordered = '=';

		//Here trigger plgVmGetProductStockToUpdateByCustom
		$productModel = VmModel::getModel('product');

		if (!empty($tableOrderItems->product_attribute)) {
			if(!class_exists('VirtueMartModelCustomfields'))require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
			$virtuemart_product_id = $tableOrderItems->virtuemart_product_id;
			$product_attributes = json_decode($tableOrderItems->product_attribute,true);
			foreach ($product_attributes as $virtuemart_customfield_id=>$param){
				if ($param) {
					if ($productCustom = VirtueMartModelCustomfields::getProductCustomField ($virtuemart_customfield_id ) ) {
						if ($productCustom->field_type == "E") {
								//$product = self::addParam($product);
								if(!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS.DS.'vmcustomplugin.php');
								JPluginHelper::importPlugin('vmcustom');
								$dispatcher = JDispatcher::getInstance();
							//vmdebug('handleStockAfterStatusChangedPerProduct ',$param);
								$dispatcher->trigger('plgVmGetProductStockToUpdateByCustom',array(&$tableOrderItems,$param, $productCustom));
						}
					}
				}
			}
			//vmdebug('produit',$product);
			// we can have more then one product in case of pack
			// in case of child, ID must be the child ID
			// TO DO use $prod->amount change for packs(eg. 1 computer and 2 HDD)
			if (is_array($tableOrderItems))	foreach ($tableOrderItems as $prod ) $productModel->updateStockInDB($prod, $quantity,$product_in_stock,$product_ordered);
			else $productModel->updateStockInDB($tableOrderItems, $quantity,$product_in_stock,$product_ordered);

		} else {
			$productModel->updateStockInDB ($tableOrderItems, $quantity,$product_in_stock,$product_ordered);
		}

	}

	/**
	 * Create the ordered item records
	 *
	 * @author Oscar van Eijk
	 * @author Kohl Patrick
	 * @param integer $_id integer Order ID
	 * @param object $_cart array The cart data
	 * @return boolean True on success
	 */
	private function _createOrderLines($_id, $_cart)
	{
		$_orderItems = $this->getTable('order_items');
		//		$_lineCount = 0;
		foreach ($_cart->products as $priceKey=>$_prod) {

			if (!is_int($priceKey)) {

				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$variantmods = $calculator->parseModifier($priceKey);

				$row=0 ;
				//$product_id = (int)$priceKey;
				$_prod->product_attribute = '';
				$product_attribute = array();
				//MarkerVarMods
				//foreach($variantmods as $variant=>$selected){
				foreach($variantmods as $selected=>$variant){
					if ($selected) {
						if(!class_exists('VirtueMartModelCustomfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
						$productCustom = VirtueMartModelCustomfields::getProductCustomField ($selected );
						//vmdebug('$_prod,$productCustom',$productCustom );
						if ($productCustom->field_type == "E") {

							if(!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS.DS.'vmcustomplugin.php');

							//We need something like this
							$product_attribute[$selected] = $productCustom->virtuemart_custom_id;
							//but seems we are forced to use this
							//$product_attribute[$selected] = $selected;
							if(!empty($_prod->param)){
								foreach($_prod->param as $k => $plg){
									if ($k == $selected){
										//TODO productCartId
										$product_attribute[$selected] = $plg ;
									}
								}
							}

						} else {
							$product_attribute[$selected] = ' <span class="costumTitle">'.$productCustom->custom_title.'</span><span class="costumValue" >'.$productCustom->custom_value.'</span>';
							//$product_attribute[$variant] = ' <span class="costumTitle">'.$productCustom->custom_title.'</span><span class="costumValue" >'.$productCustom->custom_value.'</span>';
						}
					}
					$row++;
				}
				//if (isset($_prod->userfield )) $_prod->product_attribute .= '<br/ > <b>'.$_prod->userfield.' : </b>';
				$_orderItems->product_attribute = json_encode($product_attribute);
				//print_r($product_attribute);
			} else {
			    $_orderItems->product_attribute = null ;
			}
			// TODO: add fields for the following data:
			//    * [double] basePrice = 38.48
			//    * [double] basePriceVariant = 38.48
			//    * [double] basePriceWithTax = 42.04
			//    * [double] discountedPriceWithoutTax = 36.48
			//    * [double] priceBeforeTax = 36.48
			//    * [double] salesPrice = 39.85
			//    * [double] salesPriceTemp = 39.85
			//    * [double] taxAmount = 3.37
			//    * [double] salesPriceWithDiscount = 0
			//    * [double] discountAmount = 2.19
			//    * [double] priceWithoutTax = 36.48
			//    * [double] variantModification = 0
			$_orderItems->virtuemart_order_item_id = null;
			$_orderItems->virtuemart_order_id = $_id;
// 			$_orderItems->virtuemart_userinfo_id = 'TODO'; //$_cart['BT']['virtuemart_userinfo_id']; // TODO; Add it in the cart... but where is this used? Obsolete?
			$_orderItems->virtuemart_vendor_id = $_prod->virtuemart_vendor_id;
			$_orderItems->virtuemart_product_id = $_prod->virtuemart_product_id;
			$_orderItems->order_item_sku = $_prod->product_sku;
			$_orderItems->order_item_name = $_prod->product_name; //TODO Patrick
			$_orderItems->product_quantity = $_prod->quantity;
			$_orderItems->product_item_price = $_cart->pricesUnformatted[$priceKey]['basePrice'];
			$_orderItems->product_basePriceWithTax = $_cart->pricesUnformatted[$priceKey]['basePriceWithTax'];
			$_orderItems->product_priceWithoutTax = $_cart->pricesUnformatted[$priceKey]['priceWithoutTax'];
			$_orderItems->product_discountedPriceWithoutTax = $_cart->pricesUnformatted[$priceKey]['discountedPriceWithoutTax'];
			//$_orderItems->product_tax = $_cart->pricesUnformatted[$priceKey]['subtotal_tax_amount'];
			$_orderItems->product_tax = $_cart->pricesUnformatted[$priceKey]['taxAmount'];
			$_orderItems->product_final_price = $_cart->pricesUnformatted[$priceKey]['salesPrice'];
			$_orderItems->product_subtotal_discount = $_cart->pricesUnformatted[$priceKey]['subtotal_discount'];
			$_orderItems->product_subtotal_with_tax = $_cart->pricesUnformatted[$priceKey]['subtotal_with_tax'];
			//			$_orderItems->order_item_currency = $_prices[$_lineCount]['']; // TODO Currency
			$_orderItems->order_status = 'P';


			if (!$_orderItems->check()) {
				vmError($this->getError());
				return false;
			}

			// Save the record to the database
			if (!$_orderItems->store()) {
				vmError($this->getError());
				return false;
			}
			$_prod->virtuemart_order_item_id = $_orderItems->virtuemart_order_item_id;
// 			vmdebug('_createOrderLines',$_prod);
			$this->handleStockAfterStatusChangedPerProduct( $_orderItems->order_status,'N',$_orderItems,$_orderItems->product_quantity);

		}
		//jExit();
		return true;
	}
/**
	 * Create the ordered item records
	 *
	 * @author Valerie Isaksen
	 * @param integer $_id integer Order ID
	 * @param object $_cart array The cart data
	 * @return boolean True on success
	 */
	private function _createOrderCalcRules($order_id, $_cart)
	{

		$productKeys = array_keys($_cart->products);

		$calculation_kinds = array('DBTax','Tax','VatTax','DATax');

		foreach($productKeys as $key){
			foreach($calculation_kinds as $calculation_kind) {

				if(!isset($_cart->pricesUnformatted[$key][$calculation_kind])) continue;
				$productRules = $_cart->pricesUnformatted[$key][$calculation_kind];

				foreach($productRules as $rule){
					$orderCalcRules = $this->getTable('order_calc_rules');
					$orderCalcRules->virtuemart_order_calc_rule_id= null;
					$orderCalcRules->virtuemart_calc_id= $rule[7];
					$orderCalcRules->virtuemart_order_item_id = $_cart->products[$key]->virtuemart_order_item_id;
					$orderCalcRules->calc_rule_name = $rule[0];
					$orderCalcRules->calc_amount =  0;
					$orderCalcRules->calc_result =  0;
					if ($calculation_kind == 'VatTax') {
						$orderCalcRules->calc_amount =  $_cart->pricesUnformatted[$key]['taxAmount'];
						$orderCalcRules->calc_result =  $_cart->cartData['VatTax'][$rule[7]]['result'];
					}
					$orderCalcRules->calc_value = $rule[1];
					$orderCalcRules->calc_mathop = $rule[2];
					$orderCalcRules->calc_kind = $calculation_kind;
					$orderCalcRules->calc_currency = $rule[4];
					$orderCalcRules->calc_params = $rule[5];
					$orderCalcRules->virtuemart_vendor_id = $rule[6];
					$orderCalcRules->virtuemart_order_id = $order_id;

					if (!$orderCalcRules->check()) {
						vmError('_createOrderCalcRules check product rule '.$this->getError());
						vmdebug('_createOrderCalcRules check product rule '.$this->getError());
						return false;
					}

					// Save the record to the database
					if (!$orderCalcRules->store()) {
						vmError('_createOrderCalcRules store product rule '.$this->getError());
						vmdebug('_createOrderCalcRules store product rule '.$this->getError());
						return false;
					}
				}

			}
		}


		$Bill_calculation_kinds=array('DBTaxRulesBill', 'taxRulesBill', 'DATaxRulesBill');
	//	vmdebug('_createOrderCalcRules',$_cart );
		foreach($Bill_calculation_kinds as $calculation_kind) {
// 			if(empty($_cart->cartData)){
// 				vmError('Cart data was empty, why?');
// 				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
// 				$calculator = calculationHelper::getInstance();
// 				$_cart->cartData = $calculator->getCartData();
// 			}

		    foreach($_cart->cartData[$calculation_kind] as $rule){
			    $orderCalcRules = $this->getTable('order_calc_rules');
			     $orderCalcRules->virtuemart_order_calc_rule_id = null;
				 $orderCalcRules->virtuemart_calc_id= $rule['virtuemart_calc_id'];
			     $orderCalcRules->calc_rule_name= $rule['calc_name'];
			     $orderCalcRules->calc_amount =  $_cart->pricesUnformatted[$rule['virtuemart_calc_id'].'Diff'];
				 if ($calculation_kind == 'taxRulesBill' and !empty($_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'])) {
					$orderCalcRules->calc_result =  $_cart->cartData['VatTax'][$rule['virtuemart_calc_id']]['result'];
				 }
			     $orderCalcRules->calc_kind=$calculation_kind;
			     $orderCalcRules->calc_mathop=$rule['calc_value_mathop'];
			     $orderCalcRules->virtuemart_order_id=$order_id;
			     $orderCalcRules->calc_params=$rule['calc_params'];
			     if (!$orderCalcRules->check()) {
				    vmError('_createOrderCalcRules store bill rule '.$this->getError());
				    return false;
			    }

			    // Save the record to the database
			    if (!$orderCalcRules->store()) {
				    vmError('_createOrderCalcRules store bill rule '.$this->getError());
				    return false;
			    }
		    }
		}

		if(!empty($_cart->virtuemart_paymentmethod_id)){

			$orderCalcRules = $this->getTable('order_calc_rules');
			$calcModel = VmModel::getModel('calc');
			$calcModel->setId($_cart->pricesUnformatted['payment_calc_id']);
			$calc = $calcModel->getCalc();
			$orderCalcRules->virtuemart_order_calc_rule_id = null;
			$orderCalcRules->virtuemart_calc_id = $calc->virtuemart_calc_id;
			$orderCalcRules->calc_kind = 'payment';
			$orderCalcRules->calc_rule_name = $calc->calc_name;
			$orderCalcRules->calc_amount = $_cart->pricesUnformatted['paymentTax'];
			$orderCalcRules->calc_value = $calc->calc_value;
			$orderCalcRules->calc_mathop = $calc->calc_value_mathop;
			$orderCalcRules->calc_currency = $calc->calc_currency;
			$orderCalcRules->calc_params = $calc->calc_params;
			$orderCalcRules->virtuemart_vendor_id = $calc->virtuemart_vendor_id;
			$orderCalcRules->virtuemart_order_id = $order_id;
			if (!$orderCalcRules->check()) {
				vmError('_createOrderCalcRules store payment rule '.$this->getError());
				return false;
			}

			// Save the record to the database
			if (!$orderCalcRules->store()) {
				vmError('_createOrderCalcRules store payment rule '.$this->getError());
				return false;
			}

		}

		if(!empty($_cart->virtuemart_shipmentmethod_id)){

			$orderCalcRules = $this->getTable('order_calc_rules');
			$calcModel = VmModel::getModel('calc');
			$calcModel->setId($_cart->pricesUnformatted['shipment_calc_id']);
			$calc = $calcModel->getCalc();

			$orderCalcRules->virtuemart_order_calc_rule_id = null;
			$orderCalcRules->virtuemart_calc_id = $calc->virtuemart_calc_id;
			$orderCalcRules->calc_kind = 'shipment';
			$orderCalcRules->calc_rule_name = $calc->calc_name;
			$orderCalcRules->calc_amount = $_cart->pricesUnformatted['shipmentTax'];
			$orderCalcRules->calc_value = $calc->calc_value;
			$orderCalcRules->calc_mathop = $calc->calc_value_mathop;
			$orderCalcRules->calc_currency = $calc->calc_currency;
			$orderCalcRules->calc_params = $calc->calc_params;
			$orderCalcRules->virtuemart_vendor_id = $calc->virtuemart_vendor_id;
			$orderCalcRules->virtuemart_order_id = $order_id;
			if (!$orderCalcRules->check()) {
				vmError('_createOrderCalcRules store shipment rule '.$this->getError());
				return false;
			}

			// Save the record to the database
			if (!$orderCalcRules->store()) {
				vmError('_createOrderCalcRules store shipment rule '.$this->getError());
				return false;
			}
		}


		//jExit();
		return true;
	}

	/**
	 * Update the order history
	 *
	 * @author Oscar van Eijk
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	public function _updateOrderHist($_id, $_status = 'P', $_notified = 0, $_comment = '')
	{
		$_orderHist = $this->getTable('order_histories');
		$_orderHist->virtuemart_order_id = $_id;
		$_orderHist->order_status_code = $_status;
		//$_orderHist->date_added = date('Y-m-d G:i:s', time());
		$_orderHist->customer_notified = $_notified;
		$_orderHist->comments = nl2br($_comment);
		$_orderHist->store();
	}	/**
	 * Update the order item history
	 *
	 * @author Oscar van Eijk,kohl patrick
	 * @param $_id Order ID
	 * @param $_status New order status (default: P)
	 * @param $_notified 1 (default) if the customer was notified, 0 otherwise
	 * @param $_comment (Customer) comment, default empty
	 */
	private function _updateOrderItemHist($_id, $status = 'P', $notified = 1, $comment = '')
	{
		$_orderHist = $this->getTable('order_item_histories');
		$_orderHist->virtuemart_order_item_id = $_id;
		$_orderHist->order_status_code = $status;
		$_orderHist->customer_notified = $notified;
		$_orderHist->comments = $comment;
		$_orderHist->store();
	}

	/**
	 * Generate a unique ordernumber. This is done in a similar way as VM1.1.x, although
	 * the reason for this is unclear to me :-S
	 *
	 * @author Oscar van Eijk
	 * @param integer $uid The user ID. Defaults to 0 for guests
	 * @return string A unique ordernumber
	 */
	static public function generateOrderNumber($uid = 0,$length=10, $virtuemart_vendor_id=1)
	{

		$db = JFactory::getDBO();

		$q = 'SELECT COUNT(1) FROM #__virtuemart_orders WHERE `virtuemart_vendor_id`="'.$virtuemart_vendor_id.'"';
		$db->setQuery($q);

		//We can use that here, because the order_number is free to set, the invoice_number must often follow special rules
		$count = $db->loadResult();
		$count = $count + (int)VM_ORDER_OFFSET;
// 		vmdebug('my db creating ordernumber VM_ORDER_OFFSET '.VM_ORDER_OFFSET.' $count '.$count, $this->_db);
// 		$variable_fixed=sprintf("%06s",$num_rows);
		$data = substr( md5( session_id().(string)time().(string)$uid )
		,0
		,$length
		).'0'.$count;

		return $data;
	}

/*
 * returns true if an invoice number has been created
 * returns false if an invoice number has not been created  due to some configuration parameters
 */
	function createInvoiceNumber($orderDetails, &$invoiceNumber){

		$orderDetails = (array)$orderDetails;
		$db = JFactory::getDBO();
		if(!isset($orderDetails['virtuemart_order_id'])){
			vmWarn('createInvoiceNumber $orderDetails has no virtuemart_order_id ',$orderDetails);
			vmdebug('createInvoiceNumber $orderDetails has no virtuemart_order_id ',$orderDetails);
		}
		$q = 'SELECT * FROM `#__virtuemart_invoices` WHERE `virtuemart_order_id`= "'.$orderDetails['virtuemart_order_id'].'" '; // AND `order_status` = "'.$orderDetails->order_status.'" ';

		$db->setQuery($q);
		$result = $db->loadAssoc();
// 		vmdebug('my createInvoiceNumber $q '.$q,$result);
		if (!class_exists('ShopFunctions')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
		if(!$result or   empty($result['invoice_number']) ){

			$data['virtuemart_order_id'] = $orderDetails['virtuemart_order_id'];

			$data['order_status'] = $orderDetails['order_status'];

			$data['virtuemart_vendor_id'] = $orderDetails['virtuemart_vendor_id'];

			JPluginHelper::importPlugin('vmshopper');
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			// plugin returns invoice number, 0 if it does not want an invoice number to be created by Vm
			$plg_datas = $dispatcher->trigger('plgVmOnUserInvoice',array($orderDetails,&$data));
			foreach($plg_datas as $plg_data){
// 				$data = array_merge($plg_data,$data);
			}
			if(!isset($data['invoice_number']) ) {
			    // check the default configuration
			    $orderstatusForInvoice = VmConfig::get('inv_os',array('C'));
				if(!is_array($orderstatusForInvoice)) $orderstatusForInvoice = array($orderstatusForInvoice); //for backward compatibility 2.0.8e
			    $pdfInvoice = (int)VmConfig::get('pdf_invoice', 0); // backwards compatible
			    $force_create_invoice=JRequest::getInt('create_invoice', 0);
			    // florian : added if pdf invoice are enabled
			    if ( in_array($orderDetails['order_status'],$orderstatusForInvoice)  or $pdfInvoice==1  or $force_create_invoice==1 ){
					$q = 'SELECT COUNT(1) FROM `#__virtuemart_invoices` WHERE `virtuemart_vendor_id`= "'.$orderDetails['virtuemart_vendor_id'].'" '; // AND `order_status` = "'.$orderDetails->order_status.'" ';
					$db->setQuery($q);

					$count = $db->loadResult()+1;

					if(empty($data['invoice_number'])) {
						//$variable_fixed=sprintf("%05s",$num_rows);
						$date = date("Y-m-d");
	// 					$date = JFactory::getDate()->toMySQL();
						$data['invoice_number'] = str_replace('-', '', substr($date,2,8)).substr(md5($orderDetails['order_number'].$orderDetails['order_status']),0,3).'0'.$count;
					}
			    } else {
					return false;
			    }
			}


			$table = $this->getTable('invoices');

			$table->bindChecknStore($data);
			$invoiceNumber= array($table->invoice_number,$table->created_on);
		} elseif (ShopFunctions::InvoiceNumberReserved($result['invoice_number']) ) {
			$invoiceNumber = array($result['invoice_number'],$result['created_on']);
		    return true;
		} else {
			$invoiceNumber = array($result['invoice_number'],$result['created_on']);
		}
		return true;
	}

	/*
	 * @author Valérie Isaksen
	 */
	function getInvoiceNumber($virtuemart_order_id){

		$db = JFactory::getDBO();
		$q = 'SELECT `invoice_number` FROM `#__virtuemart_invoices` WHERE `virtuemart_order_id`= "'.$virtuemart_order_id.'" ';
		$db->setQuery($q);
		return $db->loadresult();
	}


	/**
	 * Notifies the customer that the Order Status has been changed
	 *
	 * @author RolandD, Christopher Roussel, Valérie Isaksen, Max Milbers
	 *
	 */
	private function notifyCustomer($virtuemart_order_id, $newOrderData = 0 ) {

// 		vmdebug('notifyCustomer', $newOrderData);
		if (isset($newOrderData['customer_notified']) && $newOrderData['customer_notified']==0) {
		    return true;
		}
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		//Important, the data of the order update mails, payments and invoice should
		//always be in the database, so using getOrder is the right method
		$orderModel=VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$payment_name = $shipment_name='';
		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

		JPluginHelper::importPlugin('vmshipment');
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEShipment',array(  $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->virtuemart_shipmentmethod_id, &$shipment_name));
		$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEPayment',array(  $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->virtuemart_paymentmethod_id, &$payment_name));
		$order['shipmentName']=$shipment_name;
		$order['paymentName']=$payment_name;
		if($newOrderData!=0){	//We do not really need that
			$vars['newOrderData'] = (array)$newOrderData;
		}

		$vars['orderDetails']=$order;

		//$vars['includeComments'] = JRequest::getVar('customer_notified', array());
		//I think this is misleading, I think it should always ask for example $vars['newOrderData']['doVendor'] directly
		//Using this function garantue us that it is always there. If the vendor should be informed should be done by the plugins
		//We may add later something to the method, defining this better
		$vars['url'] = 'url';
		if(!isset($newOrderData['doVendor'])) $vars['doVendor'] = false; else $vars['doVendor'] = $newOrderData['doVendor'];

		$virtuemart_vendor_id=1;
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		$vars['vendor'] = $vendor;
		$vendorEmail = $vendorModel->getVendorEmail($virtuemart_vendor_id);
		$vars['vendorEmail'] = $vendorEmail;
/*
		$path = VmConfig::get('forSale_path',0);
		$orderstatusForInvoice = VmConfig::get('inv_os','C');
		$pdfInvoice = VmConfig::get('pdf_invoice', 1); // backwards compatible
*/
		// florian : added if pdf invoice are enabled
		//if  ($this->getInvoiceNumber( $order['details']['BT']->virtuemart_order_id ) ){
		$invoiceNumberDate = array();
		if ($orderModel->createInvoiceNumber($order['details']['BT'], $invoiceNumberDate )) {
			$orderstatusForInvoice = VmConfig::get('inv_os',array('C'));
			if(!is_array($orderstatusForInvoice)) $orderstatusForInvoice = array($orderstatusForInvoice);   // for backward compatibility 2.0.8e
			$pdfInvoice = (int)VmConfig::get('pdf_invoice', 0); // backwards compatible
			$force_create_invoice=JRequest::getInt('create_invoice', 0);
			//TODO we need an array of orderstatus
			if ( (in_array($order['details']['BT']->order_status,$orderstatusForInvoice))  or $pdfInvoice==1  or $force_create_invoice==1 ){
				if (!shopFunctions::InvoiceNumberReserved($invoiceNumberDate[0])) {
					if(!class_exists('VirtueMartControllerInvoice')) require( JPATH_VM_SITE.DS.'controllers'.DS.'invoice.php' );
					$controller = new VirtueMartControllerInvoice( array(
						'model_path' => JPATH_VM_SITE.DS.'models',
						'view_path' => JPATH_VM_SITE.DS.'views'
					));

					$vars['mediaToSend'][] = $controller->getInvoicePDF($order);
				}
			}

		}

		// Send the email
		$res = shopFunctionsF::renderMail('invoice', $order['details']['BT']->email, $vars, null,$vars['doVendor'],$this->useDefaultEmailOrderStatus);

		//We need this, to prevent that a false alert is thrown.
		if ($res and $res!=-1) {
			$string = 'COM_VIRTUEMART_NOTIFY_CUSTOMER_SEND_MSG';
		}
		else if (!$res) {
			$string = 'COM_VIRTUEMART_NOTIFY_CUSTOMER_ERR_SEND';
		}
		if($res!=-1){
			vmInfo( JText::_($string,false).' '.$order['details']['BT']->first_name.' '.$order['details']['BT']->last_name. ', '.$order['details']['BT']->email);
		}

		return true;
	}


	/**
	 * Retrieve the details for an order line item.
	 *
	 * @author RickG
	 * @param string $orderId Order id number
	 * @param string $orderLineId Order line item number
	 * @return object Object containing the order item details.
	 */
	function getOrderLineDetails($orderId, $orderLineId) {
		$table = $this->getTable('order_items');
		if ($table->load((int)$orderLineId)) {
			return $table;
		}
		else {
			$table->reset();
			$table->virtuemart_order_id = $orderId;
			return $table;
		}
	}


	/**
	 * Save an order line item.
	 *
	 * @author RickG
	 * @return boolean True of remove was successful, false otherwise
	 */
	function saveOrderLineItem($data) {
		$table = $this->getTable('order_items');

		//Done in the table already
		/*
		$curDate = JFactory::getDate();
		$data['modified_on'] = $curDate->toMySql();*/

		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmshipment');
		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderLineShipment',array( $data));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}
		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
		JPluginHelper::importPlugin('vmpayment');
		$_returnValues = $_dispatcher->trigger('plgVmOnUpdateOrderLinePayment',array( $data));
		foreach ($_returnValues as $_retVal) {
			if ($_retVal === false) {
				// Stop as soon as the first active plugin returned a failure status
				return;
			}
		}
		$table->bindChecknStore($data);
		return true;

		//		return true;
	}


	/*
	 *remove product from order item table
	*@var $virtuemart_order_id Order to clear
	*/
	function removeOrderItems ($virtuemart_order_id){
		$q ='DELETE from `#__virtuemart_order_items` WHERE `virtuemart_order_id` = ' .(int) $virtuemart_order_id;
		$this->_db->setQuery($q);

		if ($this->_db->query() === false) {
			vmError($this->_db->getError());
			return false;
		}
		return true;
	}
	/**
	 * Remove an order line item.
	 *
	 * @author RickG
	 * @param string $orderLineId Order line item number
	 * @return boolean True of remove was successful, false otherwise
	 */
	function removeOrderLineItem($orderLineId) {

		$item = $this->getTable('order_items');
		if (!$item->load($orderLineId)) {
			vmError($item->getError());
			return false;
		}
		//TODO Why should the stock change, when the order is deleted? Paypal? Valerie?
// 		$this->handleStockAfterStatusChangedPerProduct('C', $item->order_status,$item, $item->product_quantity);
		if ($item->delete($orderLineId)) {
			return true;
		}
		else {
			vmError($item->getError());
			return false;
		}
	}

	/**
	 * Delete all record ids selected
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 * @return boolean True is the delete was successful, false otherwise.
	 */
	public function remove($ids) {

		$table = $this->getTable($this->_maintablename);

		foreach($ids as $id) {

			// remove order_item and update stock
			$q = "SELECT `virtuemart_order_item_id` FROM `#__virtuemart_order_items`
				WHERE `virtuemart_order_id`=".$id;
			$this->_db->setQuery($q);
			$item_ids = $this->_db->loadResultArray();
			foreach( $item_ids as $item_id ) {
			    $this->removeOrderLineItem($item_id);
			}
			// rename invoice number by adding the date, and update the invoice table
			 $this->renameInvoice($id );


			if (!$table->delete((int)$id)) {
				vmError(get_class( $this ).'::remove '.$id.' '.$table->getError());
				return false;
			}
		}

		return true;
	}


	/** Update order head record
	*
	* @author Ondřej Spilka
	* @author Maik Künnemann
	* @return boolean True is the update was successful, otherwise false.
	*/ 
	public function UpdateOrderHead($virtuemart_order_id, $_orderData)
	{

		$orderTable = $this->getTable('orders');
		$orderTable->load($virtuemart_order_id);

		if (!$orderTable->bindChecknStore($_orderData, true)){
			vmError($orderTable->getError());
			return false;
		}

		$_userInfoData = array();

		if(!class_exists('VirtueMartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');

		$_userFieldsModel = VmModel::getModel('userfields');

		//bill to
		$_userFieldsBT = $_userFieldsModel->getUserFields('account'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);


		foreach ($_userFieldsBT as $_fld) {
			$_name = $_fld->name;
			if(!empty( $_orderData["BT_{$_name}"])){

				$_userInfoData[$_name] = $_orderData["BT_{$_name}"];
			}
		}

		$_userInfoData['virtuemart_order_id'] = $virtuemart_order_id;
		$_userInfoData['address_type'] = 'BT';

		$order_userinfosTable = $this->getTable('order_userinfos');
			$order_userinfosTable->load($virtuemart_order_id, 'virtuemart_order_id'," AND address_type='BT'");
		if (!$order_userinfosTable->bindChecknStore($_userInfoData, true)){
			vmError($order_userinfosTable->getError());
			return false;
		}

		//ship to
		$_userFieldsST = $_userFieldsModel->getUserFields('account'
			, array('delimiters'=>true, 'captcha'=>true)
			, array('username', 'password', 'password2', 'user_is_vendor')
			);

		$_userInfoData = array();
		foreach ($_userFieldsST as $_fld) {
			$_name = $_fld->name;
			if(!empty( $_orderData["ST_{$_name}"])){

				$_userInfoData[$_name] = $_orderData["ST_{$_name}"];
			}
		}

		$_userInfoData['virtuemart_order_id'] = $virtuemart_order_id;
		$_userInfoData['address_type'] = 'ST';

		$order_userinfosTable = $this->getTable('order_userinfos');
			$order_userinfosTable->load($virtuemart_order_id, 'virtuemart_order_id'," AND address_type='ST'");
		if (!$order_userinfosTable->bindChecknStore($_userInfoData, true)){
			vmError($order_userinfosTable->getError());
			return false;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$dispatcher = JDispatcher::getInstance();

		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		// Update Payment Method

		if($_orderData['old_virtuemart_paymentmethod_id'] != $_orderData['virtuemart_paymentmethod_id']) {


			$this->_db->setQuery( 'SELECT `payment_element` FROM `#__virtuemart_paymentmethods` , `#__virtuemart_orders`
					WHERE `#__virtuemart_paymentmethods`.`virtuemart_paymentmethod_id` = `#__virtuemart_orders`.`virtuemart_paymentmethod_id` AND `virtuemart_order_id` = ' . $virtuemart_order_id );
			$paymentTable = '#__virtuemart_payment_plg_'. $this->_db->loadResult();

			$this->_db->setQuery("DELETE from `". $paymentTable ."` WHERE `virtuemart_order_id` = " . $virtuemart_order_id);
			if ($this->_db->query() === false) {
				vmError($this->_db->getError());
				return false;
			} else {
				JPluginHelper::importPlugin('vmpayment');
			}

		}

		// Update Shipment Method

		if($_orderData['old_virtuemart_shipmentmethod_id'] != $_orderData['virtuemart_shipmentmethod_id']) {

			$this->_db->setQuery( 'SELECT `shipment_element` FROM `#__virtuemart_shipmentmethods` , `#__virtuemart_orders`
					WHERE `#__virtuemart_shipmentmethods`.`virtuemart_shipmentmethod_id` = `#__virtuemart_orders`.`virtuemart_shipmentmethod_id` AND `virtuemart_order_id` = ' . $virtuemart_order_id );
			$shipmentTable = '#__virtuemart_shipment_plg_'. $this->_db->loadResult();

			$this->_db->setQuery("DELETE from `". $shipmentTable ."` WHERE `virtuemart_order_id` = " . $virtuemart_order_id);
			if ($this->_db->query() === false) {
				vmError($this->_db->getError());
				return false;
			} else {
				JPluginHelper::importPlugin('vmshipment');
			}

		}


//		JPluginHelper::importPlugin('vmshipment');
//		JPluginHelper::importPlugin('vmcustom');

		if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$cart->virtuemart_paymentmethod_id = $_orderData['virtuemart_paymentmethod_id'];
		$cart->virtuemart_shipmentmethod_id = $_orderData['virtuemart_shipmentmethod_id'];

		$order['order_status'] = $order['details']['BT']->order_status;
		$order['customer_notified'] = 0;
		$order['comments'] = '';

		$returnValues = $dispatcher->trigger('plgVmConfirmedOrder', array($cart, $order));

		return true;
	}


	/** Create empty order head record from admin only
	*
	* @author Ondřej Spilka
	* @return ID of the newly created order
	*/ 
	public function CreateOrderHead()
	{
		// TODO 
		// multivendor
		//usrid
	
		$usrid = 0;
		$_orderData = new stdClass();

		$_orderData->virtuemart_order_id = null;
		$_orderData->virtuemart_user_id = 0;
		$_orderData->virtuemart_vendor_id = 1; //TODO

		$_orderData->order_total = 0;
		$_orderData->order_salesPrice = 0;
		$_orderData->order_billTaxAmount = 0;
		$_orderData->order_billDiscountAmount = 0;
		$_orderData->order_discountAmount = 0;
		$_orderData->order_subtotal = 0;
		$_orderData->order_tax = 0;
		$_orderData->order_shipment = 0;
		$_orderData->order_shipment_tax = 0;
		$_orderData->order_payment = 0;
		$_orderData->order_payment_tax = 0;

		$_orderData->order_discount = 0;
		$_orderData->order_status = 'P';
		$_orderData->order_currency = $this->getVendorCurrencyId($_orderData->virtuemart_vendor_id);

		$_orderData->virtuemart_paymentmethod_id = JRequest::getInt('virtuemart_paymentmethod_id');
		$_orderData->virtuemart_shipmentmethod_id = JRequest::getInt('virtuemart_shipmentmethod_id');

		$_orderData->customer_note = '';
		$_orderData->ip_address = $_SERVER['REMOTE_ADDR'];

		$_orderData->order_number ='';
		JPluginHelper::importPlugin('vmshopper');
		$dispatcher = JDispatcher::getInstance();
		$_orderData->order_number = $this->generateOrderNumber($usrid,4,$_orderData->virtuemart_vendor_id);
		$_orderData->order_pass = 'p_'.substr( md5((string)time().rand(1,1000).$_orderData->order_number ), 0, 5);

		$orderTable =  $this->getTable('orders');
		$orderTable -> bindChecknStore($_orderData);
		$errors = $orderTable->getErrors();
		foreach($errors as $error){
			vmError($error);
		}

		$db = JFactory::getDBO();
		$_orderID = $db->insertid();

		$_usr  = JFactory::getUser();
		if (!$this->_writeUserInfo($_orderID, $_usr, array())) {
			vmError($error);
		}

		$orderModel = VmModel::getModel('orders');
		$order= $orderModel->getOrder($_orderID);

		$dispatcher = JDispatcher::getInstance();

		JPluginHelper::importPlugin('vmshipment');
		JPluginHelper::importPlugin('vmcustom');
		JPluginHelper::importPlugin('vmpayment');
		if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$returnValues = $dispatcher->trigger('plgVmConfirmedOrder', array($cart, $order));

		return $_orderID;
	}

	/** Rename Invoice  (when an order is deleted)
	 *
	 * @author Valérie Isaksen
	 * @param $order_id Id of the order
	 * @return boolean true if deleted successful, false if there was a problem
	 */
	function renameInvoice($order_id ) {
		$db = JFactory::getDBO();

		$q = 'SELECT * FROM `#__virtuemart_invoices` WHERE `virtuemart_order_id`= "'.$order_id.'" ';

		$db->setQuery($q);
		$data = $db->loadAssoc();
		if(!$data or   empty($data['invoice_number']) ){
			return true;
		}

		// rename invoice pdf file
		$invoice_prefix='vminvoice_';
		$path = shopFunctions::getInvoicePath(VmConfig::get('forSale_path',0));
		$invoice_name_src = $path.DS.$invoice_prefix.$data['invoice_number'].'.pdf';

		if(!file_exists($invoice_name_src)){
			// may be it was already deleted when changing order items
			$data['invoice_number'] = "";
		} else {
			$date = date("Ymd");
			$data['invoice_number'] = $data['invoice_number'].'_'.$date;
			$invoice_name_dst = $path.DS.$data['invoice_number'].'.pdf';

			if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');
			if (!JFile::move($invoice_name_src, $invoice_name_dst)) {
				vmError ('Could not rename Invoice '.$invoice_name_src.'to '. $invoice_name_dst );
			}
		}

		$table = $this->getTable('invoices');
		$table->bindChecknStore($data);

		return true;


	}
	/** Delete Invoice when an item is updated
	 *
	 * @author Valérie Isaksen
	 * @param $order_id Id of the order
	 * @return boolean true if deleted successful, false if there was a problem
	 */
	function deleteInvoice($order_id ) {
		$db = JFactory::getDBO();

		$q = 'SELECT * FROM `#__virtuemart_invoices` WHERE `virtuemart_order_id`= "'.$order_id.'" ';

		$db->setQuery($q);
		$data = $db->loadAssoc();
		if(!$data or   empty($data['invoice_number']) ){
			return true;
		}

		// rename invoice pdf file
		$invoice_prefix='vminvoice_';
		$path = shopFunctions::getInvoicePath(VmConfig::get('forSale_path',0));
		$invoice_name_src = $path.DS.$invoice_prefix.$data['invoice_number'].'.pdf';

		if(!file_exists($invoice_name_src)){
			// was already deleted by a previoous change
			return;
		}

		if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');
		if (!JFile::delete($invoice_name_src )) {
			vmError ('Could not delete Invoice '.$invoice_name_src  );
		}

	}

}

// No closing tag
