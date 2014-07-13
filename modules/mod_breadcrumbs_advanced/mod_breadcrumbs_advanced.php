<?php
/**
* @version		$Id: mod_breadcrumbs_adv.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* Modified by UWiX - June 2011
*
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

// Get the breadcrumbs
$list	= modBreadCrumbsAdvHelper::getList($params);
$count	= count($list);

// Get the configuration parameters
$showLast = $params->get('showLast', 1);
$cutLast = $params->get('cutLast', 0);
$cutAt = $params->get('cutAt', 20);
$cutChar = $params->get('cutChar', JText::_('...'));
$showHome = $params->get('showHome', 1);
$homePath = str_replace( array('http://','https://','www.'), '', $params->get('homepath', '') );
$clickHome= $params->get('clickHome', 0);

// Set the default separator
$separator = modBreadCrumbsAdvHelper::setSeparator( $params->get( 'separator' ));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_breadcrumbs_advanced', 'default');
