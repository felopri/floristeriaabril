<?php
/**
*
* Modify user form view, User info
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit_user.php 3438 2011-06-06 20:37:06Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); ?>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LEGEND_USERDETAILS')." "; //echo JURI::root() ?>
	</legend>
	<table class="admintable" cellspacing="1">

<?php /*<tr>
			<td width="150" class="key">
				<label for="name">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_NAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $this->userDetails->JUser->get('name'); ?>" />
			</td>
		</tr>
*/ ?>
		<tr>
			<td class="key">
				<label for="username">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_USERNAME'); ?>
				</label>
			</td>
			<td>
				<?php
					// Only admins can change other users, and only admins can change usernames
					if ( $this->lists['current_id'] == $this->userDetails->JUser->get('id')
						// but new users must be able to choose a username
						&& $this->userDetails->JUser->get('id') > 0) :
				?>
					<input type="hidden" name="username" id="username" value="<?php echo $this->userDetails->JUser->get('username'); ?>" />
					<?php echo $this->userDetails->JUser->get('username');?>
				<?php  else : ?>
					<input type="text" name="username" id="username" class="inputbox" size="40" value="<?php echo $this->userDetails->JUser->get('username'); ?>" autocomplete="off" />
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="email">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_EMAIL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $this->userDetails->JUser->get('email'); ?>" />
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_NEWPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password" id="password" size="40" value=""/>
			</td>
		</tr>

		<tr>
			<td class="key">
				<label for="password2">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_VERIFYPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" id="password2" size="40" value=""/>
			</td>
		</tr>

		<tr>
			<td valign="top" class="key">
				<label for="gid">
					<?php echo JText::_('COM_VIRTUEMART_USER_FORM_GROUP'); ?>
				</label>
			</td>
			<td>
				<?php echo $this->lists['gid']; ?>
			</td>
		</tr>

		<?php if ($this->lists['canBlock']) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_BLOCKUSER'); ?>
			</td>
			<td>
				<?php echo $this->lists['block']; ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ($this->lists['canSetMailopt']) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_RECEIVESYSTEMEMAILS'); ?>
			</td>
			<td>
				<?php echo $this->lists['sendEmail']; ?>
			</td>
		</tr>

		<?php else : ?>
			<input type="hidden" name="sendEmail" value="0" />
		<?php endif; ?>

		<?php if( $this->userDetails->JUser ) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_REGISTERDATE'); ?>
			</td>
			<td>
				<?php echo $this->userDetails->JUser->get('registerDate');?>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LASTVISITDATE'); ?>
			</td>
			<td>
				<?php echo $this->userDetails->JUser->get('lastvisitDate'); ?>
			</td>
		</tr>
		<?php endif; ?>

	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('COM_VIRTUEMART_USER_FORM_LEGEND_PARAMETERS'); ?>
		</legend>
	<table class="admintable" cellspacing="1">
		<tr>
			<td>
			<?php
				if (is_callable(array($this->lists['params'], 'render'))) {
					echo $this->lists['params']->render('params');
				}
			?>
			</td>
		</tr>
	</table>
</fieldset>

<input type="hidden" name="my_virtuemart_user_id" value="<?php echo $this->lists['current_id']; ?>" />
<input type="hidden" name="virtuemart_user_id" value="<?php echo $this->userDetails->JUser->get('id'); ?>" />
