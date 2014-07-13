<?php

/*
* @author Skrill Holdings Ltd.
* @version $Id: moneybookers_did.php 6383 2012-08-27 16:53:06Z alatak $
* @package VirtueMart
* @subpackage payment
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
* 
* http://virtuemart.org
*/

defined('_JEXEC') or die('Restricted access');
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
if (!class_exists('plgVmpaymentMoneybookers'))
    require(JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'moneybookers' . DS . 'moneybookers.php');

class plgVmpaymentMoneybookers_Did extends plgVmpaymentMoneybookers
    {
    public static $_this = false;

    function __construct(& $subject, $config)
	{
        parent::__construct($subject, $config);
        
        $this->_loggable = true;
        $this->_debug = false;
        $this->_tablepkey = 'id'; //virtuemart_moneybookers_id';
        $this->_tableId = 'id'; //'virtuemart_moneybookers_id';
	}
    
    function plgVmConfirmedOrder($cart, $order, $payment_method = '')
        {
        parent::plgVmConfirmedOrder($cart, $order, "DID");
        }
    }
// No closing tag