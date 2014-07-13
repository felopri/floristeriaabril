<?php
/**
*
* The main product images
*
* @package	VirtueMart
* @subpackage Product
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: product_edit_images.php 5887 2012-04-14 13:16:20Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


?>
<div class="col50">
	<div class="selectimage">
	<?php
		if(empty($this->product->images[0]->virtuemart_media_id)) $this->product->images[0]->addHidden('file_is_product_image','1');
		if (!empty($this->product->virtuemart_media_id)) echo $this->product->images[0]->displayFilesHandler($this->product->virtuemart_media_id,'product');
		else echo $this->product->images[0]->displayFilesHandler(null,'product');
	?>
	</div>
	<div>
		<?php
			//echo '<div width="100px">'.JText::_('COM_VIRTUEMART_RTB_AD').'</div>';
			$jlang =JFactory::getLanguage();
			$tag = $jlang->getTag();
			$imgUrl = 'http://www.removethebackground.de//images/logoremove.png';
			if(strpos($tag,'de')!==FALSE){
				$url = 'http://www.removethebackground.de/virtuemart.aspx';
			} else if(strpos($tag,'fr')!==FALSE){
				$url = 'http://www.removethebackground.fr/virtuemart.aspx';
			} else {
				$url = 'http://www.removethebackground.co.uk/virtuemart.aspx';
			}
			echo '<a href="'.$url.'" target="_blank" alt="'.JText::_('COM_VIRTUEMART_RTB_AD').'"><img  height="60" src="'.$imgUrl.'" title="'.JText::_('COM_VIRTUEMART_RTB_AD').'"></a>';
		?>
	</div>
</div>
