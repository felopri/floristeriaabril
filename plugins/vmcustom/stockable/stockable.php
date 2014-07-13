<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**
 * @version $Id: standard.php,v 1.4 2005/05/27 19:33:57 ei
 *
 * a special type of 'cash on delivey':
 * its fee depend on total sum
 * @author Max Milbers
 * @version $Id: stockable.php 3681 2011-07-08 12:27:36Z alatak $
 * @package VirtueMart
 * @subpackage vmcustom
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */
if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

class plgVmCustomStockable extends vmCustomPlugin {

	private $stockhandle = 0;
	// instance of class
// 	public static $_this = false;

	function __construct(& $subject, $config) {
// 		if(self::$_this) return self::$_this;
		parent::__construct($subject, $config);

		$varsToPush = array(
			'selectname1'=>array('','char'),'selectname2'=>array('','char'),'selectname3'=>array('','char'),'selectname4'=>array('','char'),
			'selectoptions1'=>array('','char'),'selectoptions2'=>array('','char'),'selectoptions3'=>array('','char'),'selectoptions4'=>array('','char')
		);

		$this->setConfigParameterable('custom_params',$varsToPush);

// 		self::$_this = $this;
	}

	// function plgVmOnOrder($product) {

		// $dbValues['virtuemart_product_id'] = $product->virtuemart_product_id;
		// $dbValues['stockable'] = $this->_virtuemart_paymentmethod_id;
		// $this->writeCustomData($dbValues, '#__virtuemart_product_custom_' . $this->_name);
	// }




