<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_update.php 3274 2011-05-17 20:43:48Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);
if(!VmConfig::get('dangeroustools', false)){
	$uri = JFactory::getURI();
	$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
	?>

	<div class="vmquote" style="text-align:left;margin-left:20px;">
		<span style="font-weight:bold;color:green;"> <?php echo JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_ENABLED_JS',JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link) ?></span>
	</div>

<?php
}

$link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installComplete&'.JUtility::getToken().'=1&token='.JUtility::getToken() .'&install=1' ); ?>

<div id="cpanel">
	<table  >

<div class="icon"><a onclick="javascript:confirmation( '<?php echo $link; ?>');">
		<span class="vmicon48"></span>
		<br /><?php echo JText::_('COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_FRESH'); ?>

	</a></div>

<?php	$link=JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installCompleteSamples&'.JUtility::getToken().'=1&token='.JUtility::getToken() .'&install=1'); ?>
	<div class="icon"><a onclick="javascript:confirmation('<?php echo $link; ?>');">
			<span class="vmicon48"></span>
			<br /><?php echo JText::_('COM_VIRTUEMART_DELETES_ALL_VM_TABLES_AND_SAMPLE'); ?>

		</a></div>
	</table>
<?php
AdminUIHelper::endAdminArea();
?>
<script type="text/javascript">
<!--
function confirmation(destnUrl) {
	window.location = destnUrl;
}
//-->
</script>