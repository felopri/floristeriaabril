<?php
/**
 * Admin form for the checkout configuration settings
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_checkout.php 7457 2013-12-09 16:56:51Z Milbo $
 */
defined('_JEXEC') or die('Restricted access');
$js = '
		jQuery(document).ready(function( $ ) {
				if ( $("#oncheckout_opc").is(\':checked\') ) {
					$(".not_opc_param").hide();
				} else {
					$(".not_opc_param").show();
				}
			 $("#oncheckout_opc").click(function() {
				if ( $(".not_opc_param").is(\':checked\') ) {
					$(".not_opc_param").hide();
				} else {
					$(".not_opc_param").show();
				}
			});
		});
	';
$document = JFactory::getDocument();
$document->addScriptDeclaration($js);

/*
 <table width="100%">
<tr>
<td valign="top" width="50%"> */
?>
<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CHECKOUT_SETTINGS'); ?></legend>
	<table class="admintable">
    	<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ADDTOCART_POPUP_EXPLAIN'); ?>">
					<label for="addtocart_popup">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ADDTOCART_POPUP'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('addtocart_popup', VmConfig::get('addtocart_popup',1)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_POPUP_REL_TIP'); ?>">
					<label for="popup_rel">
						<?php echo JText::_('COM_VIRTUEMART_CFG_POPUP_REL'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('popup_rel', VmConfig::get('popup_rel',1)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CHECKOUT_OPC_TIP'); ?>">
					<label for="oncheckout_opc">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CHECKOUT_OPC'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_opc', VmConfig::get('oncheckout_opc',1)); ?>
			</td>
		</tr>
		<div id="not_opc_param">
		<tr class="not_opc_param">
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_STEPS_TIP'); ?>">
					<label for="oncheckout_show_steps">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_STEPS'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_show_steps', VmConfig::get('oncheckout_show_steps',0)); ?>
			</td>
		</tr>
		<tr  class="not_opc_param">
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_SHIPMENT_EXPLAIN'); ?>">
					<label for="automatic_shipment">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_SHIPMENT'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('automatic_shipment', VmConfig::get('automatic_shipment',1)); ?>
			</td>
		</tr>
		<tr  class="not_opc_param">
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_PAYMENT_EXPLAIN'); ?>">
					<label for="automatic_payment">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_PAYMENT'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('automatic_payment', VmConfig::get('automatic_payment',1)); ?>
			</td>
		</tr>
		</div>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AGREE_TERMS_ONORDER_EXPLAIN'); ?>">
					<label for="agree_to_tos_onorder">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_AGREE_TERMS_ONORDER'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('agree_to_tos_onorder', VmConfig::get('agree_to_tos_onorder',1)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_LEGALINFO_TIP'); ?>">
					<label for="oncheckout_show_legal_info">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_LEGALINFO'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_show_legal_info', VmConfig::get('oncheckout_show_legal_info',1)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER_TIP'); ?>">
					<label for="oncheckout_show_register">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_show_register', VmConfig::get('oncheckout_show_register',1)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_ONLY_REGISTERED_TIP'); ?>">
					<label for="oncheckout_only_registered">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_ONLY_REGISTERED'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_only_registered', VmConfig::get('oncheckout_only_registered',0)); ?>
			</td>
		</tr>

		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_PRODUCTIMAGES_TIP'); ?>">
					<label for="oncheckout_show_images">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_PRODUCTIMAGES'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_show_images', VmConfig::get('oncheckout_show_images',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_STATUS_PDF_INVOICES_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_STATUS_PDF_INVOICES'); ?>
				</span>
			</td>
			<td>
				<?php echo $this->orderStatusModel->renderOSList(VmConfig::get('inv_os',array('C')),'inv_os',TRUE); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_OSTATUS_EMAILS_SHOPPER_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_CFG_OSTATUS_EMAILS_SHOPPER'); ?>
				 </span>
			</td>
			<td>
				<?php echo $this->orderStatusModel->renderOSList(VmConfig::get('email_os_s',array('U','C','S','R','X')),'email_os_s',TRUE); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_OSTATUS_EMAILS_VENDOR_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_CFG_OSTATUS_EMAILS_VENDOR'); ?>
				</span>
			</td>
			<td>
				<?php echo $this->orderStatusModel->renderOSList(VmConfig::get('email_os_v',array('U','C','R','X')),'email_os_v',TRUE); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LANGFIX_EXPLAIN'); ?>">
					<label for="addtocart_popup">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_LANGFIX'); ?>
					</label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('vmlang_js', VmConfig::get('vmlang_js',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_CHANGE_SHOPPER_TIP'); ?>">
					<label for="oncheckout_change_shopper">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_ONCHECKOUT_CHANGE_SHOPPER'); ?>
					</label>
                </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('oncheckout_change_shopper', VmConfig::get('oncheckout_change_shopper',0)); ?>
			</td>
		</tr>
<?php
		$_delivery_date_options = array(
			'm' => JText::_('COM_VIRTUEMART_DELDATE_INV')
		, 'osP' => JText::_('COM_VIRTUEMART_ORDER_STATUS_PENDING')
		, 'osU' => JText::_('COM_VIRTUEMART_ORDER_STATUS_CONFIRMED_BY_SHOPPER')
		, 'osC' => JText::_('COM_VIRTUEMART_ORDER_STATUS_CONFIRMED')
		, 'osS' => JText::_('COM_VIRTUEMART_ORDER_STATUS_SHIPPED')
		, 'osR' => JText::_('COM_VIRTUEMART_ORDER_STATUS_REFUNDED')
		, 'osC' => JText::_('COM_VIRTUEMART_ORDER_STATUS_CANCELLED')
		);
		echo VmHTML::row('selectList','COM_VIRTUEMART_CFG_DELDATE_INV','del_date_type', VmConfig::get('del_date_type','m'), $_delivery_date_options);
		?>

	</table>
</fieldset>


<?php /*	</td>
 <td valign="top">

<fieldset>
<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES') ?></legend>
<table class="admintable">
<tr>
<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES_LBL_TIP'); ?>">
<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TITLES_LBL') ?>
</span>
</td>
<td><fieldset class="checkbox">
<?php echo $this->titlesFields ; ?>
</fieldset></td>
</tr>
</table>
</td>
</tr>
</table> */ ?>