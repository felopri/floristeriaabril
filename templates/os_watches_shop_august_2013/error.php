<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$params = JFactory::getApplication()->getTemplate(true)->params;
$logo =  $params->get('logo');
$showRightColumn = 0;
$showleft = 0;
$showbottom = 0;

// get params
$color			= $params->get('templatecolor');
$navposition	= $params->get('navposition');

//get language and direction
$doc = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title><?php echo $this->error->getCode(); ?> - <?php echo $this->title; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/bootstrap/css/bootstrap-responsive.css" />
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/bootstrap/js/bootstrap.js"></script>

</head>
<body class="error">
<center>
    <div class="errorbox">

    <div class="block">
	<img  src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/error_bg.png" />
	<h2>Page not found</h2>
    </div>

	<p>
	    Sorry! The page you are looking for cannot be found. Please use 
	    the provided search box to find what you are looking for, 
	    click on our top navigational menu, or 
	    <a onclick="window.history.back()">go back.</a>
	</p>
    </div>
</center>
</body>
</html>
