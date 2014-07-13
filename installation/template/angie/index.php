<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

?>
<html>
<head>
	<title>ANGIE - Akeeba Next Generation Installation Engine v. <?php echo AKEEBA_VERSION ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php include __DIR__ . '/php/head.php' ?>
</head>
<body>
<?php if (AApplication::getInstance()->getInput()->getCmd('tmpl', '') != 'component'): ?>
	<div id="wrap">
		<div class="navbar navbar-inverse navbar-static-top">
			<div class="navbar-inner">
				<div class="container">
					<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="brand" href="#">ANGIE – Akeeba Next Generation Installer Engine v.<?php echo AKEEBA_VERSION ?></a>
					<div class="nav-collapse collapse pull-right btn-group">
						<?php include __DIR__ . '/php/buttons.php'; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
<?php endif; ?>
			<?php include __DIR__ . '/php/messages.php' ?>
			<?php echo $this->getBuffer() ?>
<?php if (AApplication::getInstance()->getInput()->getCmd('tmpl', '') != 'component'): ?>
		</div>
	</div>
	<div id="footer">
		<div class="container">
			<p class="muted credit">
				Copyright &copy;2006 &ndash; <?php echo date('Y') ?> Akeeba Ltd. All rights reserved.<br/>
				ANGIE is Free Software distributed under the
				<a href="http://www.gnu.org/licenses/gpl.html">GNU GPL version 3</a> or any later version published by the FSF.
			</p>
		</div>
	</div>
<?php endif; ?>
</body>
</html>