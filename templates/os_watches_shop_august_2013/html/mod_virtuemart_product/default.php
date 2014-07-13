<?php // no direct access
defined ('_JEXEC') or die('Restricted access');
$col = 1;
$pwidth = ' boxwidth' . floor (100 / $products_per_row);
if ($products_per_row > 1) {
	$float = "floatleft";
} else {
	$float = "center";
}
?>
<div class="vmgroup<?php echo $params->get ('moduleclass_sfx') ?> products_box">

	<?php if ($headerText) { ?>
	<div class="vmheader"><?php echo $headerText ?></div>
	<?php
}
	if ($display_style == "div") {
		?>
		<div class="vmproduct<?php echo $params->get ('moduleclass_sfx'); ?> productdetails row-fluid">
			<?php foreach ($products as $product) { ?>
			<div class="box_product <?php echo $pwidth; ?>">
				<div class="spacer">
					<?php
					if (!empty($product->images[0])) {
						$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
					} else {
						$image = '';
					}
					echo JHTML::_ ('link', JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
					echo '<div class="clear"></div>';
					$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
						$product->virtuemart_category_id); ?>
					  <div class="product_name">
					<a href="<?php echo $url ?>"><?php echo $product->product_name ?></a>
					  </div>
					<?php echo '<div class="clear"></div>';

					if ($show_price) {
						// echo $currency->priceDisplay($product->prices['salesPrice']);
						if (!empty($product->prices['salesPrice'])) {
							echo $currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						}
						// if ($product->prices['salesPriceWithDiscount']>0) echo $currency->priceDisplay($product->prices['salesPriceWithDiscount']);
						if (!empty($product->prices['salesPriceWithDiscount'])) {
							echo $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						}
					}
					if ($show_addtocart) {
						echo mod_virtuemart_product::addtocart ($product);
					}
					?>
				</div>
			</div>

			<?php
			if ($col == $products_per_row && $products_per_row && $col < $totalProd) { ?>

			  </div><div class="vmproduct<?php echo $params->get ('moduleclass_sfx'); ?> productdetails row-fluid">

			  <?php

				$col = 1;
			} else {
				$col++;
			}
		} ?>

		</div>
		<br style='clear:both;'/>

		<?php
	} else {
		$last = count ($products) - 1;
		?>

		<ul class="vmproduct<?php echo $params->get ('moduleclass_sfx'); ?> productdetails row-fluid">
			<?php foreach ($products as $product) : ?>
			<li class="box_product <?php echo $pwidth; ?>">
				<?php
				if (!empty($product->images[0])) {
					$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
				} else {
					$image = '';
				}
				echo JHTML::_ ('link', JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
				echo '<div class="clear"></div>';
				$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
					$product->virtuemart_category_id); ?>
				    <div class="product_name">
				<a href="<?php echo $url ?>"><?php echo $product->product_name ?></a>
				    </div>
				    <?php echo '<div class="clear"></div>';
				// $product->prices is not set when show_prices in config is unchecked
				if ($show_price and  isset($product->prices)) {
					echo '<div class="product-price">'.$currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
					if ($product->prices['salesPriceWithDiscount'] > 0) {
						echo $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
					}
					echo '</div>';
				}
				if ($show_addtocart) {
					echo mod_virtuemart_product::addtocart ($product);
				}
				?>
			</li>
			<?php
			if ($col == $products_per_row && $products_per_row && $last) {
				echo '
		</ul><div class="clear"></div>
		<ul  class="vmproduct' . $params->get ('moduleclass_sfx') . ' productdetails row-fluid">';
				$col = 1;
			} else {
				$col++;
			}
			$last--;
		endforeach; ?>
		</ul>
		<div class="clear"></div>

		<?php
	}
	if ($footerText) : ?>
		<div class="vmfooter<?php echo $params->get ('moduleclass_sfx') ?>">
			<?php echo $footerText ?>
		</div>
		<?php endif; ?>
</div>