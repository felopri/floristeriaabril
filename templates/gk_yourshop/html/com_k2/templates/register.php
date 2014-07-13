<?php

/**

 * @version		$Id: register.php 785 2011-04-28 12:39:17Z lefteris.kavadas $

 * @package		K2

 * @author		JoomlaWorks http://www.joomlaworks.gr

 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.

 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html

 */



// no direct access

defined('_JEXEC') or die('Restricted access');



?>



<script type="text/javascript">

	//<![CDATA[

	window.onDomReady(function(){

		document.formvalidator.setHandler('passverify', function (value){

			return ($('password').value == value);

		});

	});

	//]]>

</script>



<!-- K2 user register form -->



<?php if(isset($this->message)) $this->display('message'); ?>



<form action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" method="post" id="josForm" name="josForm" class="form-validate">



  <?php if ( $this->params->def( 'show_page_title', 1 ) ) : ?>

  <div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">

  	<?php echo $this->escape($this->params->get('page_title')); ?>

  </div>

  <?php endif; ?>



  <div id="k2Container" class="k2AccountPage">



	  <table cellpadding="0" cellspacing="0">

	  	<tr>

	  		<th colspan="2"><?php echo JText::_( 'K2_ACCOUNT_DETAILS' ); ?></th>

	  	</tr>

	    <tr>
	      <td><label id="namemsg" for="name"><?php echo JText::_('K2_NAME'); ?></label></td>
	      <td><input type="text" name="<?php echo (K2_JVERSION=='16')?'jform[name]':'name'?>" id="name" size="40" value="<?php echo $this->escape($this->user->get( 'name' )); ?>" class="inputbox required" maxlength="50" />
	        * </td>
	    </tr>
	    <tr>
	      <td><label id="usernamemsg" for="username"><?php echo JText::_('K2_USER_NAME'); ?></label></td>
	      <td><input type="text" id="username" name="<?php echo (K2_JVERSION=='16')?'jform[username]':'username'?>" size="40" value="<?php echo $this->escape($this->user->get( 'username' )); ?>" class="inputbox required validate-username" maxlength="25" />
	        * </td>
	    </tr>
	    <tr>
	      <td><label id="emailmsg" for="email"><?php echo JText::_('K2_EMAIL'); ?></label></td>
	      <td><input type="text" id="email" name="<?php echo (K2_JVERSION=='16')?'jform[email1]':'email'?>" size="40" value="<?php echo $this->escape($this->user->get( 'email' )); ?>" class="inputbox required validate-email" maxlength="100" />
	        * </td>
	    </tr>
        
        <?php if(K2_JVERSION == '16'):?>
	   
        <tr>
	   
          <td><label id="email2msg" for="email2"><?php echo JText::_('K2_CONFIRM_EMAIL'); ?></label></td>
	   
          <td><input type="text" id="email2" name="jform[email2]" size="40" value="" class="inputbox required validate-email" maxlength="100" />
	   
            * </td>
	   
        </tr>	    
	   
        <?php endif;?>

	   <tr>
	     
          <td><label id="pwmsg" for="password"><?php echo JText::_('K2_PASSWORD'); ?></label></td>
	     
          <td><input class="inputbox required validate-password" type="password" id="password" name="<?php echo (K2_JVERSION=='16')?'jform[password1]':'password'?>" size="40" value="" />
	     
            * </td>
	    
        </tr>
	    
        <tr>
	    
          <td><label id="pw2msg" for="password2"><?php echo JText::_('K2_VERIFY_PASSWORD'); ?></label></td>
	    
          <td><input class="inputbox required validate-passverify" type="password" id="password2" name="<?php echo (K2_JVERSION=='16')?'jform[password2]':'password2'?>" size="40" value="" />
	    
            * </td>
	    
        </tr>
	  	
        <tr>

	  		<th colspan="2"><?php echo JText::_( 'K2_PERSONAL_DETAILS' ); ?></th>

	  	</tr>

			<!-- K2 attached fields -->

	    <tr>

	      <td><label id="gendermsg" for="gender"><?php echo JText::_( 'K2_GENDER' ); ?></label></td>

	      <td><?php echo $this->lists['gender']; ?></td>

	    </tr>

	    <tr>

	      <td><label id="descriptionmsg" for="description"><?php echo JText::_( 'K2_DESCRIPTION' ); ?></label></td>

	      <td><?php echo $this->editor; ?></td>

	    </tr>

	    <tr>

	      <td><label id="imagemsg" for="image"><?php echo JText::_( 'K2_USER_IMAGE_AVATAR' ); ?></label></td>

	      <td><input type="file" id="image" name="image"/>

	        <?php if ($this->K2User->image): ?>

	        <img class="k2AdminImage" src="<?php echo JURI::root().'media/k2/users/'.$this->K2User->image; ?>" alt="<?php echo $this->user->name; ?>" />

	        <input type="checkbox" name="del_image" id="del_image" />

	        <label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>

	        <?php endif; ?></td>

	    </tr>

	    <tr>

	      <td><label id="urlmsg" for="url"><?php echo JText::_( 'K2_URL' ); ?></label></td>

	      <td><input type="text" size="50" value="<?php echo $this->K2User->url; ?>" name="url" id="url"/></td>

	    </tr>

	    <?php if(count(array_filter($this->K2Plugins))): ?>

	    <!-- K2 Plugin attached fields -->

	  	<tr>

	  		<th colspan="2"><?php echo JText::_( 'K2_ADDITIONAL_DETAILS' ); ?></th>

	  	</tr>

	    <?php foreach ($this->K2Plugins as $K2Plugin):?>

	    <?php if(!is_null($K2Plugin)): ?>

	    <tr>

	      <td colspan="2"><?php echo $K2Plugin->fields; ?></td>

	    </tr>

	    <?php endif;?>

	    <?php endforeach; ?>

	    <?php endif; ?>

	  </table>


<?php if($this->K2Params->get('recaptchaOnRegistration') && $this->K2Params->get('recaptcha_public_key')): ?>
		<label class="formRecaptcha"><?php echo JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW'); ?></label>
		<div id="recaptcha"></div>
		<?php endif; ?>
		
		<div class="k2AccountPageNotice"><?php echo JText::_('K2_REGISTER_REQUIRED'); ?></div>
		<div class="k2AccountPageUpdate">
			<button class="button validate" type="submit">
				<?php echo JText::_('K2_REGISTER'); ?>
			</button>
		</div>



  </div>


  <input type="hidden" name="option" value="<?php echo (K2_JVERSION=='16')?'com_users':'com_user'?>" />
  
  <input type="hidden" name="task" value="<?php echo (K2_JVERSION=='16')?'registration.register':'register_save'?>" />
  
  <input type="hidden" name="id" value="0" />
  
  <input type="hidden" name="gid" value="0" />
  
  <input type="hidden" name="K2UserForm" value="1" />
  
  <?php echo JHTML::_( 'form.token' ); ?>

</form>

