<?php
/*
# ------------------------------------------------------------------------
# Templates for Joomla 2.5 - Joomla 3.5
# ------------------------------------------------------------------------
# Copyright (C) 2011-2013 Jtemplate.ru. All Rights Reserved.
# @license - PHP files are GNU/GPL V2.
# Author: Makeev Vladimir
# Websites:  http://www.jtemplate.ru 
# ---------  http://code.google.com/p/jtemplate/   
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;


/* 
only test:
randomStart: <?php echo $jt_random_start;?>,
ticker: <?php echo $jt_ticker;?>,
tickerHover: <?php echo $jt_ticker_hover;?>,
 */
?>

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#bxslider-vm-<?php echo $jt_id; ?>').bxSlider({
		mode: '<?php echo $jt_mode;?>',
		minSlides: <?php echo $jt_min_slides;?>,
		maxSlides: <?php echo $jt_max_slides;?>,
		slideWidth: <?php echo $jt_slide_width;?>,
		slideMargin: <?php echo $jt_slide_margin;?>,
		moveSlides: <?php echo $jt_move_slides;?>,
		adaptiveHeight: <?php echo $jt_adaptive_height;?>,
		adaptiveHeightSpeed: <?php echo $jt_adaptive_height_speed;?>,
		speed: <?php echo $jt_speed;?>,
		controls: <?php echo $jt_controls; ?>,
		auto: <?php echo $jt_auto;?>,
		autoControls: <?php echo $jt_auto_controls;?>,
		pause: <?php echo $jt_pause?>,
		autoDelay: <?php echo $jt_auto_delay; ?>,
		autoHover: <?php echo $jt_autohover; ?>,
		pager: <?php echo $jt_pager;?>,
		pagerType: '<?php echo $jt_pager_type;?>',
		pagerShortSeparator: '<?php echo $jt_pager_saparator;?>'
	});
});
</script>

<div class="mod_jt_bxslider_vm_product <?php echo $moduleclass_sfx ?>">	
	<div id="bxslider-vm-<?php echo $jt_id; ?>">	
		<?php foreach ($products as $product) { ?>
			<div class="box_slidesshow_product">
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
					<a class="slidesshow_product_name" href="<?php echo $url ?>"><?php echo $product->product_name ?></a>        
					
					<?php   
					 echo '<div class="clear"></div>';
					if ($show_price) {
						// 		echo $currency->priceDisplay($product->prices['salesPrice']);
						if (!empty($product->prices['salesPrice'])) {
							echo $currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						}
						// 		if ($product->prices['salesPriceWithDiscount']>0) echo $currency->priceDisplay($product->prices['salesPriceWithDiscount']);
						if (!empty($product->prices['salesPriceWithDiscount'])) {
							echo $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						}
					}

					if ($show_addtocart) {
						echo mod_jt_bxslider_vm_product::addtocart ($product);
					}
					
				?>
			</div>
		<?php } ?>
	</div>	
<div style="clear:both;"></div>
</div>

