<?php
/**
* @file
* @brief    sigplus Image Gallery Plus image download helper
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

define('JPATH_ROOT', dirname(dirname(dirname(dirname(__FILE__)))) );  // if download.php is in /portal/plugins/content/sigplus, JPATH_ROOT will be set to /portal
// phpinfo(INFO_VARIABLES); exit;

/**
* Displays a custom critical HTTP 404 "Not Found" error message.
*/
function http_critical_error($message) {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	header('Status: 404 Not Found');
?>
<html>
<head>
<title>Image not found</title>
</head>
<body>
<h1>Image not found</h1>
<p><?php print $message; ?></p>
<hr/>
<p><address><a href="http://hunyadi.info.hu/projects/sigplus">sigplus Image Gallery Plus Joomla-plug-in</a><?php if (isset($_SERVER['HTTP_HOST'])) { print ' at '.$_SERVER['HTTP_HOST']; } ?></address></p>
</body>
</html>
<?php
	exit;
}

/**
* Displays a critical HTTP 404 "Not found" error message.
*/
function http_not_found($filename) {
	http_critical_error('The requested image file '.($filename ? '<kbd>'.$filename.'</kbd> ' : '').'is not available on the server.');
}

/**
* Extracts image relative URL from request URL query string.
*/
function http_query_string_url() {
	// obtain path from query string variable
	if (!isset($_GET['imgurl'])) {
		return false;
	}
	return trim($_GET['imgurl'], '\\/');
}

/**
* Extracts image relative URL from request URL PATH_INFO.
*/
function http_path_info_url() {
	// extract path from URL
	if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
		$pathinfo = $_SERVER['PATH_INFO'];  // contains leading slash
	} elseif (isset($_SERVER['ORIG_PATH_INFO']) && !empty($_SERVER['ORIG_PATH_INFO'])) {
		$pathinfo = $_SERVER['ORIG_PATH_INFO'];
	} else {
		return false;
	}

	$self = basename(__FILE__);
	$selfstrpos = strpos($pathinfo, $self);  // some systems include download.php in PATH_INFO
	if ($selfstrpos !== false) {
		$url = substr($pathinfo, $selfstrpos + strlen($self));  // remove download.php
	} else {
		$url = $pathinfo;
	}
	return trim($url, '\\/');
}

// check hash string
if (!isset($_GET['h']))
	http_critical_error('The image hash string to validate the download is missing from the request URL.');

// perform authentication if applicable
if (isset($_GET['a'])) {  // use Joomla authentication to check if user is logged in
	define('_JEXEC', 1);
	require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'defines.php';
	require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'framework.php';

	$mainframe =& JFactory::getApplication('site');
	$mainframe->initialise();

	$user =& JFactory::getUser();
	if (!$user->id)  // check if user is logged in
		http_critical_error('Viewing this image requires authentication; you should log in to the website.');

	$userdata = $user->lastvisitDate;
} else {
	$userdata = false;
}

// normalize path to image
$imagesource = http_query_string_url();  // try URL query string first, more reliable
if (empty($imagesource)) {
	$imagesource = http_path_info_url();  // try URL PATH_INFO next, less reliable
}
if (empty($imagesource))
	http_critical_error('The image to download has not been specified in the URL.');

// check image existence
$imagepath = JPATH_ROOT.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $imagesource);
$filename = basename($imagepath);
if (!is_file($imagepath))  // image file not found
	http_not_found($filename);
if (substr($imagepath, 0, strlen(JPATH_ROOT.DIRECTORY_SEPARATOR)) !== JPATH_ROOT.DIRECTORY_SEPARATOR)  // image path is outside Joomla folder
	http_not_found($filename);

// verify image hash value
$size = @getimagesize($imagepath);
if ($size === false)
	http_not_found($filename);

$hash = md5($userdata.$imagepath.'_'.$size[0].'x'.$size[1]);
if ($hash != $_GET['h'])  // compare to computed hash
	http_not_found($filename);

// return image as HTTP payload
header('Content-Type: '.$size['mime']);
header('Content-Length: '.filesize($imagepath));
header('Content-Disposition: attachment; filename="'.$filename.'"');
@readfile($imagepath);
exit;