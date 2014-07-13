<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_shop.php 7453 2013-12-08 18:37:54Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE','shop_is_offline',VmConfig::get('shop_is_offline',0));
		?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_OFFLINE_MSG'); ?>
			</td>
			<td>
				<textarea rows="6" cols="50" name="offline_message"
				          style="text-align: left;"><?php echo VmConfig::get('offline_message', 'Our Shop is currently down for maintenance. Please check back again soon.'); ?></textarea>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_USE_ONLY_AS_CATALOGUE','use_as_catalog',VmConfig::get('use_as_catalog',0));
		?>
		<tr>
			<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_CURRENCY_MODULE_TIP'); ?>">
            		<?php echo JText::_('COM_VIRTUEMART_CFG_CURRENCY_MODULE'); ?>
            	</span>
			</td>
			<td>
				<?php echo JHTML::_('Select.genericlist', $this->currConverterList, 'currency_converter_module', 'size=1', 'value', 'text', VmConfig::get('currency_converter_module', 'convertECB.php')); ?>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ENABLE_CONTENT_PLUGIN','enable_content_plugin',VmConfig::get('enable_content_plugin',0));
		?>

		<?php    /*
     		echo VmHTML::row('input','COM_VIRTUEMART_ADMIN_CFG_DATEFORMAT','dateformat',VmConfig::get('dateformat'));
    		*/ ?>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_SSL','useSSL',VmConfig::get('useSSL',0));
		?>
	</table>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_LANGUAGES'); ?></legend>
	<table class="admintable">
		<tr>
			<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE_EXPLAIN'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MULTILANGUE'); ?>
					</span>
			</td>
			<td>
				<?php echo $this->activeLanguages; ?>
			</td>
			<td>
				<?php echo JText::sprintf('COM_VIRTUEMART_MORE_LANGUAGES','<a href="http://virtuemart.net/community/translations" target="_blank" >Translations</a>'); ?>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ENABLE_ENGLISH','enableEnglish',VmConfig::get('enableEnglish',1));
		?>

	</table>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_EMAILS'); ?></legend>
	<table class="admintable">
		<tr>
			<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_EXPLAIN'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT'); ?>
					</span>
			</td>
			<td>
				<select name="order_mail_html" id="order_mail_html">
					<option value="0" <?php if (VmConfig::get('order_mail_html') == '0') {
						echo 'selected="selected"';
					} ?>>
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_TEXT'); ?>
					</option>
					<option value="1" <?php if (VmConfig::get('order_mail_html') == '1') {
						echo 'selected="selected"';
					} ?>>
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FORMAT_HTML'); ?>
					</option>
				</select>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_MAIL_USEVENDOR','useVendorEmail',VmConfig::get('useVendorEmail',1));
		?>

		<?php /*?>		<!-- NOT YET -->
	    <!--tr>
		    <td class="key">
			<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FROM_RECIPIENT_EXPLAIN'); ?>">
			<label for="mail_from_recipient"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FROM_RECIPIENT') ?></span>
			    </span>
		    </td>
		    <td>
			    <?php echo VmHTML::checkbox('mail_from_recipient', VmConfig::get('mail_from_recipient',0)); ?>
		    </td>
	    </tr>
	    <tr>
		    <td class="key">
			<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FROM_SETSENDER_EXPLAIN'); ?>">
			<label for="mail_from_setsender"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIL_FROM_SETSENDER') ?></span>
			    </span>
		    </td>
		    <td>
			    <?php echo VmHTML::checkbox('mail_from_setsender', VmConfig::get('mail_from_setsender',0)); ?>
		    </td>
	    </tr --><?php */?>

	</table>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOP_ADVANCED'); ?></legend>
	<table class="admintable">
		<tr>
			<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_EXPLAIN'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG'); ?>
					</span>
			</td>
			<td>
				<?php
				$options = array(
					'none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_NONE'),
					'admin' => JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ADMIN'),
					'all' => JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
				);
				echo VmHTML::radioList('debug_enable', VmConfig::get('debug_enable', 'none'), $options);
				?>
			</td>
		</tr>
		<?php
			echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS','dangeroustools',VmConfig::get('dangeroustools',0));
		?>

		<tr>
			<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_EXPLAIN'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX'); ?>
					</span>
			</td>
			<td>
				<?php
				$options = array(
					'none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_NONE'),
					'admin' => JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_MULTIX_ADMIN')
					// 				'all'	=> JText::_('COM_VIRTUEMART_ADMIN_CFG_ENABLE_DEBUG_ALL')
				);
				echo VmHTML::radioList('multix', VmConfig::get('multix', 'none'), $options);
				?>
			</td>
		</tr>
	</table>
</fieldset>
