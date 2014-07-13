<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

ADocument::getInstance()->addScript('angie/js/json.js');
ADocument::getInstance()->addScript('angie/js/ajax.js');
ADocument::getInstance()->addScript('angie/js/finalise.js');

echo $this->loadAnyTemplate('steps/buttons');
echo $this->loadAnyTemplate('steps/steps', array('helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-installers.html#angie-common-finalise'));
?>
<?php
if(isset($this->extra_warning))
{
    echo $this->extra_warning;
}
?>

<?php if ($this->showconfig): ?>
<?php echo $this->loadAnyTemplate('finalise/config'); ?>
<?php endif; ?>

<div class="alert alert-info">
	<p><?php echo AText::_('FINALISE_LBL_PRBASICTS'); ?></p>
	<a href="https://www.akeebabackup.com/documentation/troubleshooter/prbasicts.html">
		<tt>https://www.akeebabackup.com/documentation/troubleshooter/prbasicts.html</tt>
	</a>
</div>

<p>
	<?php echo AText::_('FINALISE_LBL_INSTALLATIONSTILLTHERE'); ?>
</p>
<ul>
	<li><?php echo AText::_('FINALISE_LBL_IFKICKSTART'); ?></li>
	<li>
		<?php echo AText::_('FINALISE_LBL_IFSTANDALONE'); ?>
		<button type="button" class="btn btn-mini btn-danger" id="removeInstallation">
			<span class="icon-white icon-remove"></span>
			<?php echo AText::_('FINALISE_BTN_REMOVEINSTALLATION'); ?>
		</button>
	</li>
	<li><?php echo AText::_('FINALISE_LBL_IFFAILSAFE'); ?></li>
</ul>

<p>
	<?php echo AText::_('FINALISE_LBL_YOUMAYWANTTOPRINT'); ?>
</p>

<div id="error-dialog" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="error-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('FINALISE_HEADER_ERROR') ?></h3>
	</div>
	<div class="modal-body" id="error-message">
		<p><?php echo AText::_('FINALISE_LBL_ERROR') ?></p>
	</div>
</div>

<div id="success-dialog" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="success-btn-modalclose">&times;</button>
		<h3><?php echo AText::_('FINALISE_HEADER_SUCCESS') ?></h3>
	</div>
	<div class="modal-body">
		<p>
			<?php echo AText::sprintf('FINALISE_LBL_SUCCESS', 'https://www.akeebabackup.com/documentation/troubleshooter/prbasicts.html') ?>
		</p>
		<a class="btn btn-success" href="<?php echo AUri::base() . '../index.php' ?>">
			<span class="icon-white icon-forward"></span>
			<?php echo AText::_('FINALISE_BTN_VISITFRONTEND'); ?>
		</a>
	</div>
</div>