	// get product param for this plugin on edit
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnProductEdit()
	 * @author Matt Lewis-Garner
	 * @author Patrick Kohl
	 */
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {
//TODO Give warning if config not set to disableit_children
		if ($field->custom_element != $this->_name) return '';

		$this->parseCustomParams($field);
		$html ='';
		if (!$childs = $this->getChilds($product_id) ) $html .='<DIV>'.JTEXT::_('VMCUSTOM_STOCKABLE_NO_CHILD').'</DIV>';
		$db = JFactory::getDBO();
//		$db->setQuery('SELECT `virtuemart_custom_id` FROM `#__virtuemart_customs` WHERE field_type="G" ');
//		$group_custom_id = $db->loadResult();
		// $plgParam = $this->getVmCustomParams($field->virtuemart_custom_id);

		$html .='<span style="width:50px; display: inline-block;">'.JText::_('VMCUSTOM_STOCKABLE_IS_VARIANT').'</span>';

		for ($i = 1; $i<5 ;$i++) {
			$selectname = 'selectname'.$i ;
			$listname = $field->$selectname;
			if (!empty($listname)) {
			$html .=' <span style="width:98px; display: inline-block;color:#000;overflow:hidden;">'.JTEXT::_($listname).'</span>';
			}
		}
		$html .=' <span style="width:98px; display: inline-block;color:#000;">'. JText::_('VMCUSTOM_STOCKABLE_PRICE') .'</span>';
		// $param = json_decode($field->custom_param,true);
		if (isset($field->child)) $childList = $field->child;
		else $childList = array();
		$html .= '<div id="stockables">';
		foreach ($childs as $child ) {
			$checked ='';
			$price = null;
			if(!empty($childList)) {
				if (!array_key_exists($child->id, $childList) ) $childList[$child->id]['is_variant'] = 1;
				if ($childList[$child->id]['is_variant'] ) $checked='checked';
				if (array_key_exists('custom_price', $childList[$child->id] ) )
					$price = $childList[$child->id]['custom_price'] ;
			}
			//$html .= JHTML::_('select.genericlist', $childlist, 'custom_param['.$row.'][child_id]','','virtuemart_product_id','product_name',$param['child_id'],false,true);
			$name='custom_param['.$row.'][child]['.$child->id.']';
			$html .='<div class="stockable">' ;
			$html .='	<input type="hidden"  value="0" name="'.$name.'[is_variant]">';
			$html .='	<span style="width:50px; display: inline-block;"><input type="checkbox" '.$checked.'  value="1" name="'.$name.'[is_variant]"></span>';

			$html .=$this->getCustomOptionsForm($field, $name, $childList[$child->id]);

			//$html .='<input  type="hidden" name="'.$name.'[child_id]" value="'.$child->id.'">';
			// if (!$customfield = $this->getFieldId($product_id, $child->id) ) $price ='' ;
			// else

			$html .='<input style="width:98px; display: inline-block;" type="text" name="'.$name.'[custom_price]" value="'.$price.'">';
			// $html .='<input type="hidden" name="custom_param[c'.$child->id.'][field_type]" value="G">';
			// $html .='<input type="hidden" name="field[c'.$child->id.'][virtuemart_custom_id]" value="'.$group_custom_id.'">';

			$html .= ' '.$child->product_name.' ['.JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK').' : '.$child->stock.']</div>' ;

		}
		$html .='</div>
				<fieldset style="background-color:#F9F9F9;">
					<legend>'. JText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL').'</legend>
					<div id="new_stockable">
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_SKU').'</span> <span><input value="" name="stockable[product_sku]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_NAME').'</span> <span><input value="" name="stockable[product_name]" type="text"></span>
						<span>'. JText::_('VMCUSTOM_STOCKABLE_PRICE').'</span> <span><input value="" name="stockable[product_price]" type="text"></span>
						<span>'. JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK').'</span> <span><input value="" name="stockable[product_in_stock]" type="text"></span>

						<span id="new_stockable_product"><span class="icon-nofloat vmicon vmicon-16-new"></span>'. JText::_('COM_VIRTUEMART_ADD').'</span>
					</div>
				</fieldset>';

		$new_fields = $this->getCustomOptionsForm($field, '{{NAME}}', array());

		$script = "
	jQuery( function($) {
		$('#new_stockable_product').click(function() {
			var Prod = $('#new_stockable');// input[name^=\"stockable\"]').serialize();

			$.getJSON('index.php?option=com_virtuemart&view=product&task=saveJS&token=".JUtility::getToken()."' ,
				{
					product_sku: Prod.find('input[name*=\"product_sku\"]').val(),
					product_name: Prod.find('input[name*=\"product_name\"]').val(),
					product_price: Prod.find('input[name*=\"product_price\"]').val(),
					product_in_stock: Prod.find('input[name*=\"product_in_stock\"]').val(),
					product_parent_id: ".$product_id.",
					published: 1,
					format: \"json\"
				},
				function(data) {
					//console.log (data);
					//$.each(data.msg, function(index, value){
//						$(\"#new_stockable\").append(data.msg);
					//});
					name='custom_param[".$row."][child]['+data.product_id+']';
					new_fields = '".str_replace("\n", '', $new_fields)."';
					new_fields = new_fields.replace(/\{\{NAME\}\}/g, name);
					$('#stockables').append(
						'<div class=\"stockable\">' +
							'<input type=\"hidden\"  value=\"0\" name=\"'+name+'[is_variant]\">' +
							'<span style=\"width:50px; display: inline-block;\"><input type=\"checkbox\" checked=\"checked\"  value=\"1\" name=\"'+name+'[is_variant]\"></span>' +
							new_fields +
							'<input style=\"width:98px; display: inline-block;\" type=\"text\" name=\"'+name+'[custom_price]\" value=\"'+Prod.find('input[name*=\"product_price\"]').val()+'\">' +
							' '+Prod.find('input[name*=\"product_name\"]').val()+' [".JText::_('COM_VIRTUEMART_PRODUCT_SKU')." : '+Prod.find('input[name*=\"product_sku\"]').val()+'] [".JText::_('COM_VIRTUEMART_PRODUCT_IN_STOCK')." : '+(Prod.find('input[name*=\"product_in_stock\"]').val() || 0)+']' +
							'</div>');
					Prod.find('input[name*=\"product_sku\"]').val('');
					Prod.find('input[name*=\"product_name\"]').val('');
					Prod.find('input[name*=\"product_price\"]').val('');
					Prod.find('input[name*=\"product_in_stock\"]').val('');
				});
		});

		jQuery('input[name=field\\\\[$row\\\\]\\\\[custom_price\\\\]]').val('');
	});
	";
		//$document = JFactory::getDocument();
		//$document->addScriptDeclaration($script);
		// $html  ='<input type="text" value="'.$field['custom_name'].'" size="10" name="custom_param['.$row.'][custom_name]"> ';
		// $html .='<input type="text" value="'.$field['custom_size'].'" size="10" name="custom_param['.$row.'][custom_size]">';
		//$html .=JTEXT::_('VMCUSTOM_TEXTINPUT_NO_CHANGES_BE');
		$retValue .= $html.'<script type="text/javascript">'.$script.'</script>';
		return true ;
	}

	/**
	 * Get the formatted options dropdowns and input fields (backend)
	 * @author Matt Lewis-Garner
	 */
	function getCustomOptionsForm($field, $name, $values) {
		$options_html = '';
		for ($i = 1; $i<5 ;$i++) {
			$selectoptions = 'selectoptions'.$i ;
			$attributes = 'attribute'.$i ;
			if (isset($field->$selectoptions)) $selectoption = (string)$field->$selectoptions;
			else  $selectoption = "" ;
			$option = array();
			$tmpOptions = str_replace( "\r", "" ,$selectoption);

			if ($listoptions = explode("\n",$tmpOptions ) ) {
				foreach ($listoptions as $key => $val) $option[] = JHTML::_('select.option',JText::_( $val ) , $val  );
				if (empty($values[$selectoptions])) {
					$values[$selectoptions] ='';
				}
				if ($listoptions[0] == '' && $field->{'selectname'.$i}) {
					$options_html .= '<input type="text" name="'.$name.'['.$selectoptions.']" value="'.$values[$selectoptions].'" style="width:100px;" />';// <span style="width:98px; display: inline-block;color:#000;">'.JText::_('VMCUSTOM_STOCKABLE_NO_OPTION') .'</span>';
				} else if ($listoptions[0] == '') {
					$options_html .= '';
				} else {
					$options_html .= JHTML::_('select.genericlist', $option, $name.'['.$selectoptions.']','style="width:100px !important;float:none;"','text','value',$values[$selectoptions],false,true)."\n";
				}
			}
		}

		return $options_html;
	}

	/**
	 * @ idx plugin index
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::onDisplayProductFE()
	 * @author Matt Lewis-Garner
	 * @author Patrick Kohl
	 */
	function plgVmOnDisplayProductVariantFE($field,&$row,&$group) {
		// default return if it's not this plugin
		if ($field->custom_element != $this->_name) return '';
		$row++;
		$this->parseCustomParams($field);
		//if (!$childs = $this->getChilds($product_id) ) return ;
		$this->stockhandle = VmConfig::get('stockhandle','none');
		$html='<br />';
		$customfield_id = array();
		$selects = array();
		$js = array();
		// generate option with valid child results
		foreach($field->child as $child_id => &$attribut) {

			if ($attribut['is_variant']==1) {
				unset ($attribut['is_variant']);
				if ($stock = $this->getValideChild( $child_id)) {
					$field->child[$child_id]['in_stock'] = $stock->product_in_stock - $stock->product_ordered;

					// Availability Image
					if ($field->child[$child_id]['in_stock'] < 1) {
						if ($this->stockhandle == 'risetime' and VmConfig::get('rised_availability') and empty($stock->product_availability)) {
			    			$field->child[$child_id]['product_availability'] = (file_exists(JPATH_BASE . DS . VmConfig::get('assets_general_path') . 'images/availability/' . VmConfig::get('rised_availability')))
			    				? JHTML::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . VmConfig::get('rised_availability', '7d.gif'), VmConfig::get('rised_availability', '7d.gif'), array('class' => 'availability'))
			    				: $field->child[$child_id]['product_availability'] = VmConfig::get('rised_availability');
						} else if (!empty($stock->product_availability)) {
							$field->child[$child_id]['product_availability'] = (file_exists(JPATH_BASE . DS . VmConfig::get('assets_general_path') . 'images/availability/' . $stock->product_availability))
								? JHTML::image(JURI::root() . VmConfig::get('assets_general_path') . 'images/availability/' . $stock->product_availability, $stock->product_availability, array('class' => 'availability'))
								: $field->child[$child_id]['product_availability'] = $stock->product_availability;
						}
					}
					//$field->child[$child_id]['product_availability'] = $stock->product_availability;

					if ($attribut['custom_price'])
						$js[]= '"'.$child_id.'" :'.$attribut['custom_price'];
					unset ($attribut['custom_price']);


					foreach ($attribut as $key => $list) {
						// if (!in_array($key,$selects)) {
							// $selects[$key] = array() ;
						// }
						// if (!in_array($list , $selects[$key]) ) {
							if (preg_match('/^selectoptions\d+$/', $key)) {
								$selects[$key][$list] = $list ;
								$attribut[$key] = array($list,JText::_($list));
							}
						// }

					}
				}
			} else unset ($attribut);
		}

		// Javascript can be added multiple times for multiple products on a page,
		// so need to suffix everything for the right products
		$js_suffix = $field->virtuemart_customfield_id;//.'_'.uniqid();

		$html .= '<div class="stockable_block_'.$js_suffix.'">';
		$i = 1;
		foreach ($selects as $keys =>$options) {
			$selectname = 'selectname'.$i;
			$listname = $field->$selectname;
			if (!empty($listname)) {
				$optionName = 'customPlugin['.$field->virtuemart_customfield_id.']['.$this->_name.']['.$keys.']';
				$option = array();
				$show_select = false;
				foreach ($options as $key => $val) {
					if (!empty($val)) {
						if (1 == $i) {
							$option[] = JHTML::_('select.option', $val, JText::_( $val ));
						}
						$show_select = true;
					}
				}
				if ($show_select) {
					$html .='<div style="width:200px;"><span style="vertical-align: top;width:98px; display: inline-block;color:#000;">'.JTEXT::_($listname).'</span>';
					$html .= JHTML::_('select.genericlist', $option,$optionName ,'class="attribute_list customfield_id_'.$js_suffix.'" style="width:100px !important;"','value','text',reset($options),'selectoptions'.$i,false)."</div>\n";
				} else $html .='<input id="'.$keys.'" class="attribute_list" type="hidden" value="'.$val.'" name="'.$optionName.'">' ;
			}
			$i++;
		}
		$html .= '</div>';
		static $stockablejs;

		$group->display = $html.'
				<input type="hidden" value="'.$child_id.'" name="customPlugin['.$field->virtuemart_customfield_id.']['.$this->_name.'][child_id]">';
		// preventing 2 x load javascript

		/*if ($stockablejs) return;
		$stockablejs = true ;*/

		// TODO ONE PARAM IS MISSING
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('
		//<![CDATA[
		jQuery( function($) {
			//var customfield_id = {'. implode(',' , $js ) .'};
			//var selecteds = [];//all selected options
			//var found_id=0;//found child id
			var stockhandle = "'.$this->stockhandle.'";
			var stockable_'.$js_suffix.' =$.parseJSON(\'' .str_replace('\"', '\\\"', json_encode($field->child)). '\') ;
			var selections_'.$js_suffix.' = [];
			//var original=[];
//			var totalattribut_'.$js_suffix.' = $("select.attribute_list.customfield_id_'.$js_suffix.'").length+1;
			var totalattribut_'.$js_suffix.' = [];
			// get all initial select list values
			/*$.each($(".attribute_list"), function(idx,selec) {
				original[selec.name] = $.map($(this).find("option"), function(idx, opt) {
						return [[ idx.value ,idx.text ]];
					});
			});*/

//			if ( $("#selectoptions1.attribute_list.customfield_id_'.$js_suffix.'").length ) {
			if ( $("select.attribute_list.customfield_id_'.$js_suffix.'").length ) {
					var stockableBlockIndex = 0;
					$(".stockable_block_'.$js_suffix.'").each(function() {
						$(this).attr("id", "stockableBlockIndex_'.$js_suffix.'_" + stockableBlockIndex);
						totalattribut_'.$js_suffix.'[stockableBlockIndex] = $(this).find("select.attribute_list.customfield_id_'.$js_suffix.'").length+1;
						recalculate_'.$js_suffix.'(stockableBlockIndex, $(this).find("select.attribute_list.customfield_id_'.$js_suffix.'").eq(0));
						stockableBlockIndex++;
					});
			}
			$("select.attribute_list.customfield_id_'.$js_suffix.'").unbind("change");
			$("select.attribute_list.customfield_id_'.$js_suffix.'").change(function(){
				var stockableBlockIndex = $(this).parents(".stockable_block_'.$js_suffix.'").attr("id").split("_");
				recalculate_'.$js_suffix.'(stockableBlockIndex[stockableBlockIndex.length-1], $(this));

			});
			function recalculate_'.$js_suffix.'(stockableBlockIndex, Opt){
				var found_id = 0;
				var currentIndex = $("#stockableBlockIndex_'.$js_suffix.'_"+stockableBlockIndex+" select.attribute_list.customfield_id_'.$js_suffix.'").index(Opt) +1;

				selections_'.$js_suffix.'[stockableBlockIndex] = [];
				var i=1;
				$("#stockableBlockIndex_'.$js_suffix.'_"+stockableBlockIndex+" select.attribute_list.customfield_id_'.$js_suffix.'").each(function() {
					selections_'.$js_suffix.'[stockableBlockIndex][i] = $(this).val();
					// Clear the following selects
					if (i > currentIndex) {
						$(this).empty();
					}

					i++;
				});

				// Find current values
				/*for(var i=1; i<totalattribut_'.$js_suffix.'[stockableBlockIndex]; i++){
					selections_'.$js_suffix.'[stockableBlockIndex][i] = $("#selectoptions"+i+".customfield_id_'.$js_suffix.'").val();
				}*/

				// Clear the following selects
				/*for(var i=currentIndex+1; i<totalattribut_'.$js_suffix.'[stockableBlockIndex]; i++){
					$("#stockableBlockIndex_'.$js_suffix.'_"+stockableBlockIndex+" #selectoptions"+i+".customfield_id_'.$js_suffix.'").empty();
				}*/

				// Repopulate the following selects
				jQuery.each(stockable_'.$js_suffix.', function(child_id, child_attrib) {
					if (isChildValid_'.$js_suffix.'(stockableBlockIndex, child_attrib, currentIndex)) {
						populateNextSelect_'.$js_suffix.'(stockableBlockIndex, child_attrib, currentIndex+1);
					}
				});

				// Identify the current child
				jQuery.each(stockable_'.$js_suffix.', function(child_id, child_attrib) {
					var i;
					for(i = 1; i < totalattribut_'.$js_suffix.'[stockableBlockIndex]; i++){
						if (child_attrib["selectoptions"+i][0] != selections_'.$js_suffix.'[stockableBlockIndex][i]) {
							break;
						}
					}
					if (totalattribut_'.$js_suffix.'[stockableBlockIndex] == i) {
						found_id = child_id;
						return false;
					}
				});

				if ("disableadd" == stockhandle && stockable_'.$js_suffix.'[found_id].in_stock <= 0) {
					$(".addtocart-bar>span").remove();
					$(".addtocart-bar>div").remove();
					$(".addtocart-bar>a.notify").remove();
					$(".addtocart-bar").append(\'<a href="ind\'+\'ex.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=\' + found_id + \'" class="notify">' . JText::_('COM_VIRTUEMART_CART_NOTIFY') . '</a>\');
				} else {
					var quantity = $(".addtocart-bar .quantity-input").val();
					$(".addtocart-bar>span").remove();
					$(".addtocart-bar>div").remove();
					$(".addtocart-bar>a.notify").remove();
					$(".addtocart-bar").append(\'<span class="quantity-box"><input type="text" class="quantity-input js-recalculate" name="quantity[]" value="\' + quantity + \'" /></span><span class="quantity-controls js-recalculate"><input type="button" class="quantity-controls quantity-plus" /><input type="button" class="quantity-controls quantity-minus" /></span><span class="addtocart-button"><input type="submit" name="addtocart" class="addtocart-button" value="'.JText::_('COM_VIRTUEMART_CART_ADD_TO').'" title="'.JText::_('COM_VIRTUEMART_CART_ADD_TO').'" /></span><div class="clear"></div>\');
					Virtuemart.product($("form.product"));
				}

				$(".availability").remove();

				if ("risetime" == stockhandle && stockable_'.$js_suffix.'[found_id].product_availability) {
					$(".addtocart-area").after(\'<div class="availability">\' + stockable_'.$js_suffix.'[found_id].product_availability + \'</div>\');
				}

				// recalculate the price by found product child id;
				formProduct = Opt.parents("form.product");
				virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
				//formProduct.find("#selectedStockable").remove();
				//formProduct.append(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$row.'][\'+found_id+\']">\');
				formProduct.find(\'input[name*="customPlugin['.$field->virtuemart_customfield_id.']['.$this->_name.'][child_id]"]\').val(found_id);

				//(\'<input id="stockableChild" type="hidden" value="\'+customfield_id[found_id]+\'" name="customPrice['.$row.'][\'+found_id+\']">\');
				Virtuemart.setproducttype(formProduct,virtuemart_product_id);
			}
			function isChildValid_'.$js_suffix.'(stockableBlockIndex, child_attrib, currentIndex) {
				return_value = true;
				for (var i = currentIndex; i > 0; i--) {
					if (child_attrib["selectoptions"+i][0] != selections_'.$js_suffix.'[stockableBlockIndex][i]) {
						return_value = false;
					}
				}
				return return_value;
			}
			function populateNextSelect_'.$js_suffix.'(stockableBlockIndex, child_attrib, nextIndex) {
				var selectList = $("#stockableBlockIndex_'.$js_suffix.'_"+stockableBlockIndex+" select.attribute_list.customfield_id_'.$js_suffix.'");
				var nextSelect = selectList.eq(nextIndex-1);
				// if the select exists
				if ("undefined" !== typeof(nextSelect) && nextSelect.length > 0) {
					// if it doesn\'t already contain this option, add it
					if (nextSelect.find("option[value=\'" + child_attrib["selectoptions"+nextIndex][0] + "\']").length == 0) {
						nextSelect.append("<option value=\'" + child_attrib["selectoptions"+nextIndex][0] + "\'>" + child_attrib["selectoptions"+nextIndex][1] + "</option>");
					}

					// if there is only one option, make it selected
					if (1 == nextSelect.find("option").length) {
						nextSelect.find("option").attr("selected","selected");
						selections_'.$js_suffix.'[stockableBlockIndex][nextIndex] = child_attrib["selectoptions"+nextIndex][0];
					}
					// if this is the selected value, populate the next select too
					if (nextSelect.val() == child_attrib["selectoptions"+nextIndex][0]) {
						populateNextSelect_'.$js_suffix.'(stockableBlockIndex, child_attrib, nextIndex+1);
					}
				}
			}
		});
		//]]>
		');

		// 'custom_param['.$keys.']'

		//dump($param);
		//"is_variant":"1","attribute1":"Red","attribute2":"20 cm","attribute3":"10","attribute4":"10"

		//echo $plgParam->get('custom_info');
		// Here the plugin values
		//$html =JTEXT::_($param['custom_name']) ;
		//$html.=': <input type="text" value="" size="'.$param['custom_name'].'" name="customPlugin['.$row.'][comment]"><br />';


		return true;
	}

	function plgVmOnDisplayProductFE( $product, &$idx,&$group){}
	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCartModule( $product, $row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		foreach ($plgParam as $k => $attributes) {
			foreach ($attributes as $k => $attribute) {
				if ($k =='child_id') continue;
				$html .='<span class="stockablecartvariant_attribute"> '.JText::_($attribute).' </span>';
			}
		}
		return true;
	}

	/**
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product, $row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;
		$html  .= '<div class="stockablecartvariant_attributes">';
		foreach ($plgParam as $attributes) {
			foreach ($attributes as $k => $attribute) {
				if ($k =='child_id') continue;
				$html .='<span class="stockablecartvariant_attribute"> '.JText::_($attribute).' </span>';
			}
		}		// $html .='<span>'.$param->Morecomment.'</span>';
		$html.='</div>';
		return true;
		//vmdebug('stockable attributs',$plgParam);
	}

	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE($item, $row,&$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		return $this->plgVmOnViewCart($item, $row,$html);
	}

	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE($item, $row,&$html) {
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		return $this->plgVmOnViewCart($item, $row,$html);
	}

	function getChilds($child_id = null) {

		if ($child_id) {
			$db = JFactory::getDBO();
			$q = 'SELECT CONCAT( `product_name`, " [' .JText::_('COM_VIRTUEMART_PRODUCT_SKU').'"," : ",`product_sku`,"]") as product_name,`virtuemart_product_id` as id, `product_in_stock` as stock FROM `#__virtuemart_products_'.VMLANG.'` as l '
			. ' JOIN `#__virtuemart_products` AS p using (`virtuemart_product_id`)'
			. 'WHERE `product_parent_id` ='.(int)$child_id ;
			$db->setQuery($q);

			$result = $db->loadObjectList();

			if (!($result)) {
				//JError::raiseWarning(500, $db->getErrorMsg());
				return array();
			} else return $result ;
		} else {
			return array();
		}
	}

	function getFieldId($virtuemart_product_id, $child_id ) {

		$db = JFactory::getDBO();
		$q = 'SELECT cf.* FROM `#__virtuemart_product_customfields` as cf JOIN `#__virtuemart_customs` as c ON `c`.`virtuemart_custom_id` = cf.`virtuemart_custom_id` AND c.`field_type`="G"
			WHERE cf.`virtuemart_product_id` ='.(int)$virtuemart_product_id.' and cf.custom_value='.(int)$child_id ;
		$db->setQuery($q);
		$result = $db->loadObject();
		if (!($result)) {
			//JError::raiseWarning(500, $db->getErrorMsg());
			return false;
		} else return $result ;
	}

	/**
	 * Get the child object for the given ID if it is valid for the config
	 * @author Matt Lewis-Garner
	 */
	function getValideChild($child_id ) {
		$db = JFactory::getDBO();
		$q = 'SELECT `product_sku`,`product_name`,`product_in_stock`,`product_ordered`,`product_availability` FROM `#__virtuemart_products` JOIN `#__virtuemart_products_'.VMLANG.'` as l using (`virtuemart_product_id`) WHERE `published`=1 and `virtuemart_product_id` ='.(int)$child_id ;
		$db->setQuery($q);
		$child = $db->loadObject();
		if ($child) {
			if ('disableit_children' === $this->stockhandle) {
				$stock = $child->product_in_stock - $child->product_ordered ;
				if ($stock>0)return $child ;
				else return false ;
			}
			else return $child ;
		}
		return false ;
	}

	public function plgVmGetProductStockToUpdateByCustom(&$item, $pluginParam, $productCustom) {

		if ($productCustom->custom_element !== $this->_name) return false ;
//vmdebug('$pluginParam',$pluginParam[$this->_name]);
		$item->virtuemart_product_id = (int)$pluginParam[$this->_name]['child_id'];
		return true ;
		// echo $item[0]->virtuemart_product_id;jexit();
	}

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {

// 		return $this->onStoreInstallPluginTable($psType);
	}

	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams('custom', $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	public function plgVmCalculateCustomVariant(&$product, &$productCustomsPrice,$selected){

		if ($productCustomsPrice->custom_element != $this->_name) return false;

		if (!$customPlugin = JRequest::getVar('customPlugin',0)) {
			$customPlugin = json_decode($product->customPlugin,true);
		}
		$selected = $customPlugin[$productCustomsPrice->virtuemart_customfield_id]['stockable']['child_id'];

		$param = json_decode($productCustomsPrice->custom_param,true);
		if ($child = $this->getValideChild($selected)) {
			if ($param['child'][$selected]['custom_price'] !=='') {
				$productCustomsPrice->custom_price = (float)$param['child'][$selected]['custom_price'];
			} else {
				$db = JFactory::getDBO();
				$db->setQuery('SELECT `product_price` FROM `#__virtuemart_product_prices`  WHERE `virtuemart_product_id`="' . (int)$selected . '" ');
				if ($price = $db->loadResult()) $product->product_price = (float)$price;
			}
			return $child;
		}
		else return false;
		// find the selected child

	}
	public function plgVmOnAddToCart(&$product){
		$customPlugin = JRequest::getVar('customPlugin',0);

		if ($customPlugin) {
			$db = JFactory::getDBO();
			$query = 'SELECT  C.* , field.*
				FROM `#__virtuemart_customs` AS C
				LEFT JOIN `#__virtuemart_product_customfields` AS field ON C.`virtuemart_custom_id` = field.`virtuemart_custom_id`
				WHERE `virtuemart_product_id` =' . $product->virtuemart_product_id.' and `custom_element`="'.$this->_name.'"';
			$query .=' and is_cart_attribute = 1';
			$db->setQuery($query);
			$productCustomsPrice = $db->loadObject();
			if (!$productCustomsPrice) return null;
			// if ( !in_array($this->_name,$customPlugin[$productCustomsPrice->virtuemart_custom_id]) ) return false;
			$selected = $customPlugin[$productCustomsPrice->virtuemart_customfield_id]['stockable']['child_id'];

			if (!$child = $this->plgVmCalculateCustomVariant($product, $productCustomsPrice,$selected) ) return false;
			if ($child->product_sku)
				$product->product_sku = $child->product_sku;
			if ($child->product_name)
				$product->product_name = $child->product_name;
			$product->product_in_stock = $child->product_in_stock;
		}
	}

	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}


}

// No closing tag