<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

echo $this->loadAnyTemplate('steps/steps', array(
	'helpurl' => 'https://www.akeebabackup.com/documentation/solo/angie-joomla.html#angie-joomla-first',
	'videourl' => 'https://www.akeebabackup.com/videos/1204-video-tutorials/akeeba-backup-course-with-brian-teeman/1508-abt06-restoring-to-new-server-using-kickstart.html'
));
?>

<!--[if IE]>
<div class="margin: 20px; padding: 20px; background-color: yellow; border: 5px solid red; font-size: 14pt;">
<?php echo AText::sprintf('MAIN_LBL_ANCIENTIENOTICE', 'http://www.google.com/chromeframe') ?>
</div>
<![endif]-->

<?php if ($this->restoringToDifferentPHP): ?>
<div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php echo AText::sprintf('MAIN_LBL_DIFFERENTPHPVERSION', $this->extraInfo['php_version']['current'], PHP_VERSION, PHP_VERSION); ?>
</div>
<?php endif; ?>

<?php if ($this->restoringToDifferentHost): ?>
<div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php echo AText::_('MAIN_LBL_DIFFERENTHOST'); ?>
</div>
<?php endif; ?>

<?php if (!$this->reqMet): ?>
<div class="alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<?php echo AText::_('MAIN_LBL_REQUIREDREDTEXT'); ?>
</div>
<?php endif; ?>

<div class="row-fluid">
	<div class="span6">
		<h3><?php echo AText::_('MAIN_HEADER_REQUIRED') ?></h3>
		<p><?php echo AText::_('MAIN_LBL_REQUIRED') ?></p>
		<table class="table-striped" width="100%">
			<thead>
				<tr>
					<th><?php echo AText::_('MAIN_LBL_SETTING') ?></th>
					<th><?php echo AText::_('MAIN_LBL_CURRENT_SETTING') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->reqSettings as $option): ?>
				<tr>
					<td>
						<label>
							<?php echo $option['label']; ?>
						</label>
						<?php if (array_key_exists('notice',$option)): ?>
						<div class="help-block">
							<?php echo $option['notice']; ?>
						</div>
						<?php endif; ?>
					</td>
					<td>
						<span class="label label-<?php echo $option['current'] ? 'success' : ($option['warning'] ? 'warning' : 'error'); ?>">
							<?php echo $option['current'] ? AText::_('GENERIC_LBL_YES') : AText::_('GENERIC_LBL_NO'); ?>
						</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="span6">
		<h3><?php echo AText::_('MAIN_HEADER_RECOMMENDED') ?></h3>
		<p><?php echo AText::_('MAIN_LBL_RECOMMENDED') ?></p>
		<table class="table-striped" width="100%">
			<thead>
				<tr>
					<th><?php echo AText::_('MAIN_LBL_SETTING') ?></th>
					<th><?php echo AText::_('MAIN_LBL_RECOMMENDED_VALUE') ?></th>
					<th><?php echo AText::_('MAIN_LBL_CURRENT_SETTING') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->recommendedSettings as $option): ?>
				<tr>
					<td>
						<label>
							<?php echo $option['label']; ?>
						</label>
					</td>
					<td>
						<span class="label">
							<?php echo $option['recommended'] ? AText::_('GENERIC_LBL_ON') : AText::_('GENERIC_LBL_OFF'); ?>
						</span>
					</td>
					<td>
						<span class="label label-<?php echo ($option['current'] == $option['recommended']) ? 'success' : 'warning'; ?>">
							<?php echo $option['current'] ? AText::_('GENERIC_LBL_ON') : AText::_('GENERIC_LBL_OFF'); ?>
						</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="row-fluid">
	<div class="span6">
		<h3><?php echo AText::_('MAIN_HEADER_EXTRAINFO') ?></h3>
		<?php if (empty($this->extraInfo)): ?>
		<div class="alert">
			<?php echo AText::_('MAIN_ERR_EXTRAINFO') ?>
		</div>
		<?php else: ?>
		<p><?php echo AText::_('MAIN_LBL_EXTRAINFO') ?></p>
		<table class="table-striped" width="100%">
			<thead>
				<tr>
					<th><?php echo AText::_('MAIN_LBL_SETTING') ?></th>
					<th><?php echo AText::_('MAIN_LBL_BACKUP_SETTING') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->extraInfo as $option): ?>
				<tr>
					<td>
						<label>
							<?php echo $option['label']; ?>
						</label>
					</td>
					<td>
						<span class="label">
							<?php echo $option['current'] ?>
						</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<?php if (@file_exists('README.html')): ?>
		<button type="button" onclick="mainOpenReadme()" class="btn btn-info" data-toggle="modal" data-target="#readmeDialog">
			<span class="icon-white icon-file"></span>
			<?php echo AText::_('MAIN_BTN_OPENREADME') ?>
		</button>
		<br/>
		<?php echo AText::_('MAIN_LBL_OPENREADME'); ?>
		<?php endif; ?>
	</div>
	<div class="span6">
		<h3><?php echo AText::_('MAIN_HEADER_SITEINFO') ?></h3>
		<p><?php echo AText::_('MAIN_LBL_SITEINFO') ?></p>
		<table class="table-striped" width="100%">
			<tbody>
				<tr>
					<td>
						<label><?php echo AText::_('MAIN_LBL_SITE_JOOMLA') ?></label>
					</td>
					<td><?php echo $this->joomlaVersion ?></td>
				</tr>
				<tr>
					<td>
						<label><?php echo AText::_('MAIN_LBL_SITE_PHP') ?></label>
					</td>
					<td><?php echo PHP_VERSION ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="modal hide fade" id="readmeDialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php echo AText::_('MAIN_HEADER_OPENREADME') ?></h3>
	</div>
	<div class="modal-body">
		<iframe src="README.html" width="100%" height="350"></iframe>
	</div>
</div>