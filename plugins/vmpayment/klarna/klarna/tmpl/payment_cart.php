<?php
defined('_JEXEC') or die();
/**
 * @version $Id: payment_cart.php 6501 2012-10-04 13:16:05Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
?>

<?php
$logo = '<img src="' .  $viewData['logo'] . '"/>';
?>


<div class="klarna_info">
    <span style="">
	<a href="http://www.klarna.com/"><?php echo $logo ?></a><br /><?php echo $viewData['text'] ?>
    </span>
</div>

<div class="clear"></div>
<span class="payment_name"><?php echo $viewData['payment_name'] ?> </span>
<?php
if (!empty($description)) {
?>
 <span class="payment_description"><?php echo $viewData['payment_description'] ?> . '</span>
	 <?php
}

?>

