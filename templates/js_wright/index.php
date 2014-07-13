<?php
/**
 * @copyright	Copyright (C) 2005 - 2011 Joomlashack / Meritage Assets
 * @author		Jeremy Wilken - Joomlashack
 * @package		Wright
 *
 * Expected to see a template file here? Well this template is just a little
 * different. In order to provide some extra features, we've altered a few
 * little things about how Joomla templates work.
 *
 * See usage and customization information at
 * http://wright.joomlashack.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the framework
require(dirname(__FILE__).'/'.'wright'.'/'.'wright.php');

// Initialize the framework and
$tpl = Wright::getInstance();
$tpl->display();