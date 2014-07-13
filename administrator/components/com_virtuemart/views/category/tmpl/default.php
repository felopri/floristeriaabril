<?php
/**
*
* Lists all the categories in the shop
*
* @package	VirtueMart
* @subpackage Category
* @author RickG, jseros, RolandD, Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if (!class_exists ('shopFunctionsF'))
	require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');

AdminUIHelper::startAdminArea($this);

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
<div id="filterbox">
	<table class="">
		<tr>
			<td align="left">
			<?php echo $this->displayDefaultViewSearch() ?>
			</td>

		</tr>
	</table>
	</div>
	<div id="resultscounter"><?php echo $this->catpagination->getResultsCounter(); ?></div>

</div>


	<div id="editcell">
		<table class="adminlist" cellspacing="0" cellpadding="0">
		<thead>
		<tr>

			<th width="20px">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->categories); ?>);" />
			</th>
			<th align="left" width="12%">
				<?php echo $this->sort('category_name') ?>
			</th>
			<th align="left">
				<?php echo $this->sort('category_description', 'COM_VIRTUEMART_DESCRIPTION') ; ?>
			</th>
			<th align="left" width="11%">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_S'); ?>
			</th>

			<th align="left" width="13%">
				<?php echo $this->sort( 'c.ordering' , 'COM_VIRTUEMART_ORDERING') ?>
				<?php echo JHTML::_('grid.order', $this->categories, 'filesave.png', 'saveOrder' ); ?>
			</th>
			<th align="center" width="20px">
				<?php echo $this->sort('c.published' , 'COM_VIRTUEMART_PUBLISHED') ?>
			</th>
			<?php if(Vmconfig::get('multix','none')!=='none'){
				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
				if(Permissions::getInstance()->check('admin') ){ ?>
					<th width="20px">
					<?php echo $this->sort( 'cx.category_shared' , 'COM_VIRTUEMART_SHARED') ?>
					</th>
			<?php }
			}?>

			<th><?php echo $this->sort('virtuemart_category_id', 'COM_VIRTUEMART_ID')  ?></th>
			<!--th></th-->
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		$repeat = 0;

 		$nrows = count( $this->categories );

		if( $this->catpagination->limit < $nrows ){
			if( ($this->catpagination->limitstart + $this->catpagination->limit) < $nrows ) {
				$nrows = $this->catpagination->limitstart + $this->catpagination->limit;
			}
		}

// 		for ($i = $this->pagination->limitstart; $i < $nrows; $i++) {

		foreach($this->categories as $i=>$cat){

// 			if( !isset($this->rowList[$i])) $this->rowList[$i] = $i;
// 			if( !isset($this->depthList[$i])) $this->depthList[$i] = 0;

// 			$row = $this->categories[$this->rowList[$i]];

			$checked = JHTML::_('grid.id', $i, $cat->virtuemart_category_id);
			$published = JHTML::_('grid.published', $cat, $i);
			$editlink = JRoute::_('index.php?option=com_virtuemart&view=category&task=edit&cid=' . $cat->virtuemart_category_id, FALSE);
// 			$statelink	= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cat->virtuemart_category_id);
			$showProductsLink = JRoute::_('index.php?option=com_virtuemart&view=product&virtuemart_category_id=' . $cat->virtuemart_category_id, FALSE);
			$shared = $this->toggle($cat->shared, $i, 'toggle.shared');

			$categoryLevel = '';
			if(!isset($cat->level)){
				if($cat->category_parent_id){
					$cat->level = 1;
				} else {
					$cat->level = 0;
				}

			}
			$repeat = $cat->level;

			if($repeat > 1){
				$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $repeat - 1);
				$categoryLevel .= "<sup>|_</sup>&nbsp;";
			}
		?>
			<tr class="<?php echo "row".$k;?>">

				<td><?php echo $checked;?></td>
				<td align="left">
					<span class="categoryLevel"><?php echo $categoryLevel;?></span>
					<a href="<?php echo $editlink;?>"><?php echo $this->escape($cat->category_name);?></a>
				</td>
				<td align="left">

					<?php

					echo shopFunctionsF::limitStringByWord(JFilterOutput::cleanText($cat->category_description),200); ?>
				</td>
				<td>
					<?php echo  $this->catmodel->countProducts($cat->virtuemart_category_id);//ShopFunctions::countProductsByCategory($row->virtuemart_category_id);?>
					&nbsp;<a href="<?php echo $showProductsLink; ?>">[ <?php echo JText::_('COM_VIRTUEMART_SHOW');?> ]</a>
				</td>
				<td align="center" class="order">
					<span><?php 
					
					
					$cond = (($cat->category_parent_id == 0 || $cat->category_parent_id == @$this->categories[$i - 1]->category_parent_id));
					$cond2= ($cat->category_parent_id == 0 || $cat->category_parent_id == @$this->categories[$i + 1]->category_parent_id);
					echo $this->catpagination->orderUpIcon( $i, $cond, 'orderUp', JText::_('COM_VIRTUEMART_MOVE_UP')); ?></span>
					<span><?php echo $this->catpagination->orderDownIcon( $i, $nrows, $cond2, 'orderDown', JText::_('COM_VIRTUEMART_MOVE_DOWN')); ?></span>
					<input class="ordering" type="text" name="order[<?php echo $i?>]" id="order[<?php echo $i?>]" size="5" value="<?php echo $cat->ordering; ?>" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<?php
				if((Vmconfig::get('multix','none')!='none')) {
					?><td align="center">
						<?php echo $shared; ?>
                    </td>
					<?php
				}
				?>
				<td><?php echo $cat->virtuemart_category_id; // echo $product->vendor_name; ?></td>
				<!--td >
					<span class="vmicon vmicon-16-move"></span>
				</td-->
			</tr>
		<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->catpagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<?php
	vmdebug('my name here is '.$this->_name);
	echo $this->addStandardHiddenToForm($this->_name,$this->task);

	  ?>
</form>

<?php
// Removed for the moment,categories can only be drag and drop within their sublevel
//DragnDrop by StephanBais
	//if ($this->virtuemart_category_id ) { ?>
	<!--script>

		jQuery(function() {

			jQuery( ".adminlist" ).sortable({
				handle: ".vmicon-16-move",
				items: 'tr:not(:first,:last)',
				opacity: 0.8,
				update: function(event, ui) {
					var i = 1;
					jQuery(function updaterows() {
						jQuery(".order").each(function(index){
							var row = jQuery(this).parent('td').parent('tr').prevAll().length;
							jQuery(this).val(row);
							i++;
						});

					});
				},
				stop: function () {
					var inputs = jQuery('input.ordering');
					var rowIndex = inputs.length;
					jQuery('input.ordering').each(function(idx) {
						jQuery(this).val(idx + 1);
					});
				}

			});
		});
		jQuery('input.ordering').css({'color': '#666666', 'background-color': 'transparent','border': 'none' }).attr('readonly', true);
	</script-->

<?php // } ?>

<?php AdminUIHelper::endAdminArea(); ?>
