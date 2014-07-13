<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* Currency Selector Module
*
* NOTE: THIS MODULE REQUIRES THE VIRTUEMART COMPONENT!
/*
* @version $Id: mod_virtuemart_currencies.php 6555 2012-10-17 15:49:43Z alatak $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2011 virtuemart team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/


/***********
 *
 * Prices in the orders are saved in the shop currency; these fields are required
 * to show the prices to the user in a later stadium.
  */

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');

VmConfig::loadConfig();
VmConfig::loadJLang('mod_virtuemart_currencies', true);
$mainframe = Jfactory::getApplication();
$vendorId = JRequest::getInt('vendorid', 1);
$text_before = $params->get( 'text_before', '');
/* table vm_vendor */
$db = JFactory::getDBO();
// the select list should include the vendor currency which is the currency in which the product prices are displayed by default.
$q  = 'SELECT CONCAT(`vendor_accepted_currencies`, ",",`vendor_currency`) AS all_currencies, `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`='.$vendorId;
$db->setQuery($q);
$vendor_currency = $db->loadAssoc();


$virtuemart_currency_id = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',JRequest::getInt('virtuemart_currency_id', $vendor_currency['vendor_currency']) );

//if (!$vendor_currency['vendor_accepted_currencies']) return;
//$currency_codes = explode(',' , $currencies->vendor_accepted_currencies );

/* table vm_currency */
//$q = 'SELECT `virtuemart_currency_id`,CONCAT_WS(" ",`currency_name`,`currency_exchange_rate`,`currency_symbol`) as currency_txt FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` IN ('.$currency_codes.') and enabled =1 ORDER BY `currency_name`';
$q = 'SELECT `virtuemart_currency_id`,CONCAT_WS(" ",`currency_name`,`currency_symbol`) as currency_txt
FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` IN ('.$vendor_currency['all_currencies'].') and (`virtuemart_vendor_id` = "'.$vendorId.'" OR `shared`="1") AND published = "1" ORDER BY `ordering`,`currency_name`';
$db->setQuery($q);
$currencies = $db->loadObjectList();
/* load the template */
require(JModuleHelper::getLayoutPath('mod_virtuemart_currencies'));
    ?>
