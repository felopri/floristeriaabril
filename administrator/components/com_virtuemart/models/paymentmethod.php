<?php
/**
*
* Model paymentmethod
*
* @package	VirtueMart
* @subpackage  Payment
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: paymentmethod.php 6474 2012-09-19 18:22:26Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

class VirtueMartModelPaymentmethod extends VmModel{

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('paymentmethods');
		$this->_selectedOrdering = 'ordering';
	}

	/**
	 * Gets the virtuemart_paymentmethod_id with a plugin and vendorId
	 *
	 * @author Max Milbers
	 */
	 public function getIdbyCodeAndVendorId($jpluginId,$vendorId=1){
	 	if(!$jpluginId) return 0;
	 	$q = 'SELECT `virtuemart_paymentmethod_id` FROM #__virtuemart_paymentmethods WHERE `payment_jplugin_id` = "'.$jpluginId.'" AND `virtuemart_vendor_id` = "'.$vendorId.'" ';
		$this->_db->setQuery($q);
		return $this->_db->loadResult();
	 }

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Max Milbers
     */
	public function getPayment(){

  		if (empty($this->_data)) {
   			$this->_data = $this->getTable('paymentmethods');
   			$this->_data->load((int)$this->_id);
  		}

  		if(empty($this->_data->virtuemart_vendor_id)){
  		   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
   			$this->_data->virtuemart_vendor_id = VirtueMartModelVendor::getLoggedVendor();
  		}

  		if($this->_data->payment_jplugin_id){
  			JPluginHelper::importPlugin('vmpayment');
  			$dispatcher = JDispatcher::getInstance();
  			$retValue = $dispatcher->trigger('plgVmDeclarePluginParamsPayment',array($this->_data->payment_element,$this->_data->payment_jplugin_id,&$this->_data));
		}
  		if(!empty($this->_id)){

			/* Add the paymentmethod shoppergroups */
			$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_paymentmethod_shoppergroups WHERE `virtuemart_paymentmethod_id` = "'.$this->_id.'"';
			$this->_db->setQuery($q);
			$this->_data->virtuemart_shoppergroup_ids = $this->_db->loadResultArray();

			if (JVM_VERSION===1) {
				$table = '#__plugins';
				$ext_id = 'id';
			} else {
				$table = '#__extensions';
				$ext_id = 'extension_id';
			}
			$q = 'SELECT `params` FROM `' . $table . '` WHERE `' . $ext_id . '` = "'.$this->_data->payment_jplugin_id.'"';
			$this->_db->setQuery($q);

			$this->_data->param = $this->_db->loadResult();

  		} else {
  			$this->_data->virtuemart_shoppergroup_ids = '';
  			$this->_data->param = '';
  		}


  		return $this->_data;
	}

	/**
	 * Retireve a list of calculation rules from the database.
	 *
     * @author Max Milbers
     * @param string $onlyPuiblished True to only retreive the publish Calculation rules, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of calculation rule objects
	 */
	public function getPayments($onlyPublished=false, $noLimit=false) {
		$where = array();
		if ($onlyPublished) {
			$where[] = ' `#__virtuemart_paymentmethods`.`published` = 1';
		}

		$whereString = '';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;

		$select = ' * FROM `#__virtuemart_paymentmethods_'.VMLANG.'` as l ';
		$joinedTables = ' JOIN `#__virtuemart_paymentmethods`   USING (`virtuemart_paymentmethod_id`) ';
		$this->_data =$this->exeSortSearchListQuery(0,$select,$joinedTables,$whereString,' ',$this->_getOrdering() );

			//$this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_paymentmethods`',$whereString,'',$this->_getOrdering('ordering'));

		if(isset($this->_data)){

			if(!class_exists('shopfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
			foreach ($this->_data as $data){
				/* Add the paymentmethod shoppergroups */
				$q = 'SELECT `virtuemart_shoppergroup_id` FROM #__virtuemart_paymentmethod_shoppergroups WHERE `virtuemart_paymentmethod_id` = "'.$data->virtuemart_paymentmethod_id.'"';
				$this->_db->setQuery($q);
				$data->virtuemart_shoppergroup_ids = $this->_db->loadResultArray();


				/* Write the first 5 shoppergroups in the list */
				$data->paymShoppersList = shopfunctions::renderGuiList('virtuemart_shoppergroup_id','#__virtuemart_paymentmethod_shoppergroups','virtuemart_paymentmethod_id',$data->virtuemart_paymentmethod_id,'shopper_group_name','#__virtuemart_shoppergroups','virtuemart_shoppergroup_id','shoppergroup',4,0);

			}

		}
		return $this->_data;
	}


	/**
	 * Bind the post data to the paymentmethod tables and save it
     *
     * @author Max Milbers
     * @return boolean True is the save was successful, false otherwise.
	 */
    public function store(&$data)
	{

		if(!empty($data['params'])){
			foreach($data['params'] as $k=>$v){
				$data[$k] = $v;
			}
		}

	  	if(empty($data['virtuemart_vendor_id'])){
	  	   	if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
	   		$data['virtuemart_vendor_id'] = VirtueMartModelVendor::getLoggedVendor();
	  	}


		$table = $this->getTable('paymentmethods');

		if(isset($data['payment_jplugin_id'])){

			// missing string FIX, Bad way ?
			if (JVM_VERSION===1) {
				$tb = '#__plugins';
				$ext_id = 'id';
			} else {
				$tb = '#__extensions';
				$ext_id = 'extension_id';
			}
			$q = 'SELECT `element` FROM `' . $tb . '` WHERE `' . $ext_id . '` = "'.$data['payment_jplugin_id'].'"';
			$this->_db->setQuery($q);
			$data['payment_element'] = $this->_db->loadResult();

			$q = 'UPDATE `' . $tb . '` SET `enabled`= 1 WHERE `' . $ext_id . '` = "'.$data['payment_jplugin_id'].'"';
			$this->_db->setQuery($q);
			$this->_db->query();

// special case moneybookers
			if ( strpos($data['payment_element'] , "moneybookers"  ) !==false) {
				$q = 'UPDATE `#__extensions` SET `enabled`= 1 WHERE  `element` ="moneybookers"';
				$this->_db->setQuery($q);
				$this->_db->query();
			}

			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$retValue = $dispatcher->trigger('plgVmSetOnTablePluginParamsPayment',array( $data['payment_element'],$data['payment_jplugin_id'],&$table));

		}

		$table->bindChecknStore($data);
		$errors = $table->getErrors();
		foreach($errors as $error){
				vmError($error);
			}

		$xrefTable = $this->getTable('paymentmethod_shoppergroups');
		$xrefTable->bindChecknStore($data);
		$errors = $xrefTable->getErrors();
		foreach($errors as $error){
			vmError($error);
		}

		if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			//Add a hook here for other shipment methods, checking the data of the choosed plugin
			$dispatcher = JDispatcher::getInstance();
			$retValues = $dispatcher->trigger('plgVmOnStoreInstallPaymentPluginTable', array(  $data['payment_jplugin_id']));

		return $table->virtuemart_paymentmethod_id;
	}


	/**
     * Publish a field
     *
     * @author Max Milbers
     *
     */
/*	public function published( $row, $i, $variable = 'published' )
	{
		$imgY = 'tick.png';
		$imgX = 'publish_x.png';
		$img 	= $row->$variable ? $imgY : $imgX;
		$task 	= $row->$variable ? 'unpublish' : 'publish';
		$alt 	= $row->$variable ? JText::_('COM_VIRTUEMART_PUBLISHED') : JText::_('COM_VIRTUEMART_UNPUBLISHED');
		$action = $row->$variable ? JText::_('COM_VIRTUEMART_UNPUBLISH_ITEM') : JText::_('COM_VIRTUEMART_PUBLISH_ITEM');

		$href = '
		<a title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;
		return $href;
	}*/




	/**
	 * Due the new plugin system this should be obsolete
	 * function to render the payment plugin list
	 *
	 * @author Max Milbers
	 *
	 * @param radio list of creditcards
	 * @return html
	 */
	public function renderPaymentList($selectedPaym=0,$selecedCC=0){

		$payms = self::getPayments(false,true);
		$listHTML='';
		foreach($payms as $item){
			$checked='';
			if($item->virtuemart_paymentmethod_id==$selectedPaym){
				$checked='"checked"';
			}
			$listHTML .= '<input type="radio" name="virtuemart_paymentmethod_id" value="'.$item->virtuemart_paymentmethod_id.'" '.$checked.'>'.$item->payment_name.' <br />';
			$listHTML .= ' <br />';
		}

		return $listHTML;

	}



}
