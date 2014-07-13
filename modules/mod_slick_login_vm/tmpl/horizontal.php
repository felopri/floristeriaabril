<?php
/*
* Pixel Point Creative - Slick Login plus VirtueMart
* License: GNU General Public License version 2 http://www.gnu.org/copyleft/gpl.html
* Copyright (c) 2012 Pixel Point Creative LLC.
* More info at http://www.pixelpointcreative.com
* Review our terms/license here: http://pixelpointcreative.com/terms.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>


<?php if($type == 'logout') : ?>
<form action="index.php" method="post" name="login" id="login-form">
<?php else : ?>
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
						' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
						' var modlogin = 1;';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration( $langScript );
		JHTML::_('script', 'openid.js');
endif; ?>
<form action="<?php echo JRoute::_( 'index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="login-form">
<?php endif; ?>

<span class="<?php echo $sl_view ?>" style="display: block;">
	<span class="slick_login_vm">
	
		<?php if($type == 'logout') : ?>
		<span class="logout">
		
			<?php if ($params->get('greeting')) : ?>
			<span class="greeting" ><?php echo JText::sprintf( 'HINAME', $user->get('username') ); ?></span>
			<?php endif; ?>
    


<?php if ($params->get('account')) : ?>
<span class="account"><a href="index.php?option=com_virtuemart&view=user&layout=edit"><?php echo JText::_('ACCOUNTLABEL'); ?></a></span>
<?php endif; ?>


			
			<span class="logout-button-icon">
			<button name="Submit" type="submit" title="<?php echo JText::_('BUTTON_LOGOUT'); ?>"><?php echo JText::_('BUTTON_LOGOUT'); ?></button>
			</span>
		
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="user.logout" />
			<input type="hidden" name="return" value="<?php echo $return; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</span>
		
		<?php else : ?>
		<span class="login">
		
			<div style="float:left;clear:both;width:100%;"><?php echo $params->get('pretext'); ?></div>
			
			<span class="username">
			<input type="text" name="username" size="18" alt="<?php echo JText::_( 'Username' ); ?>" value="<?php echo JText::_( 'Username' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'Username' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'Username' ); ?>') this.value='';" />
			</span>
			<span class="password">
			<input type="password" name="password" size="10" alt="<?php echo JText::_( 'Password' ); ?>" value="<?php echo JText::_( 'Password' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'Password' ); ?>') this.value='';" />
			</span>

			<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<input type="hidden" name="remember" value="yes" />
			<?php endif; ?>
			
			<span class="login-button-icon">
			<button name="Submit" type="submit" title="<?php echo JText::_('BUTTON_LOGIN'); ?>"></button>
			</span>
			
			<?php if ( $lost_password ) { ?>
			<span class="lostpassword">
			<a href="<?php echo JRoute::_( 'index.php?option=com_users&view=reset' ); ?>" 
			title="<?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?>"><?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></a>

			</span>

			<?php } ?>

			

			<?php if ( $lost_username ) { ?>

			<span class="lostusername">

<a href="<?php echo JRoute::_(
'index.php?option=com_users&view=remind' ); ?>" title="<?php
echo JText::_('FORGOT_YOUR_USERNAME'); ?>"><?php
echo JText::_('FORGOT_YOUR_USERNAME'); ?></a>

			</span>

			<?php } ?>

			<?php

			$usersConfig = &JComponentHelper::getParams( 'com_users' );

			if ($usersConfig->get('allowUserRegistration') && $registration) { ?>

<span class="registration">
<?php if ($registermode == "vm") { ?>
<a href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user' ); ?>"
title="<?php echo JText::_( 'REGISTER'); ?>"><?php echo JText::_( 'REGISTER'); ?></a>
<?php } ?>

<?php if ($registermode == "joomla") { ?>
<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration' ); ?>"
title="<?php echo JText::_( 'REGISTER'); ?>"><?php echo JText::_( 'REGISTER'); ?></a>
<?php } ?>

</span>
<?php } ?>
			<div style="float:left;clear:both;width:100%;"><?php echo $params->get('posttext'); ?></div>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="user.login" />
			<input type="hidden" name="return" value="<?php echo $return; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>

		</span>
		<?php endif; ?>

	</span>
</span>
</form>