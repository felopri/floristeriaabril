<?php
/**
 *
 * Description
 *
 * @packageVirtueMart
 * @subpackage Config
 * @author RickG
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_shopfront.php 7518 2013-12-22 00:21:09Z openglobal $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');?>
<table width="100%">
<tr>
<td valign="top" width="50%">
<fieldset>
<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MORE_CORE_SETTINGS'); ?></legend>
<table class="admintable">
	<?php echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_SHOW_PRINTICON','show_printicon',VmConfig::get('show_printicon',1));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_PDF_ICON_SHOW','pdf_icon',VmConfig::get('pdf_icon',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND','show_emailfriend',VmConfig::get('show_emailfriend',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_RECCOMEND_UNATUH','recommend_unauth',VmConfig::get('recommend_unauth',0));
	echo VmHTML::row('checkbox','COM_VIRTUEMART_ASK_QUESTION_SHOW','ask_question',VmConfig::get('ask_question',0));
	?>
	<tr>
		<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH_EXPLAIN'); ?>">
									<label for="ask_question">
										<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH'); ?>
									</label>
	</span>
		</td>
		<td>
			<input type="text" value="<?php echo VmConfig::get('asks_minimum_comment_length', 50); ?>" class="inputbox" size="4" name="asks_minimum_comment_length">
		</td>
	</tr>
	<tr>
		<td class="key">
		<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH_EXPLAIN'); ?>">
									<label for="ask_question">
										<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH'); ?>
									</label>
								</span>
		</td>
		<td>
			<input type="text" value="<?php echo VmConfig::get('asks_maximum_comment_length', 2000); ?>" class="inputbox" size="4" name="asks_maximum_comment_length">
		</td>
	</tr>
	<?php
		echo VmHTML::row('checkbox','COM_VIRTUEMART_PRODUCT_NAVIGATION_SHOW','product_navigation',VmConfig::get('product_navigation',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_DISPLAY_STOCK','display_stock',VmConfig::get('display_stock',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_COUPONS_ENABLE','coupons_enable',VmConfig::get('coupons_enable',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_UNCAT_CHILD_PRODUCTS_SHOW','show_uncat_child_products',VmConfig::get('show_uncat_child_products',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_VM_ERROR_HANDLING_ENABLE','handle_404',VmConfig::get('handle_404',1));
	?>

<tr>
	<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE_EXPLAIN'); ?>">
	<label for="coupons_default_expire">
		<?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE'); ?>
	</label>
</span>
	</td>
	<td>
		<?php
		// TODO This must go to the view.html.php.... but then... that goes for most of the config sruff I'ld say :-S
		$_defaultExpTime = array(
			'1,D' => '1 ' . JText::_('COM_VIRTUEMART_DAY')
		, '1,W' => '1 ' . JText::_('COM_VIRTUEMART_WEEK')
		, '2,W' => '2 ' . JText::_('COM_VIRTUEMART_WEEK_S')
		, '1,M' => '1 ' . JText::_('COM_VIRTUEMART_MONTH')
		, '3,M' => '3 ' . JText::_('COM_VIRTUEMART_MONTH_S')
		, '6,M' => '6 ' . JText::_('COM_VIRTUEMART_MONTH_S')
		, '1,Y' => '1 ' . JText::_('COM_VIRTUEMART_YEAR')
		);
		echo VmHTML::selectList('coupons_default_expire', VmConfig::get('coupons_default_expire'), $_defaultExpTime);
		?>
	</td>
</tr>
	<tr>
		<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_REMOVE_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_COUPONS_REMOVE'); ?>
				 </span>
		</td>
		<td>
			<?php echo $this->orderStatusModel->renderOSList(VmConfig::get('cp_rm',array('C')),'cp_rm',TRUE) ; ?>
		</td>
	</tr>


<tr>
	<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_WEIGHT_UNIT_DEFAULT_EXPLAIN'); ?>">
	<label for="weight_unit_default">
		<?php echo JText::_('COM_VIRTUEMART_WEIGHT_UNIT_DEFAULT'); ?>
	</label>
</span>
	</td>
	<td>
		<?php echo ShopFunctions::renderWeightUnitList('weight_unit_default', VmConfig::get('weight_unit_default')); ?>
	</td>
</tr>
<tr>
	<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_LWH_UNIT_DEFAULT_EXPLAIN'); ?>">
	<label for="weight_unit_default">
		<?php echo JText::_('COM_VIRTUEMART_LWH_UNIT_DEFAULT'); ?>
	</label>
</span>
	</td>
	<td>
		<?php echo ShopFunctions::renderLWHUnitList('lwh_unit_default', VmConfig::get('lwh_unit_default')); ?>
	</td>
</tr>
<tr>
	<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_DAYS_EXPLAIN'); ?>">
	<label for="latest_products_weeks">
		<?php echo JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_DAYS'); ?>
	</label>
</span>
	</td>
	<td>
		<input type="text" value="<?php echo VmConfig::get('latest_products_days', 7); ?>" class="inputbox" size="4" name="latest_products_days">
	</td>
</tr>
<tr>
	<td class="key">
<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY_EXPLAIN'); ?>">
	<label for="latest_products_orderBy">
		<?php echo JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY'); ?>
	</label>
</span>
	</td>
	<td>
		<?php
		$latest_products_orderBy = array(
			'modified_on' => JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY_MODIFIED'),
			'created_on' => JText::_('COM_VIRTUEMART_LATEST_PRODUCTS_ORDERBY_CREATED')
		);
		echo VmHTML::selectList('latest_products_orderBy', VmConfig::get('latest_products_orderBy', 'created_on'), $latest_products_orderBy);
		?>
	</td>
</tr>
</table>
</fieldset>
</td>
<td>
	<fieldset class="checkboxes">
		<legend>
			<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_POOS_ENABLE_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_CFG_POOS_ENABLE'); ?>
			</span>
		</legend>
		<div>
			<?php echo VmHTML::checkbox('lstockmail', VmConfig::get('lstockmail')); ?>
			<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY_TIP'); ?>">
				<label for="reviews_autopublish">
					<?php echo JText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY'); ?>
				</label>
			</span>
		</div>
		<?php
		$options = array(
			'none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_NONE'),
			'disableit' => JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT'),
			'disableit_children' => JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT_CHILDREN'),
			'disableadd' => JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_ADD'),
			'risetime' => JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_RISE_AVATIME')
		);
		echo VmHTML::radioList('stockhandle', VmConfig::get('stockhandle', 'none'), $options);
		?>
		<div style="font-weight:bold;">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY_EXPLAIN'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY'); ?>
					</span>
		</div>
		<input type="text" class="inputbox" id="product_availability" name="rised_availability" value="<?php echo VmConfig::get('rised_availability'); ?>"/>
		<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>' . JText::_('COM_VIRTUEMART_AVAILABILITY') . '</b><br/ >' . JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1') ?>"></span>

		<div class="clr"></div>
		<?php echo JHTML::_('list.images', 'image', VmConfig::get('rised_availability'), " ", $this->imagePath); ?>
		<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>' . JText::_('COM_VIRTUEMART_AVAILABILITY') . '</b><br/ >' . JText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2', $this->imagePath) ?>"></span>

		<div class="clr"></div>
		<img border="0" id="imagelib" alt="<?php echo JText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php if (VmConfig::get('rised_availability')) {
			echo JURI::root(true) . $this->imagePath . VmConfig::get('rised_availability');
		}?>"/>
	</fieldset>
	<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_TITLE'); ?></legend>
		<table class="admintable">
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH_TIP'); ?>">
						<label for="reviews_autopublish">
							<?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH'); ?>
						</label>
					</span>
				</td>
				<td>
					<?php echo VmHTML::checkbox('reviews_autopublish', VmConfig::get('reviews_autopublish')); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH_TIP'); ?>">
						<label for="reviews_minimum_comment_length">
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH'); ?>
						</label>
					</span>
				</td>
				<td>
					<input type="text" size="6" id="reviews_minimum_comment_length" name="reviews_minimum_comment_length" class="inputbox" value="<?php echo VmConfig::get('reviews_minimum_comment_length'); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH_TIP'); ?>">
						<label>
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH'); ?>
						</label>
					</span>
				</td>
				<td>
					<input type="text" size="6" id="reviews_maximum_comment_length" name="reviews_maximum_comment_length" class="inputbox" value="<?php echo VmConfig::get('reviews_maximum_comment_length'); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_EXPLAIN'); ?>">
						<label>
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW'); ?>
						</label>
					</span>
				</td>
				<td>
					<fieldset class="checkboxes">
						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_NONE'),
							'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_REGISTERED'),
							'all' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_ALL')
						); //showReviewFor
						echo VmHTML::radioList('showReviewFor', VmConfig::get('showReviewFor', 2), $showReviewFor); ?>

					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_EXPLAIN'); ?>">
						<label>
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW'); ?>
						</label>
					</span>
				</td>
				<td>
					<fieldset class="checkboxes">
						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_NONE'),
							'bought' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_BOUGHT_PRODUCT'),
							'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_REGISTERED')
							//	3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_ALL')
						);
						echo VmHTML::radioList('reviewMode', VmConfig::get('reviewMode', 2), $showReviewFor); ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_EXPLAIN'); ?>">
						<label>
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW'); ?>
						</label>
					</span>
				</td>
				<td>
					<fieldset class="checkboxes">
						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_NONE'),
							'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_REGISTERED'),
							'all' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_ALL')
						);
						echo VmHTML::radioList('showRatingFor', VmConfig::get('showRatingFor', 2), $showReviewFor); ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_EXPLAIN'); ?>">
						<label>
							<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING'); ?>
						</label>
					</span>
				</td>
				<td>
					<fieldset class="checkboxes">
						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_NONE'),
							'bought' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_BOUGHT_PRODUCT'),
							'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_REGISTERED')
							//	3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_ALL')	//TODO write system for all users (cookies)
						);
						echo VmHTML::radioList('ratingMode', VmConfig::get('ratingMode', 2), $showReviewFor); ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="key">
            	<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_REVIEWS_OS_TIP'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_REVIEWS_OS'); ?>
				 </span>
				</td>
				<td>
					<?php echo $this->orderStatusModel->renderOSList(VmConfig::get('rr_os',array('C')),'rr_os',TRUE); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</td>
</tr>
</table>
<script type="text/javascript">
	jQuery('#image').change(function () {
		var $newimage = jQuery(this).val();
		jQuery('#product_availability').val($newimage);
		jQuery('#imagelib').attr({ src:'<?php echo JURI::root(true) . $this->imagePath ?>' + $newimage, alt:$newimage });
	});
</script>