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
if (!defined('DS')) { 
define('DS', DIRECTORY_SEPARATOR); 
}
// count instances
if (!isset($GLOBALS['slick_logins'])) {
	$GLOBALS['slick_logins'] = 1;
} else {
	$GLOBALS['slick_logins']++;
}

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$params->def('greeting', 1);

$type 	= modSlickloginHelper::getType();
$return	= modSlickloginHelper::getReturnURL($params, $type);

$user =& JFactory::getUser();

// init vars
$sl_view               = $params->get('sl_view', 'horizontal');
$pretext               = $params->get('pretext', '');
$posttext              = $params->get('posttext', '');
$registermode          = $params->get('registermode', '');
$auto_remember         = $params->get('auto_remember', '1');
$lost_password         = $params->get('lost_password', '1');
$lost_username         = $params->get('lost_username', '1');
$registration          = $params->get('registration', '1');
$account               = $params->get('account', '1');
$accountlabel          = $params->get('accountlabel', '');

// css parameters
$slicklogin_id           = $GLOBALS['slick_logins'];

$module_base           = JURI::base() . 'modules/mod_slick_login_vm/';

$document =& JFactory::getDocument();

switch ($sl_view) {
	case "vertical":
		require(JModuleHelper::getLayoutPath('mod_slick_login_vm', 'vertical'));
		break;
	default:
		require(JModuleHelper::getLayoutPath('mod_slick_login_vm', 'horizontal'));
		
}

$document->addStyleSheet($module_base . 'css/style.css');