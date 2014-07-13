<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla!
 */

define('APATH_BASE',          __DIR__);
define('APATH_INSTALLATION',  __DIR__);

$parts = explode(DIRECTORY_SEPARATOR, APATH_BASE);
array_pop($parts);

define('APATH_ROOT',          implode(DIRECTORY_SEPARATOR, $parts));

define('APATH_SITE',          APATH_ROOT);
define('APATH_CONFIGURATION', APATH_ROOT);
define('APATH_ADMINISTRATOR', APATH_ROOT . '/administrator');
define('APATH_LIBRARIES',     APATH_ROOT . '/libraries');
define('APATH_THEMES',        APATH_INSTALLATION . '/template');
define('APATH_TEMPINSTALL',   APATH_INSTALLATION . '/tmp');