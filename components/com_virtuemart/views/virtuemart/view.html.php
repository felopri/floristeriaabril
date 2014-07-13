<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 6564 2012-10-19 11:45:27Z kkmediaproduction $
 */

# Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

# Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');

/**
 * Default HTML View class for the VirtueMart Component
 * @todo Find out how to use the front-end models instead of the backend models
 */
class VirtueMartViewVirtueMart extends VmView {

	public function display($tpl = null) {

		$vendorId = JRequest::getInt('vendorid', 1);

		$vendorModel = VmModel::getModel('vendor');

		$vendorModel->setId(1);
		$vendor = $vendorModel->getVendor();

		if(!class_exists('shopFunctionsF'))require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		if (VmConfig::get ('enable_content_plugin', 0)) {
			shopFunctionsF::triggerContentPlugin($vendor, 'vendor','vendor_store_desc');
			shopFunctionsF::triggerContentPlugin($vendor, 'vendor','vendor_terms_of_service');
		}

		$this->assignRef('vendor',$vendor);

		$document = JFactory::getDocument();

		if(!VmConfig::get('shop_is_offline',0)){

			$categoryModel = VmModel::getModel('category');
			$productModel = VmModel::getModel('product');
			$ratingModel = VmModel::getModel('ratings');
			$productModel->withRating = $ratingModel->showRating();

			$products = array();
			$categoryId = JRequest::getInt('catid', 0);

			$categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);

			$categoryModel->addImages($categoryChildren,1);

			$this->assignRef('categories',	$categoryChildren);

			if(!class_exists('CurrencyDisplay'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance( );
			$this->assignRef('currency', $currency);
			
			$products_per_row = VmConfig::get('homepage_products_per_row',3);
			
			$featured_products_rows = VmConfig::get('featured_products_rows',1);
			$featured_products_count = $products_per_row * $featured_products_rows;

			if (!empty($featured_products_count) and VmConfig::get('show_featured', 1)) {
				$products['featured'] = $productModel->getProductListing('featured', $featured_products_count);
				$productModel->addImages($products['featured'],1);
			}
			
			$latest_products_rows = VmConfig::get('latest_products_rows');
			$latest_products_count = $products_per_row * $latest_products_rows;

			if (!empty($latest_products_count) and VmConfig::get('show_latest', 1)) {
				$products['latest']= $productModel->getProductListing('latest', $latest_products_count);
				$productModel->addImages($products['latest'],1);
			}

			$topTen_products_rows = VmConfig::get('topTen_products_rows');
			$topTen_products_count = $products_per_row * $topTen_products_rows;
			
			if (!empty($topTen_products_count) and VmConfig::get('show_topTen', 1)) {
				$products['topten']= $productModel->getProductListing('topten', $topTen_products_count);
				$productModel->addImages($products['topten'],1);
			}
			
			$recent_products_rows = VmConfig::get('recent_products_rows');
			$recent_products_count = $products_per_row * $recent_products_rows;
			$recent_products = $productModel->getProductListing('recent');
			
			if (!empty($recent_products_count) and VmConfig::get('show_recent', 1) and !empty($recent_products)) {
				$products['recent']= $productModel->getProductListing('recent', $recent_products_count);
				$productModel->addImages($products['recent'],1);
			}
			
			$this->assignRef('products', $products);

			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
			$this->assignRef('showBasePrice', $showBasePrice);

			//		$layoutName = VmConfig::get('vmlayout','default');

			$layout = VmConfig::get('vmlayout','default');
			$this->setLayout($layout);


			// Add feed links
			if ($products  && (VmConfig::get('feed_featured_published', 0)==1 or VmConfig::get('feed_topten_published', 0)==1 or VmConfig::get('feed_latest_published', 0)==1)) {
				$link = '&format=feed&limitstart=';
				$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
				$document->addHeadLink(JRoute::_($link . '&type=rss', FALSE), 'alternate', 'rel', $attribs);
				$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
				$document->addHeadLink(JRoute::_($link . '&type=atom', FALSE), 'alternate', 'rel', $attribs);
			}
		} else {
			$this->setLayout('off_line');
		}


		$error = JRequest::getInt('error',0);

		//Todo this may not work everytime as expected, because the error must be set in the redirect links.
		if(!empty($error)){
			$document->setTitle(JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND').JText::sprintf('COM_VIRTUEMART_HOME',$vendor->vendor_store_name));
		} else {

			if(empty($vendor->customtitle)){
				$app = JFactory::getApplication();
				$menus = $app->getMenu();
				$menu = $menus->getActive();

				if ($menu){
					$menuTitle = $menu->params->get('page_title');
					if(empty($menuTitle)) {
						$menuTitle = JText::sprintf('COM_VIRTUEMART_HOME',$vendor->vendor_store_name);
					}
					$document->setTitle($menuTitle);
				} else {
					$title = JText::sprintf('COM_VIRTUEMART_HOME',$vendor->vendor_store_name);
					$document->setTitle($title);
				}
			} else {
				$document->setTitle($vendor->customtitle);
			}


			if(!empty($vendor->metadesc)) $document->setMetaData('description',$vendor->metadesc);
			if(!empty($vendor->metakey)) $document->setMetaData('keywords',$vendor->metakey);
			if(!empty($vendor->metarobot)) $document->setMetaData('robots',$vendor->metarobot);
			if(!empty($vendor->metaauthor)) $document->setMetaData('author',$vendor->metaauthor);

		}

		$template = VmConfig::get('vmtemplate','default');
		shopFunctionsF::setTemplate($template);

		parent::display($tpl);

	}
}
# pure php no closing tag