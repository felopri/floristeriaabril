<?php

/**
 *
 * Main file
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;
// enable showing errors in PHP
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors','On');

// include framework classes and files
require_once('lib/framework/gk.const.php');
require_once('lib/framework/gk.parser.php');
require_once('lib/gk.framework.php');
// run the framework
$tpl = new GKTemplate($this, $GK_TEMPLATE_MODULE_STYLES);

/* End of the file - index.php */