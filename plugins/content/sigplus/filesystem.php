<?php
/**
* @file
* @brief    sigplus Image Gallery Plus file system functions
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2010 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'constants.php';

/**
* pathinfo with component selector argument PATHINFO_FILENAME implementation for PHP < 5.2.0.
*/
function pathinfo_filename($path) {
	$basename = pathinfo($path, PATHINFO_BASENAME);
	$p = strrpos($basename, '.');
	return substr($basename, 0, $p);  // drop extension from filename
}

/**
* Ensure that a string is a relative path, removing leading and trailing space and slashes from a path string.
*/
function make_relative_path($folder) {
	$folder = str_replace('\\', '/', trim($folder, "\t\n\r /"));  // remove leading and trailing spaces and slashes
	if (preg_match('#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#', $folder)) {
		return $folder;
	} else {
		return false;  // cannot be made a valid relative path
	}
}

/**
* Ensures that all components of a URL are URL-encoded.
*/
function safeurlencode($url) {
	$urlparts = parse_url($url);
	$pattern = '#^([0-9A-Za-z!"$&\'()*+,.:;=@_-]|%[0-9A-Za-z]{2})+$#';
	$segments = explode('/', $urlparts['path']);
	foreach ($segments as &$segment) {
		if (!preg_match($pattern, $segment)) {  // path segment contains a character that has not been URL-encoded
			$segment = rawurlencode($segment);
		}
	}
	$urlparts['path'] = implode('/', $segments);
	if (!empty($urlparts['query'])) {
		if (!preg_match($pattern, $urlparts['query'])) {  // query contains a character that has not been URL-encoded
			$urlparts['query'] = rawurlencode($urlparts['query']);
		}
	}
	return
		$urlparts['scheme'].'://'.
		( empty($urlparts['user']) ? '' : $urlparts['user'].( empty($urlparts['pass']) ? '' : ':'.$urlparts['pass'] ).'@' ).
		$urlparts['host'].$urlparts['path'].
		( empty($urlparts['query']) ? '' : '?'.$urlparts['query'] ).
		( empty($urlparts['fragment']) ? '' : '#'.$urlparts['fragment'] );
}

function pathurlencode($path) {
	$parts = explode('/', $path);
	foreach ($parts as &$part) {
		$part = rawurlencode($part);
	}
	return implode('/', $parts);
}

function is_remote_path($path) {
	return preg_match('#^https?://#', $path);
}

/**
* Check if a path is an absolute file system path.
*/
function is_absolute_path($path) {
	return (bool) preg_match('#^([A-Za-z0-9]+:)?[/\\\\]#', $path);
}

/**
* Filters regular files, skipping those that are hidden.
* The filename of a hidden file starts with a dot.
*/
function is_regular_file($filename) {
	return $filename[0] != '.';
}

/**
* List files and directories inside the specified path with modification time.
* @return An associative array with filenames as keys and timestamps as values.
*/
function scandirmtime($dir) {
	$dh = @opendir($dir);
	if ($dh === false) {  // cannot open directory
		return false;
	}
	$files = array();
	while (false !== ($filename = readdir($dh))) {
		if (!is_regular_file($filename)) {
			continue;
		}
		$files[$filename] = filemtime($dir.DIRECTORY_SEPARATOR.$filename);
	}
	closedir($dh);
	return $files;
}

/**
* Flat file listing.
*/
function fscandirsorted($folder, $criterion = SIGPLUS_FILENAME, $order = SIGPLUS_ASCENDING, array $exceptions = array()) {
	switch ($criterion) {
		case SIGPLUS_UNSORTED:
		case SIGPLUS_FILENAME:
			$entries = @scandir($folder, $order);
			if ($entries === false) {
				return false;
			}
			$files = array_filter($entries, 'is_regular_file');  // list files and directories inside the specified path but omit hidden files
			break;
		case SIGPLUS_MTIME:
			$entries = scandirmtime($folder);
			if ($entries === false) {
				return false;
			}
			switch ($order) {
				case SIGPLUS_ASCENDING:
					asort($entries); break;
				case SIGPLUS_DESCENDING:
				default:
					arsort($entries); break;
			}
			$files = array_keys($entries);
			break;
		case SIGPLUS_RANDOM:
			$entries = @scandir($folder);
			if ($entries === false) {
				return false;
			}
			$files = array_filter($entries, 'is_regular_file');  // list files and directories inside the specified path but omit hidden files
			shuffle($files);  // randomize order
			break;
		default:
			return false;
	}
	return array_diff($files, $exceptions);
}

if (class_exists('RecursiveFilterIterator')) {
	class RecursiveDirectoryExceptionFilter extends RecursiveFilterIterator {
		protected $exceptions;

		public function __construct(RecursiveDirectoryIterator $iterator, array $exceptions = array()) {
			$this->exceptions = $exceptions;
			parent::__construct($iterator);
		}

		public function accept() {
			return !in_array($this->current()->getSubPathname(), $this->exceptions);
		}

		public function getChildren() {
			$base = $this->current()->getSubPathname();
			$exceptions = $this->exceptions;
			foreach ($exceptions as &$exception) {
				$exception = $base.DIRECTORY_SEPARATOR.$exception;
			}
			return new self($this->getInnerIterator()->getChildren(), $exceptions);
		}
	}
}

