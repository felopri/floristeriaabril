<?php
/**
*
* Currency table
*
* @package	VirtueMart
* @subpackage Currency
* @author Seyi Awofadeju
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: currencies.php 3256 2011-05-15 20:04:08Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * WaitingUsers table class
 * The class is is used to manage the currencies in the shop.
 *
 * @package		VirtueMart
 * @author Seyi Awofadeju
 */
class TableWaitingUsers extends VmTable {

	var $virtuemart_waitinguser_id	= 0;
	var $virtuemart_product_id		= 0;
	var $virtuemart_user_id        	= 0;
	var $notify_email				= '';
	var $notified         			= 0;
	var $notify_date 				= '';
    var $ordering					= 0;

	/**
	 * @author Max Milbers
	 * @param JDataBase $db
	 */
	function __construct(&$db) {
		parent::__construct('#__virtuemart_waitingusers', 'virtuemart_waitinguser_id', $db);
		$this->setLoggable();

	}

	function check() {
		if(empty($this->notify_email) || !filter_var($this->notify_email, FILTER_VALIDATE_EMAIL)) {
			vmError(JText::_('COM_VIRTUEMART_ENTER_A_VALID_EMAIL_ADDRESS'),JText::_('COM_VIRTUEMART_ENTER_A_VALID_EMAIL_ADDRESS'));
			return false;
		}
		return parent::check();
	}

}
// pure php no closing tag
