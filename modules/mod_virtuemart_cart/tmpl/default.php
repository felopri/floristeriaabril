<?php // no direct access
defined('_JEXEC') or die('Restricted access');

//dump ($cart,'mod cart');
// Ajax is displayed in vm_cart_products
// ALL THE DISPLAY IS Done by Ajax using "hiddencontainer" ?>

<!-- Virtuemart 2 Ajax Card -->
<div class="vmCartModule <?php echo $params->get('moduleclass_sfx'); ?>" id="vmCartModule">
<?php
if ($show_product_list) {
	?>
	<div id="hiddencontainer" style=" display: none; ">
		<div class="container">
			<?php if ($show_price) { ?>
			  <div class="prices" style="float: right;"></div>
			<?php } ?>
			<div class="product_row">
				<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
			</div>

			<div class="product_attributes"></div>
		</div>
	</div>
	<div class="vm_cart_products">
		<div class="container">

		<?php
			foreach ($data->products as $product)
		{
			if ($show_price) { ?>
				  <div class="prices" style="float: right;"><?php echo  $product['prices'] ?></div>
				<?php } ?>
			<div class="product_row">
				<span class="quantity"><?php echo  $product['quantity'] ?></span>&nbsp;x&nbsp;<span class="product_name"><?php echo  $product['product_name'] ?></span>
			</div>
			<?php if ( !empty($product['product_attributes']) ) { ?>
				<div class="product_attributes"><?php echo $product['product_attributes'] ?></div>

			<?php }
		}
		?>
		</div>
	</div>
<?php } ?>

<div class="total" style="float: right;">
	<?php if ($data->totalProduct and $show_price) echo  $data->billTotal; ?>
</div>
<div class="total_products"><?php echo  $data->totalProductTxt ?></div>
<div class="show_cart">
	<?php if ($data->totalProduct and $show_price) echo  $data->cart_show; ?>
</div>
<div style="clear:both;"></div>

<noscript>
<?php echo JText::_('MOD_VIRTUEMART_CART_AJAX_CART_PLZ_JAVASCRIPT') ?>
</noscript>
</div>

