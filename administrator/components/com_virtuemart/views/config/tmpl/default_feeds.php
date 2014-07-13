<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_feeds.php 3859 2011-08-13 15:11:08Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<br />
<fieldset>
    <legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_ENABLE_TIP'); ?>">
		<?php echo JHTML::tooltip(JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_ENABLE_TIP'), JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_ENABLE_TIP')); ?>
		<label for="feed_published"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_ENABLE') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_published', $this->config->get('feed_published')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_CACHE_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_CACHE') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_cache', $this->config->get('feed_cache')); ?>
		<br />
		<input type="text" size="10" value="<?php echo $this->config->get('feed_cachetime', '1800'); ?>" name="feed_cachetime" id="feed_cachetime" />
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_CACHETIME') ?>
	    </td>
	</tr>

	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_TITLE_CATEGORIES_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_TITLE') ?>
		</span>
		</td>
	    <td>
		<input type="text" size="40" value="<?php echo $this->config->get('feed_title'); ?>" name="feed_title" id="feed_title" /><br />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_TITLE_CATEGORIES_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_TITLE_CATEGORIES') ?>
		</span>
	    </td>
	    <td>
		<input type="text" size="40" value="<?php echo $this->config->get('feed_title_categories'); ?>" name="feed_title_categories" id="feed_title_categories" /><br />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWIMAGES_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWIMAGES') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_show_images', $this->config->get('feed_show_images')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWPRICES_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWPRICES') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_show_prices', $this->config->get('feed_show_prices')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWDESC_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_SHOWDESC') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_show_description', $this->config->get('feed_show_description')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_DESCRIPTION_TYPE_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_DESCRIPTION_TYPE') ?>
		</span>
	    </td>
	    <td>
		<?php
		$options = array();
		$options[] = JHTML::_('select.option', 'product_s_desc', JText::_('COM_VIRTUEMART_PRODUCT_FORM_S_DESC'));
		$options[] = JHTML::_('select.option', 'product_desc', JText::_('COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION'));
		echo JHTML::_('Select.genericlist', $options, 'feed_description_type', 'size=1', 'value', 'text', $this->config->get('feed_description_type'));
		?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAX_TEXT_LENGTH_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_LIMITTEXT') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('feed_limittext', $this->config->get('feed_limittext')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAX_TEXT_LENGTH_TIP'); ?>">
		<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEED_MAX_TEXT_LENGTH') ?>
		</span>
	    </td>
	    <td>
		<input type="text" size="10" value="<?php echo $this->config->get('feed_max_text_length', '500'); ?>" name="feed_max_text_length" id="feed_max_text_length" />
	    </td>
	</tr>
    </table>
</fieldset>