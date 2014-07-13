<?php
/**
 *
 * Controller for the front end Orderviews
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
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
defined('_JEXEC') or die('Restricted access for invoices');
if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');
if(!class_exists('VmPdf'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmpdf.php');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerInvoice extends JController
{

	public function __construct()
	{
		parent::__construct();
		$this->useSSL = VmConfig::get('useSSL',0);
		$this->useXHTML = true;
		VmConfig::loadJLang('com_virtuemart_shoppers',TRUE);
		VmConfig::loadJLang('com_virtuemart_orders',TRUE);
	}

	/**
	 * Override of display to prevent caching
	 *
	 * @return  JController  A JController object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)  {
		$format = JRequest::getWord('format','html');
		$layout = JRequest::getWord('layout', 'invoice');

		if ($format != 'pdf') {
			$viewName='invoice';

			$view = $this->getView($viewName, $format);
			$view->headFooter = true;
			$view->display();
		} else {
			//PDF needs more RAM than usual
			VmConfig::ensureMemoryLimit(64);
			$viewName='invoice';
			$format="html";

			// Create the invoice PDF file on disk and send that back
			$orderDetails = $this->getOrderDetails();
			$fileLocation = $this->getInvoicePDF($orderDetails, 'invoice',$layout);
			$fileName = basename ($fileLocation);

			if (file_exists ($fileLocation)) {
				$maxSpeed = 200;
				$range = 0;
				$size = filesize ($fileLocation);
				$contentType = 'application/pdf';
				header ("Cache-Control: public");
				header ("Content-Transfer-Encoding: binary\n");
				header ('Content-Type: application/pdf');

				$contentDisposition = 'attachment';

				$agent = strtolower ($_SERVER['HTTP_USER_AGENT']);

				if (strpos ($agent, 'msie') !== FALSE) {
					$fileName = preg_replace ('/\./', '%2e', $fileName, substr_count ($fileName, '.') - 1);
				}

				header ("Content-Disposition: $contentDisposition; filename=\"$fileName\"");

				header ("Accept-Ranges: bytes");

				if (isset($_SERVER['HTTP_RANGE'])) {
					list($a, $range) = explode ("=", $_SERVER['HTTP_RANGE']);
					str_replace ($range, "-", $range);
					$size2 = $size - 1;
					$new_length = $size - $range;
					header ("HTTP/1.1 206 Partial Content");
					header ("Content-Length: $new_length");
					header ("Content-Range: bytes $range$size2/$size");
				}
				else {
					$size2 = $size - 1;
					header ("Content-Range: bytes 0-$size2/$size");
					header ("Content-Length: " . $size);
				}

				if ($size == 0) {
					die('Zero byte file! Aborting download');
				}

				//$contents = file_get_contents ($fileName);
				//echo $contents;

				//	set_magic_quotes_runtime(0);
				$fp = fopen ("$fileLocation", "rb");
				fseek ($fp, $range);

				while (!feof ($fp) and (connection_status () == 0)) {
					set_time_limit (0);
					print(fread ($fp, 1024 * $maxSpeed));
					flush ();
					ob_flush ();
					sleep (1);
				}
				fclose ($fp);

				JFactory::getApplication()->close();

			} else {
				// TODO: Error message
				// vmError("File $fileName not found!");
			}
		}
	}

	public function getOrderDetails() {
		$orderModel = VmModel::getModel('orders');
		$orderDetails = 0;
		// If the user is not logged in, we will check the order number and order pass
		if ($orderPass = JRequest::getString('order_pass',false) and $orderNumber = JRequest::getString('order_number',false)){
			$orderId = $orderModel->getOrderIdByOrderPass($orderNumber,$orderPass);
			if(empty($orderId)){
				vmDebug ('Invalid order_number/password '.JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS'));
				return 0;
			}
			$orderDetails = $orderModel->getOrder($orderId);
		}

		if($orderDetails==0) {

			$_currentUser = JFactory::getUser();
			$cuid = $_currentUser->get('id');

			// If the user is logged in, we will check if the order belongs to him
				$virtuemart_order_id = JRequest::getInt('virtuemart_order_id',0) ;
			if (!$virtuemart_order_id) {
				$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber(JRequest::getString('order_number'));
			}
			$orderDetails = $orderModel->getOrder($virtuemart_order_id);

			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if(!Permissions::getInstance()->check("admin")) {
				if(!empty($orderDetails['details']['BT']->virtuemart_user_id)){
					if ($orderDetails['details']['BT']->virtuemart_user_id != $cuid) {
						echo 'view '.JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
						return ;
					}
				}
			}
		}
		return $orderDetails;
	}


	public function samplePDF() {
		if(!class_exists('VmVendorPDF')){
			vmError('vmPdf: For the pdf, you must install the tcpdf library at '.JPATH_VM_LIBRARIES.DS.'tcpdf');
			return 0;
		}

		$pdf = new VmVendorPDF();
		$pdf->AddPage();
		$pdf->PrintContents(JText::_('COM_VIRTUEMART_PDF_SAMPLEPAGE'));
		$pdf->Output("vminvoice_sample.pdf", 'I');
		JFactory::getApplication()->close();
	}

	function getInvoicePDF($orderDetails = 0, $viewName='invoice', $layout='invoice', $format='html', $force = false){
// 		$force = true;

		$path = VmConfig::get('forSale_path',0);
		if(empty($path) ){
			vmError('No path set to store invoices');
			return false;
		} else {
			$path .= shopFunctions::getInvoiceFolderName().DS;
			if(!file_exists($path)){
				vmError('Path wrong to store invoices, folder invoices does not exist '.$path);
				return false;
			} else if(!is_writable( $path )){
				vmError('Cannot store pdf, directory not writeable '.$path);
				return false;
			}
		}

		$orderModel = VmModel::getModel('orders');
		$invoiceNumberDate=array();
		if (!  $orderModel->createInvoiceNumber($orderDetails['details']['BT'], $invoiceNumberDate)) {
		    return 0;
		}

		if(!empty($invoiceNumberDate[0])){
			$invoiceNumber = $invoiceNumberDate[0];
		} else {
			$invoiceNumber = FALSE;
		}

		if(!$invoiceNumber or empty($invoiceNumber)){
			vmError('Cant create pdf, createInvoiceNumber failed');
			return 0;
		}
		if (shopFunctions::InvoiceNumberReserved($invoiceNumber)) {
			return 0;
		}
		
		$path .= preg_replace('/[^A-Za-z0-9_\-\.]/', '_', 'vm'.$layout.'_'.$invoiceNumber.'.pdf');

		if(file_exists($path) and !$force){
			return $path;
		}

		//We come from the be, so we need to load the FE langauge
		$jlang =JFactory::getLanguage();
		$jlang->load('com_virtuemart', JPATH_SITE, 'en-GB', true);
		$jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
		$jlang->load('com_virtuemart', JPATH_SITE, null, true);

		$this->addViewPath( JPATH_VM_SITE.DS.'views' );
		$view = $this->getView($viewName, $format);

		$view->addTemplatePath( JPATH_VM_SITE.DS.'views'.DS.$viewName.DS.'tmpl' );
		$vmtemplate = VmConfig::get('vmtemplate',0);
		if(!empty($vmtemplate) and $vmtemplate=='default'){
			if(JVM_VERSION == 2){
				$q = 'SELECT `template` FROM `#__template_styles` WHERE `client_id`="0" AND `home`="1"';
			} else {
				$q = 'SELECT `template` FROM `#__templates_menu` WHERE `client_id`="0" AND `menuid`="0"';
			}
			$db = JFactory::getDbo();
			$db->setQuery($q);
			$templateName = $db->loadResult();
		} else {
			$templateName = shopFunctionsF::setTemplate($vmtemplate);
		}

		if(!empty($templateName)){
			$TemplateOverrideFolder = JPATH_SITE.DS."templates".DS.$templateName.DS."html".DS."com_virtuemart".DS."invoice";
			if(file_exists($TemplateOverrideFolder)){
				$view->addTemplatePath( $TemplateOverrideFolder);
			}
		}

		$view->invoiceNumber = $invoiceNumberDate[0];
		$view->invoiceDate = $invoiceNumberDate[1];

		$view->orderDetails = $orderDetails;
		$view->uselayout = $layout;
		$view->showHeaderFooter = false;

		$vendorModel = VmModel::getModel('vendor');
		$virtuemart_vendor_id = 1;	//We could set this automatically by the vendorId stored in the order.
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		
		$metadata = array (
			'title' => JText::sprintf('COM_VIRTUEMART_INVOICE_TITLE', 
				$vendor->vendor_store_name, $view->invoiceNumber, 
				$orderDetails['details']['BT']->order_number),
			'keywords' => JText::_('COM_VIRTUEMART_INVOICE_CREATOR'));

		return VmPdf::createVmPdf($view, $path, 'F', $metadata);
	}
}




// No closing tag
