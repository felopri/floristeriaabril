<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 6068 2012-06-06 14:59:42Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewMedia extends VmView {

	function display($tpl = null) {

		if (!class_exists('VmHTML'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);

		// TODO add icon for media view
		$this->SetViewTitle();

		$model = VmModel::getModel('media');
		$perms = Permissions::getInstance();
		$this->assignRef('perms', $perms);

		$layoutName = JRequest::getWord('layout', 'default');
		if ($layoutName == 'edit') {

			$media = $model->getFile();
			$this->assignRef('media',	$media);

			$isNew = ($media->virtuemart_media_id < 1);

			$this->addStandardEditViewCommands();

        }
        else {
			$virtuemart_product_id = JRequest::getVar('virtuemart_product_id',array(),'', 'array');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
        	$cat_id = JRequest::getInt('virtuemart_category_id',0);

			JToolBarHelper::customX('synchronizeMedia', 'new', 'new', JText::_('COM_VIRTUEMART_TOOLS_SYNC_MEDIA_FILES'),false);
			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model,null,null,'searchMedia');
			$options = array( '' => JText::_('COM_VIRTUEMART_LIST_ALL_TYPES'),
				'product' => JText::_('COM_VIRTUEMART_PRODUCT'),
				'category' => JText::_('COM_VIRTUEMART_CATEGORY'),
				'manufacturer' => JText::_('COM_VIRTUEMART_MANUFACTURER'),
				'vendor' => JText::_('COM_VIRTUEMART_VENDOR')
				);
			$this->lists['search_type'] = VmHTML::selectList('search_type', JRequest::getVar('search_type'),$options,1,'','onchange="this.form.submit();"');

			$options = array( '' => JText::_('COM_VIRTUEMART_LIST_ALL_ROLES'),
				'file_is_displayable' => JText::_('COM_VIRTUEMART_FORM_MEDIA_DISPLAYABLE'),
				'file_is_downloadable' => JText::_('COM_VIRTUEMART_FORM_MEDIA_DOWNLOADABLE'),
				'file_is_forSale' => JText::_('COM_VIRTUEMART_FORM_MEDIA_SET_FORSALE'),
				);
			$this->lists['search_role'] = VmHTML::selectList('search_role', JRequest::getVar('search_role'),$options,1,'','onchange="this.form.submit();"');

			$files = $model->getFiles(false,false,$virtuemart_product_id,$cat_id);
			$this->assignRef('files',	$files);

			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

		}

		parent::display($tpl);
	}

}
// pure php no closing tag