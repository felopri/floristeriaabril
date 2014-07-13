<?php

// Here you can modify the navigation of the website

// No direct access.
defined('_JEXEC') or die; 

$logo_image = $this->getParam('logo_image', '');

if(($logo_image == '') || ($this->getParam('logo_type', '') == 'css')) {
     $logo_image = $this->URLtemplate() . '/images/logo.png';
} else {
     $logo_image = $this->URLbase() . $logo_image;
}

$logo_text = $this->getParam('logo_text', '');
$logo_slogan = $this->getParam('logo_slogan', '');
//
$user = JFactory::getUser();
$userID = $user->get('id');
$btn_login_text = ($userID == 0) ? JText::_('GK_YOURSHOP_LOGIN') : JText::_('GK_YOURSHOP_ACCOUNT');

?>

<div id="gkPageTop" class="gkMain <?php echo $this->generatePadding('gkPageTop'); ?>">
<?php if ($this->getParam('logo_type', 'image')!=='none'): ?>
     <?php if($this->getParam('logo_type', 'image') == 'css') : ?>
     <h1 id="gkLogo">
          <a href="./" class="cssLogo"></a>
          <span><?php echo $this->getParam('logo_text', ''); ?></span>
     </h1>
     <?php elseif($this->getParam('logo_type', 'image')=='text') : ?>
     <h1 id="gkLogo" class="text">
         <a href="./">
              <span><?php echo $this->getParam('logo_text', ''); ?></span>
               <small class="gkLogoSlogan"><?php echo $this->getParam('logo_slogan', ''); ?></small>
         </a>
     </h1>
    <?php elseif($this->getParam('logo_type', 'image')=='image') : ?>
    <h1 id="gkLogo">
          <a href="./">
          <img src="<?php echo $logo_image; ?>" alt="<?php echo $this->getPageName(); ?>" />
          </a>
     </h1>
     <?php endif; ?>
<?php endif; ?>
	<?php if($this->modules('cart')): ?>
    <div id="gkCartBtn">
      <h2><?php echo JText::_('TPL_GK_LANG_CART'); ?></h2>
  	  <div id="gkItems">
  		  [ <strong>0</strong> ] <?php echo JText::_('TPL_GK_LANG_ITEMS'); ?>
  	  </div>
  		  <a href="index.php?option=com_virtuemart&view=cart" class="button"><?php echo JText::_('TPL_GK_LANG_GO_TO_CHECKOUT'); ?></a>
    </div>
    <div id="gkCart">
      <jdoc:include type="modules" name="cart" style="<?php echo $this->module_styles['cart']; ?>" />
    </div>
  	<?php endif; ?>