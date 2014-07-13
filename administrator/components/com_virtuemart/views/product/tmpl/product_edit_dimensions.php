<?php
/**
*
* Set the product dimensions
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_dimensions.php 6379 2012-08-25 17:09:39Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
   <table class="adminform">
   <tbody>

    <tr class="row1">
		<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_LENGTH') ?>
      </th>
      <td width="79%">
        <input type="text" class="inputbox"  name="product_length" value="<?php echo $this->product->product_length; ?>" size="15" maxlength="15" />   <?php echo " ".$this->lists['product_lwh_uom'];?>
      </td>
    </tr>
    <tr class="row0">
		<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_WIDTH') ?></th>
      <td>
        <input type="text" class="inputbox"  name="product_width" value="<?php echo $this->product->product_width; ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row1">
		<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_HEIGHT') ?></th>
      <td>
        <input type="text" class="inputbox"  name="product_height" value="<?php echo $this->product->product_height; ?>" size="15" maxlength="15" />
      </td>
    </tr>
   
    <tr class="row0">
		<th><?php echo JText::_('COM_VIRTUEMART_PRODUCT_WEIGHT') ?></th>
       <td>
        <input type="text" class="inputbox"  name="product_weight" size="15" maxlength="15" value="<?php echo $this->product->product_weight; ?>" />
        <?php echo " ".$this->lists['product_weight_uom'];?>
      </td>
    </tr>


    <!-- Changed Packaging - Begin -->
   
    <tr class="row0">
		<th>
        <span class="hasTip" title="<?php echo JText::sprintf('COM_VIRTUEMART_PRODUCT_PACKAGING_DESCRIPTION',JText::_('COM_VIRTUEMART_UNIT_NAME_L'),JText::_('COM_VIRTUEMART_PRODUCT_UNIT'),JText::_('COM_VIRTUEMART_UNIT_NAME_100ML')); ?>">
        <?php echo JText::_('COM_VIRTUEMART_PRODUCT_PACKAGING') ?>
         </span>
		</th>
      <td>
        <input type="text" class="inputbox"  name="product_packaging" value="<?php echo $this->product->product_packaging; ?>" size="15" maxlength="15" />&nbsp;
		<?php echo " ".$this->lists['product_iso_uom'];?>
      </td>
    </tr>
    <tr class="row1">
		<th>
                <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_BOX_DESCRIPTION'); ?>">
                <?php echo JText::_('COM_VIRTUEMART_PRODUCT_BOX') ?>
                </span></th>
      <td>
        <input type="text" class="inputbox"  name="product_box" value="<?php echo $this->product->product_box; ?>" size="15" maxlength="15"/>&nbsp;
      </td>
    </tr>
    <!-- Changed Packaging - End -->
</tbody>
</table>
