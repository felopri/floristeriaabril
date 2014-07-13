<?php
	defined('_JEXEC') or die();
	$class='vmcustom-textinput';
//vmdebug('$this',$viewData[0]);
	//if ($field->custom_price_by_letter) $class='vmcustom-textinput';?>

	<input class="<?php echo $class ?>" type="text" value="" size="<?php echo $this->params->custom_size ?>" name="customPlugin[<?php echo $viewData[0]->virtuemart_customfield_id ?>][<?php echo $this->_name?>][comment]"><br />
<?php
	// preventing 2 x load javascript
	static $textinputjs;
	if ($textinputjs) return true;
	$textinputjs = true ;
	//javascript to update price
	$document = JFactory::getDocument();
	$document->addScriptDeclaration('
/* <![CDATA[ */
jQuery(document).ready( function($) {
	jQuery(".vmcustom-textinput").keyup(function() {
			formProduct = $(this).parents("form.product");
			virtuemart_product_id = formProduct.find(\'input[name="virtuemart_product_id[]"]\').val();
		Virtuemart.setproducttype(formProduct,virtuemart_product_id);
		});

});
/* ]]> */
	');