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
ADocument::getInstance()->addScript('angie/platform/js/main.js');
$url = 'index.php';
ADocument::getInstance()->addScriptDeclaration(<<<ENDSRIPT
var akeebaAjax = null;
$(document).ready(function(){
	akeebaAjax = new akeebaAjaxConnector('$url');
});
ENDSRIPT
);

ADocument::getInstance()->appendButton(
	'GENERIC_BTN_STARTOVER', 'index.php?view=main&task=startover', 'danger', 'white fire'
);
ADocument::getInstance()->appendButton(
	'GENERIC_BTN_RECHECK', 'javascript:mainGetPage();', 'warning', 'white retweet'
);

echo $this->loadAnyTemplate('steps/buttons');
?>
<noscript>
<div class="alert alert-error">
	<h3><?php echo AText::_('MAIN_HEADER_NOJAVASCRIPT') ?></h3>
	<p><?php echo AText::_('MAIN_LBL_NOJAVASCRIPT') ?></p>
</div>
</noscript>
<div class="well" style="text-align: center;">
	<h1><?php echo AText::_('MAIN_HEADER_INITIALISING') ?></h1>
	<p>
		<img src="template/angie/img/loading_big.gif" />
	</p>
	<p>
		<?php echo AText::_('MAIN_LBL_INITIALISINGWAIT') ?>
	</p>
</div>