/**
* Recursive file listing.
*/
function rscandirsorted($folder, $criterion = SIGPLUS_FILENAME, $order = SIGPLUS_ASCENDING, array $exceptions = array(), $depth = 0) {
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryExceptionFilter(
			new RecursiveDirectoryIterator($folder,
				RecursiveDirectoryIterator::KEY_AS_FILENAME | RecursiveDirectoryIterator::CURRENT_AS_SELF
			),
			$exceptions
		),
		RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD
	);
	$iterator->setMaxDepth($depth);
	switch ($criterion) {
		case SIGPLUS_UNSORTED:
			$subpaths = array();   // an array of relative paths (with file name component)
			foreach ($iterator as $key => $item) {
				$subpaths[] = $item->getSubPathname();
			}
			break;
		case SIGPLUS_FILENAME:
			$filenames = array();  // an array of file names (without path component)
			$subpaths = array();   // an array of relative paths (with file name component)
			foreach ($iterator as $key => $item) {
				$filenames[] = $key;
				$subpaths[] = $item->getSubPathname();
			}
			switch ($order) {
				case SIGPLUS_ASCENDING:
					array_multisort($filenames, SORT_ASC, SORT_STRING, $subpaths, SORT_ASC, SORT_STRING);
					break;
				case SIGPLUS_DESCENDING:
					array_multisort($filenames, SORT_DESC, SORT_STRING, $subpaths, SORT_DESC, SORT_STRING);
					break;
			}
			break;
		case SIGPLUS_MTIME:
			$filetimes = array();  // an array of times that file was last modified
			$subpaths = array();   // an array of relative paths (with file name component)
			foreach ($iterator as $key => $item) {
				$filetimes[] = $item->current()->getMTime();
				$subpaths[] = $item->getSubPathname();
			}
			switch ($order) {
				case SIGPLUS_ASCENDING:
					array_multisort($filetimes, SORT_ASC, SORT_NUMERIC, $subpaths, SORT_ASC, SORT_STRING);
					break;
				case SIGPLUS_DESCENDING:
					array_multisort($filetimes, SORT_DESC, SORT_NUMERIC, $subpaths, SORT_DESC, SORT_STRING);
					break;
			}
			break;
		case SIGPLUS_RANDOM:
			$subpaths = array();   // an array of relative paths (with file name component)
			foreach ($iterator as $key => $item) {
				$subpaths[] = $item->getSubPathname();
			}
			shuffle($subpaths);  // randomize order
			break;
		default:
			return false;
	}
	if (empty($subpaths)) {
		return false;
	} else {
		return $subpaths;
	}
}

/**
* List files and directories inside the specified path with custom sorting option.
* @param string $folder The directory whose files and subdirectories to list.
* @param int $criterion The sort criterion, e.g. filename or last modification time.
* @param int $order The sort order, ascending or descending.
*/
function scandirsorted($folder, $criterion = SIGPLUS_FILENAME, $order = SIGPLUS_ASCENDING, array $exceptions = array(), $depth = 0) {
	if ($depth != 0 && class_exists('RecursiveDirectoryIterator')) {
		return rscandirsorted($folder, $criterion, $order, $exceptions, $depth);
	} else {
		return fscandirsorted($folder, $criterion, $order, $exceptions);
	}
}

/**
* Checks whether a file or directory exists accepting both lowercase and uppercase extension.
* @return The file name with extension as found in the file system.
*/
function file_exists_case_insensitive($path) {
	$realpath = realpath($path);
	if ($realpath !== false) {
		return pathinfo($realpath, PATHINFO_BASENAME);  // file name possibly with extension
	}
	$filename = pathinfo($path, PATHINFO_BASENAME);  // file name possibly with extension
	if (file_exists($path)) {  // file exists as-is, no inspection of extension is necessary
		return $filename;
	}
	$extension = pathinfo($path, PATHINFO_EXTENSION);  // file extension if present
	if ($extension) {  // if file has extension
		$p = strrpos($path, '.');              // starting position of extension (incl. dot)
		$base = substr($path, 0, $p);          // everything up to extension
		$extension = substr($path, $p);        // extension (incl. dot)
		$p = strrpos($filename, '.');
		$filename = substr($filename, 0, $p);  // drop extension from filename
		$extension = strtolower($extension);
		if (file_exists($base.$extension)) {   // file with lowercase extension
			return $filename.$extension;
		}
		$extension = strtoupper($extension);
		if (file_exists($base.$extension)) {   // file with uppercase extension
			return $filename.$extension;
		}
	}
	return false;  // file not found
}

/**
* Get the lastest time the folder or one of its descendants has been modified.
* @param string $dir
*    An absolute path to a folder.
* @param int $depth
*    0 for current folder only, 1 for current and children, n (>1) for descandants until the given limit, -1 for all descendants.
*/
function get_folder_last_modified($dir, $depth = 0) {
	$mtime = filemtime($dir);
	if ($depth != 0) {
		// scan directory for last modified descandant folder
		if ($dh = @opendir($dir)) {
			while (($entry = readdir($dh)) !== false) {
				if ($entry != '.' && $entry != '..' && is_dir($dir.DIRECTORY_SEPARATOR.$entry)) {
					$mtime = max($mtime, get_folder_last_modified($dir.DIRECTORY_SEPARATOR.$entry, $depth - 1));
				}
			}
			closedir($dh);
		}
	}
	return $mtime;
}