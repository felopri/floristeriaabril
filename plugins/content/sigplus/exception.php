<?php
/**
* @file
* @brief    sigplus Image Gallery Plus exceptions
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

class SIGPlusException extends Exception {
	/** The language key for the exception. */
	protected function getErrorKey() {
		return false;
	}

	/** The standard error message text for the exception. */
	protected function getErrorText() {
		return false;
	}

	protected function getErrorMessage($errortext) {
		return $errortext;
	}

	/** The text of a critical error message. */
	public function __construct() {
		$errorheader = JText::_('SIGPLUS_EXCEPTION');
		if ($errorheader == 'SIGPLUS_EXCEPTION') {  // error message not mapped to language string
			$errorheader = '[sigplus] Critical error';
		}

		$errorkey = $this->getErrorKey();
		if ($errorkey !== false) {
			$errormessage = JText::_($errorkey);  // use language-specific error message text if available
			if ($errormessage == $errorkey) {  // error message not available in language
				$errormessage = $this->getErrorText();  // use standard (English) error message
			}
			$errormessage = $this->getErrorMessage($errormessage);
		} else {
			$errormessage = parent::getMessage();
		}
		$this->message = '<p><strong>'.$errorheader.':</strong> '.$errormessage.'</p>';
	}
}

/** Thrown when the extension is not able to guess what the base URL prefix for image folders is. */
class SIGPlusBaseURLException extends SIGPlusException {
	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_BASEURL';
	}

	protected function getErrorText() {
		return 'Unable to deduce image base URL from the current configuration settings, please specify an explicit base URL in the back-end.';
	}
}

/** Thrown when a text file is not encoded with UTF-8. */
class SIGPlusEncodingException extends SIGPlusException {
	private $textfile;

	public function __construct($textfile) {
		$this->textfile = $textfile;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.str_replace(array(JPATH_ROOT,DIRECTORY_SEPARATOR), array('<em>root</em>','/'), $this->textfile).'</kbd>');
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_ENCODING';
	}

	protected function getErrorText() {
		return 'Text files are assumed to have UTF-8 character encoding but %s uses a different encoding.';
	}
}

/** Thrown when a URL contains invalid characters. */
class SIGPlusURLEncodingException extends SIGPlusException {
	private $url;

	public function __construct($url) {
		$this->url = $url;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.$this->url.'</kbd>');
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_URLENCODING';
	}

	protected function getErrorText() {
		return 'URLs are assumed to have been URL-encoded but the URL %s appears to have an invalid character.';
	}
}

class SIGPlusFolderException extends SIGPlusException {
	protected $folder;

	public function __construct($folder) {
		$this->folder = $folder;
		parent::__construct();
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, '<kbd>'.str_replace(array(JPATH_ROOT,DIRECTORY_SEPARATOR), array('<em>root</em>','/'), $this->folder).'</kbd>');
	}
}

/** Thrown when the image folder does not exist or is inaccessible. */
class SIGPlusImageFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER';
	}

	protected function getErrorText() {
		return 'Image folder %s specified in the administration back-end does not exist or is inaccessible.';
	}
}

/** Thrown when the image gallery folder is not valid. */
class SIGPlusImageGalleryFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER_GALLERY';
	}

	protected function getErrorText() {
		return 'Image gallery folder %s is expected to be a path relative to the image base folder specified in the administration back-end.';
	}
}

/** Thrown when the image base folder is not valid. */
class SIGPlusBaseFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER_BASE';
	}

	protected function getErrorText() {
		return 'Image base folder %s specified in the administration back-end is expected to be a relative path w.r.t. the Joomla root.';
	}
}

/** Thrown when the thumbnail folder is not valid. */
class SIGPlusThumbFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER_THUMB';
	}

	protected function getErrorText() {
		return 'Thumb folder %s specified in administration back-end is expected to be a relative path w.r.t. the image folder.';
	}
}

/** Thrown when the preview image folder is not valid. */
class SIGPlusPreviewFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER_PREVIEW';
	}

	protected function getErrorText() {
		return 'Preview image folder %s specified in administration back-end is expected to be a relative path w.r.t. the image folder.';
	}
}

/** Thrown when the folder for high-resolution image versions is not valid. */
class SIGPlusFullsizeFolderException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_FOLDER_FULLSIZE';
	}

	protected function getErrorText() {
		return 'Folder %s specified for high-resolution image versions in the administration back-end is expected to be a relative path w.r.t. the image folder, or should be left empty.';
	}
}

/** Thrown when the thumbnail folder and the preview image folder are set to point to the same directory. */
class SIGPlusFolderConflictException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_CONFLICT';
	}

	protected function getErrorText() {
		return 'Thumb folder and preview image folder cannot be both set to %s.';
	}
}

/** Thrown when the extension lacks permissions to access the image base folder. */
class SIGPlusBaseFolderPermissionException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_PERMISSION_BASE';
	}

	protected function getErrorText() {
		return 'Insufficient file system permissions to access the image base folder %s, or the folder does not exist.';
	}
}

/** Thrown when the extension lacks permissions to create the folder for thumbnail images. */
class SIGPlusFolderPermissionException extends SIGPlusFolderException {
	public function __construct($folder) {
		parent::__construct($folder);
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_PERMISSION';
	}

	protected function getErrorText() {
		return 'Insufficient file system permissions to create the folder %s.';
	}
}

/** Thrown when a required library dependency is not available. */
class SIGPlusLibraryUnavailableException extends SIGPlusException {
	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_LIBRARY';
	}

	protected function getErrorText() {
		return 'The Graphics Draw (gd) or ImageMagick (imagick) image processing library has to be enabled in the PHP configuration to generate thumbnails.';
	}
}

/** Thrown when the extension could not properly initialize. */
class SIGPlusInitializationException extends SIGPlusException {
	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_INITIALIZATION';
	}

	protected function getErrorText() {
		return 'The extension could not properly initialize image services.';
	}
}

/** Thrown when the extension attempts to allocate memory for a resource with prohibitively large memory footprint. */
class SIGPlusOutOfMemoryException extends SIGPlusException {
	private $required;
	private $available;
	private $resourcefile;

	public function __construct($required, $available, $resourcefile) {
		$this->required = $required;
		$this->available = $available;
		$this->resourcefile = $resourcefile;
		parent::__construct();
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_MEMORY';
	}

	protected function getErrorText() {
		return 'Insufficient memory to carry out the requested operation on %3$s, %1$d bytes required, %2$d bytes available.';
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, $this->required, $this->available, '<kbd>'.str_replace(array(JPATH_ROOT,DIRECTORY_SEPARATOR), array('<em>root</em>',DIRECTORY_SEPARATOR), $this->resourcefile).'</kbd>');
	}
}

class SIGPlusMooToolsException extends SIGPlusException {
	private $engine;

	public function __construct($engine) {
		$this->engine = $engine;
		parent::__construct();
	}

	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_MOOTOOLS';
	}

	protected function getErrorText() {
		return '%s expects MooTools 1.2 or later, please enable the <em>MooTools Upgrade</em> system plug-in in the Joomla 1.5 administration back-end.';
	}

	protected function getErrorMessage($errortext) {
		return sprintf($errortext, $this->engine);
	}
}

class SIGPlusNotSupportedException extends SIGPlusException {
	protected function getErrorKey() {
		return 'SIGPLUS_EXCEPTION_NOTSUPPORTED';
	}

	protected function getErrorText() {
		return 'This syntax or combination of configuration settings is not supported. Some settings are mutually exclusive, please check the documentation on whether your set of configuration parameters is valid.';
	}
}