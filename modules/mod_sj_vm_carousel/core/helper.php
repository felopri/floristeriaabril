<?php
/**
 * @package Sj Carousel for Virtuemart
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
 
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/vmloader.php';
require_once dirname(__FILE__).'/helper_base.php';

abstract class SjCarouselHelper extends SjCarouselBaseHelper {
	public static function getList(&$params)
	{
		if(!class_exists('VmConfig')) return;
		VmConfig::loadConfig ();
		JFactory::getLanguage ()->load ('com_virtuemart');
		$items = array();
		$list = array();
		$_list = array();
		$catids = $params->get('catid');
		settype($catids, 'array');
		$limitation = (int)$params->get('source_limit',8);
		$desc_maxlength = $params->get('item_desc_max_characs',50);
		$source_group = null;
		$ordering_direction = $params->get('article_ordering_direction');
		$source_order = $params->get('source_order','group.featured');
		$p_special =  $params->get('show_front');
		$categoryModel = VmModel::getModel('Category');
		
		$additional_catids = array();
		$categories = array();
		if(!empty($catids)){
			if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 0) > 0) {
				$levels = $params->get('levels', 1) ? $params->get('levels', 1) : 9999;
				foreach($catids as $catid){
					$items = $categoryModel->getCategoryTree($catid,0);
					if(!empty($items)){
						foreach($items as $category){
							$condition = $category->level <= $levels;
							if ($condition) {
								$additional_catids[] = $category->virtuemart_category_id;
							}
						}
					}
				}
				$catids = array_unique(array_merge($catids, $additional_catids));
			}
			$productModel = VmModel::getModel('Product');
			$productModel = new VirtuemartModelProductExtend();
			$productModel->filter_order = $source_order;
			$productModel->specail_product = $p_special;
			$productModel->ordering_direction = $ordering_direction;
			$items = $productModel->getProductListing( $source_group,$limitation , true, true, false, true, $catids );
			
			$productModel->addImages($items);
			$ratingModel = VmModel::getModel('ratings');
			foreach($items as $item){
				$item->title = $item->product_name;	
				$item->id = $item->virtuemart_product_id;
				$item->description = $item->product_desc;
				self::getVmImages($item, $params);
				$item->short_desc = self::_cleanText($item->product_s_desc);
				$item->_description =  self::_cleanText($item->description);
				$item->_description = ($item->_description !='')?self::truncate($item->_description,$desc_maxlength):self::truncate($item->short_desc,$desc_maxlength);
				$item->vote = $ratingModel->getVoteByProduct($item->virtuemart_product_id);
				$item->rating = $ratingModel->getRatingByProduct($item->virtuemart_product_id);
				$list[] = $item;
			} 
			
		}
		return $list;
	}
	
}


if(!class_exists('VirtuemartModelProductExtend') && class_exists('VirtueMartModelProduct')){
	class VirtuemartModelProductExtend extends VirtueMartModelProduct{
				function sortSearchListQuery ($onlyPublished = TRUE, $virtuemart_category_id = FALSE, $group = FALSE, $nbrReturnProducts = FALSE) {
			$app = JFactory::getApplication ();
			$groupBy = ' group by p.`virtuemart_product_id` ';

			$joinCategory = FALSE;
			$joinMf = FALSE;
			$joinPrice = FALSE;
			$joinCustom = FALSE;
			$joinShopper = FALSE;
			$joinChildren = FALSE;
			$joinLang = TRUE;
			$orderBy = ' ';

			$where = array();
			$useCore = TRUE;
			if ($useCore) {
				$isSite = $app->isSite ();
				if ($onlyPublished) {
					$where[] = ' p.`published`="1" ';
				}
				if($isSite and !VmConfig::get('use_as_catalog',0)) {
					if (VmConfig::get('stockhandle','none')=='disableit_children') {
						$where[] = ' (p.`product_in_stock` - p.`product_ordered` >"0" OR children.`product_in_stock` - children.`product_ordered` > "0") ';
						$joinChildren = TRUE;
					} else if (VmConfig::get('stockhandle','none')=='disableit') {
						$where[] = ' p.`product_in_stock` - p.`product_ordered` >"0" ';
					}
				}
			
				if ( $virtuemart_category_id !== false ){
					$joinCategory = TRUE ;
					
					if ( is_string($virtuemart_category_id) && preg_match('/[\s|,]+/', $virtuemart_category_id)){
						$virtuemart_category_id = preg_split('/[\s|,]+/', $virtuemart_category_id);
					}
					if ( !is_array($virtuemart_category_id) ){
						settype($virtuemart_category_id, 'array');
					}
					
					$where[] = ' `pc`.`virtuemart_category_id` IN ('.implode(',', $virtuemart_category_id).')';
					
				}

				if ($isSite and !VmConfig::get('show_uncat_child_products',TRUE)) {
					$joinCategory = TRUE;
					$where[] = ' `pc`.`virtuemart_category_id` > 0 ';
				}

				if ($this->product_parent_id) {
					$where[] = ' p.`product_parent_id` = ' . $this->product_parent_id;
				}

				if ($isSite) {
					$usermodel = VmModel::getModel ('user');
					$currentVMuser = $usermodel->getUser ();
					$virtuemart_shoppergroup_ids = (array)$currentVMuser->shopper_groups;

					if (is_array ($virtuemart_shoppergroup_ids)) {
						$sgrgroups = array();
						foreach ($virtuemart_shoppergroup_ids as $key => $virtuemart_shoppergroup_id) {
							$sgrgroups[] = 's.`virtuemart_shoppergroup_id`= "' . (int)$virtuemart_shoppergroup_id . '" ';
						}
						$sgrgroups[] = 's.`virtuemart_shoppergroup_id` IS NULL ';
						$where[] = " ( " . implode (' OR ', $sgrgroups) . " ) ";

						$joinShopper = TRUE;
					}
				}

				if ($this->virtuemart_manufacturer_id) {
					$joinMf = TRUE;
					$where[] = ' `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` = ' . $this->virtuemart_manufacturer_id;
				}
				
				switch ($this->specail_product){
					case 'hide':
						$where[] = ' p.`product_special`="0" '; 
						break;

					case 'only':
						$where[] = ' p.`product_special`="1" '; 
						break;

					case 'show':
					default:
						break;
				}
				
				$this->filter_order_Dir = $this->ordering_direction;
				
				if($this->filter_order){
					switch ($this->filter_order) {
						default:
						case 'id':
							$orderBy = ' ORDER BY p.`virtuemart_product_id` ';
							break;
						case 'ordering':
							$orderBy = ' ORDER BY `pc`.`ordering` ';
							$joinCategory = TRUE;
							break;
						case 'product_name':
							$orderBy = ' ORDER BY `product_name` ';
							$joinPrice = TRUE;
							break;
						case 'product_price':
							$orderBy = ' ORDER BY `product_price` ';
							$joinPrice = TRUE;
							break;	
						case 'created_on':
							$orderBy = ' ORDER BY p.`created_on` ';
							break;
						case 'latest':
							$orderBy = 'ORDER BY p.`modified_on`';
							break;
						case 'topten':
							$orderBy = ' ORDER BY p.`product_sales` '; 
							$where[] = 'pp.`product_price`>"0.0" ';
							break;
						
					}
					$joinPrice = TRUE;
				}
				if ($group) {
					$groupBy = 'group by p.`virtuemart_product_id` ';
				}
			}

			if ($joinLang) {
				$select = ' l.`virtuemart_product_id` FROM `#__virtuemart_products_' . VMLANG . '` as l';
				$joinedTables = ' JOIN `#__virtuemart_products` AS p using (`virtuemart_product_id`)';
			}
			else {
				$select = ' p.`virtuemart_product_id` FROM `#__virtuemart_products` as p';
				$joinedTables = '';
			}

			if ($joinCategory == TRUE) {
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_categories` as pc ON p.`virtuemart_product_id` = `pc`.`virtuemart_product_id`
				 LEFT JOIN `#__virtuemart_categories_' . VMLANG . '` as c ON c.`virtuemart_category_id` = `pc`.`virtuemart_category_id`';
			}
			if ($joinMf == TRUE) {
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_manufacturers` ON p.`virtuemart_product_id` = `#__virtuemart_product_manufacturers`.`virtuemart_product_id`
				 LEFT JOIN `#__virtuemart_manufacturers_' . VMLANG . '` as m ON m.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
			}

			if ($joinPrice == TRUE) {
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_prices` as pp ON p.`virtuemart_product_id` = pp.`virtuemart_product_id` ';
			}
			if ($this->searchcustoms) {
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_customfields` as pf ON p.`virtuemart_product_id` = pf.`virtuemart_product_id` ';
			}
			if ($this->searchplugin !== 0) {
				if (!empty($PluginJoinTables)) {
					$plgName = $PluginJoinTables[0];
					$joinedTables .= ' LEFT JOIN `#__virtuemart_product_custom_plg_' . $plgName . '` as ' . $plgName . ' ON ' . $plgName . '.`virtuemart_product_id` = p.`virtuemart_product_id` ';
				}
			}
			if ($joinShopper == TRUE) {
				$joinedTables .= ' LEFT JOIN `#__virtuemart_product_shoppergroups` ON p.`virtuemart_product_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_product_id`
				 LEFT  OUTER JOIN `#__virtuemart_shoppergroups` as s ON s.`virtuemart_shoppergroup_id` = `#__virtuemart_product_shoppergroups`.`virtuemart_shoppergroup_id`';
			}

			if ($joinChildren) {
				$joinedTables .= ' LEFT OUTER JOIN `#__virtuemart_products` children ON p.`virtuemart_product_id` = children.`product_parent_id` ';
			}

			if (count ($where) > 0) {
				$whereString = ' WHERE (' . implode (' AND ', $where) . ') ';
			}
			else {
				$whereString = '';
			}

			$this->orderByString = $orderBy;
			$product_ids = $this->exeSortSearchListQuery (2, $select, $joinedTables, $whereString, $groupBy, $orderBy, $this->filter_order_Dir, $nbrReturnProducts);
			return $product_ids;

		}

	}
}	