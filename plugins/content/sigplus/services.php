<?php
/**
* @file
* @brief    sigplus Image Gallery Plus general image services
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'dependencies.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'exception.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'filesystem.php';

/**
* Whether to use PATH_INFO in building image download URLs.
* Some servers do not have proper support for PATH_INFO in URLs. In such cases, this constant must be set to false.
*/
define('SIGPLUS_USE_URL_PATH_INFO', true);

/** Duration while generated temporary content (excluding images) is valid [sec]. */
define('SIGPLUS_CACHE_LIFETIME', 24*60*60);

/**
* A short caption and a more verbose description attached to an image.
* Objects of this class are instantiated based on a "labels.txt" file.
*/
class SIGPlusImageLabel {
	/** Image file name (without path) this label entry corresponds to. */
	public $imagefile;
	/** The short caption attached to the image. */
	private $caption;
	/** The longer description attached to the image if any. */
	private $description;

	function __construct($imagefile, $caption, $description = false) {
		$this->imagefile = $imagefile;
		$this->caption = $caption;
		$this->description = $description;
	}

	/**
	* Image caption with special HTML characters escaped.
	*/
	public function getCaptionHtml() {
		return $this->caption;
	}

	/**
	* Image description with special HTML characters escaped.
	*/
	public function getDescriptionHtml() {
		if ($this->description) {
			$description = $this->description;
		} else {
			$description = $this->caption;  // copy caption to description if omitted
		}
		return $description;
	}

	/**
	* Image description without HTML tags.
	*/
	public function getDescriptionText() {
		return strip_tags($this->description);
	}
}

/**
* System-wide image gallery generation configuration parameters.
*/
class SIGPlusImageServicesConfiguration {
	/** Whether to support multilingual labeling. */
	public $multilingual = false;
	/** Base directory for images. */
	public $imagesfolder = 'images';
	/** Base URL the directory for images corresponds to. */
	public $baseurl = false;
	/** Subdirectory for watermarked images. */
	public $watermarkfolder = 'watermark';
	/** Subdirectory for thumbnail images. */
	public $thumbsfolder = 'thumbs';
	/** Subdirectory for preview images. */
	public $previewfolder = 'preview';
	/** Subdirectory for full-size images. */
	public $fullsizefolder = false;
	/** Subdirectory for external script files. */
	public $scriptfolder = 'sigplus';
	/** Whether to use Joomla cache folder for storing generated images. */
	public $thumbscache = false;
	/** Whether to use Joomla cache folder for storing temporary generated content. */
	public $contentcache = true;
	/** Image processing library to use. */
	public $library = 'default';

	public function validate() {
		$this->multilingual = (bool) $this->multilingual;
		$this->thumbscache = (bool) $this->thumbscache;
		$this->contentcache = (bool) $this->contentcache;
		switch ($this->library) {
			case 'gd':
				if (!is_gd_supported()) {
					$this->library = 'default';
				}
				break;
			case 'imagick':
				if (!is_imagick_supported()) {
					$this->library = 'default';
				}
				break;
			default:
				$this->library = 'default';
		}
	}

	public function checkFolders() {
		// image base folder
		if (preg_match('#^(?:[a-zA-Z]+:)?[/\\\\]#', $this->imagesfolder)) {  // starts with a leading slash (UNIX) or a drive letter designation and a backslash (Windows)
			// absolute path
			$path = realpath($this->imagesfolder);
			if ($path === false) {
				throw new SIGPlusBaseFolderException($this->imagesfolder);
			}
			if ($this->baseurl === false && strpos($path, JPATH_ROOT.DIRECTORY_SEPARATOR) === 0) {  // starts with Joomla root folder
				$this->baseurl = JURI::base(true).str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen(JPATH_ROOT)));
			}
		} else {
			$folder = make_relative_path($this->imagesfolder);
			if ($folder === false) {
				throw new SIGPlusBaseFolderException($this->imagesfolder);
			}
			$path = JPATH_ROOT.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $folder);
			if (!is_dir($path)) {
				throw new SIGPlusBaseFolderPermissionException($path);
			}
			if ($this->baseurl === false) {
				$this->baseurl = JURI::base(true).'/'.$folder;
			}
		}
		$this->imagesfolder = $path;

		// base URL
		if ($this->baseurl === false) {
			throw new SIGPlusBaseURLException();
		}

		// thumbnail folder (either inside image folder or cache folder)
		$thumbsfolder = make_relative_path($this->thumbsfolder);
		if ($thumbsfolder === false) {
			throw new SIGPlusThumbFolderException($this->thumbsfolder);
		}

		// preview image folder (either inside image folder or cache folder)
		$previewfolder = make_relative_path($this->previewfolder);
		if ($previewfolder === false) {
			throw new SIGPlusPreviewFolderException($this->previewfolder);
		}

		// check that thumbnail folder and preview folder are not identical
		if (!$this->thumbscache && $thumbsfolder == $previewfolder) {
			throw new SIGPlusFolderConflictException($this->previewfolder);
		}

		// set folders
		$this->previewfolder = $previewfolder;
		$this->thumbsfolder = $thumbsfolder;

		// full size image folder
		if ($this->fullsizefolder) {
			$fullsizefolder = make_relative_path($this->fullsizefolder);
			if ($fullsizefolder === false) {
				throw new SIGPlusFullsizeFolderException($this->fullsizefolder);
			}
			$this->fullsizefolder = $fullsizefolder;
		} else {  // no folder available for high-resolution images
			$this->fullsizefolder = false;
		}
	}

	public function setParameters(JRegistry $params) {
		$this->multilingual = $params->get('labels_multilingual', $this->multilingual);  // get whether to use multilingual labeling
		$this->imagesfolder = $params->get('base_folder', $params->get('images_folder', $this->imagesfolder));
		$this->baseurl = $params->get('base_url', $this->baseurl);
		$this->thumbsfolder = $params->get('thumb_folder', $this->thumbsfolder);
		$this->previewfolder = $params->get('preview_folder', $this->previewfolder);
		$this->fullsizefolder = $params->get('fullsize_folder', $this->fullsizefolder);
		$this->thumbscache = $params->get('thumb_cache', $this->thumbscache);
		$this->library = $params->get('library', $this->library);
		$this->validate();
	}
}

/**
* Image and thumbnail file and folder services.
*/
class SIGPlusImageServices {
	/** System-wide configuration parameters. */
	private $config;

	public function __construct(SIGPlusImageServicesConfiguration $config = null) {
		$this->config = is_null($config) ? new SIGPlusImageServicesConfiguration() : $config;
		$this->config->checkFolders();
	}

	public function getLibrary() {
		return $this->config->library;
	}

	/**
	* Creates a directory if it does not already exist.
	* @param string $directory
	*    The full path to the directory.
	*/
	private function createDirectoryOnDemand($directory) {
		if (!is_dir($directory)) {  // directory does not exist
			@mkdir($directory, 0755, true);  // try to create it
			if (!is_dir($directory)) {
				throw new SIGPlusFolderPermissionException($directory);
			}
			// create an index.html to prevent getting a web directory listing
			@file_put_contents($directory.DIRECTORY_SEPARATOR.'index.html', '<html><body bgcolor="#FFFFFF"></body></html>');
		}
	}
	
	/**
	* Checks whether a file could have been generated from an original.
	* @param string $fileOriginal
	*    The full path to the original file.
	* @param string $fileGenerated
	*    The full path to the generated file (which may not exist).
	*/
	private static function isFileGeneratedFrom($fileOriginal, $fileGenerated) {
		if (is_file($fileGenerated)) {  // generated file exists
			$timeOriginal = filemtime($fileOriginal);
			$timeGenerated = filemtime($fileGenerated);
			if ($timeOriginal !== false && $timeGenerated !== false) {  // both original and generated file has timestamp
				return $timeGenerated >= $timeOriginal;  // generated file is more recent than original
			} else {
				return true;  // file could have been generated from original, timestamp cannot decide
			}
		}
		return false;
	}

	/**
	* Maps an image folder to a full file system path.
	* @param string $entry
	*    A simple directory entry (file or folder).
	*/
	public function getImagePath($entry) {
		if ($entry) {
			return $this->config->imagesfolder.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entry);  // replace '/' with platform-specific directory separator
		} else {
			return $this->config->imagesfolder;
		}
	}

	/**
	* The full file system path to a high-resolution image version.
	* @param string $imagepath
	*    An absolute path to an image file.
	*/
	private function getFullsizeImagePath($imagepath) {
		if (!$this->config->fullsizefolder) {
			return $imagepath;
		}
		$fullsizepath = dirname($imagepath).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->fullsizefolder).DIRECTORY_SEPARATOR.basename($imagepath);
		if (!is_file($fullsizepath)) {
			return $imagepath;
		}
		return $fullsizepath;
	}

	private function getImageShortUrl($imagepath) {
		if (strpos($imagepath, $this->config->imagesfolder.DIRECTORY_SEPARATOR) === 0) {
			// cut off image base folder and normalize directory separator
			$segments = explode(DIRECTORY_SEPARATOR, substr($imagepath, strlen($this->config->imagesfolder) + 1));
			foreach ($segments as &$segment) {
				$segment = rawurlencode($segment);
			}
			return implode('/', $segments);
		} else {
			return false;
		}
	}

	/**
	* Generate (one-time) hash to prevent client-side URL tampering.
	* The hash encrypts user data, full image path in file system and image size.
	*/
	private function getImageDownloadHash($imagepath, $userdata = false) {
		$imagesize = @getimagesize($imagepath);
		return md5($userdata.$imagepath.'_'.$imagesize[0].'x'.$imagesize[1]);
	}

	/**
	* Image download URL.
	* @param bool $authentication
	*    If true, the hash to prevent URL tampering will include user login information.
	*/
	private function getImageDownloadUrl($imagepath, $authentication = false) {
		// compute hash for download attempt
		if ($authentication) {
			$user = JFactory::getUser();
			if (!$user->id) {  // forbidden to access image if user is not logged in
				return JURI::base(true).'/plugins/content/sigplus/css/404.png';
			}
			$hash = $this->getImageDownloadHash($imagepath, $user->lastvisitDate);
		} else {
			$hash = $this->getImageDownloadHash($imagepath);  // no user data required
		}

		// check if inside Joomla directory hierarchy
		$root = JURI::base(true).'/';
		if (strpos($this->config->baseurl, $root) === 0) {
			$path = substr($this->config->baseurl, strlen($root)).'/'.$this->getImageShortUrl($imagepath);
			if (SIGPLUS_USE_URL_PATH_INFO) {
				return JURI::base(true).'/plugins/content/sigplus/download.php/'.$path.'?h='.$hash.( $authentication ? '&a=1' : '' );
			} else {
				return JURI::base(true).'/plugins/content/sigplus/download.php?imgurl='.$path.'&h='.$hash.( $authentication ? '&a=1' : '' );
			}
		} else {
			throw new SIGPlusNotSupportedException();
		}
	}

	/**
	* Temporary or permanent link to image resource.
	* @param bool $authentication
	*    If true, URL is to be a temporary link to image that is available to the currently logged-in user; if false, URL is to be a permanent link.
	*/
	private function getAuthenticatedUrl($imagepath, $authentication = false) {
		if ($authentication) {
			return $this->getImageDownloadUrl($imagepath, $authentication);
		} else {
			return $this->config->baseurl.'/'.$this->getImageShortUrl($imagepath);
		}
	}

	/**
	* The full URL to an image.
	*/
	public function getImageUrl($imageref, $authentication = false) {
		if (is_remote_path($imageref)) {  // authentication not possible with remote images
			return $imageref;
		} else {
			return $this->getAuthenticatedUrl($imageref, $authentication);
		}
	}

	/**
	* The full URL for downloading the high-resolution version of an image.
	*/
	public function getFullsizeImageDownloadUrl($imageref, $authentication = false) {
		if (is_remote_path($imageref)) {  // download option or high-resolution image URL not meaningful for remote images
			return false;
		}
		$imageref = $this->getFullsizeImagePath($imageref);
		return $this->getImageDownloadUrl($imageref, $authentication);
	}

	/**
	* The full path to an image used for image watermarking.
	* @param string $imagedirectory
	*    The full path to a directory where images to watermark are to be found.
	* @return The full path to a watermark image, or false if not found.
	*/
	public function checkWatermarkPath($imagedirectory) {
		$watermarkimage = 'watermark.png';
		// look inside image gallery folder (e.g. "images/stories/myfolder")
		$watermarkingallery = $imagedirectory.DIRECTORY_SEPARATOR.$watermarkimage;
		// look inside watermark subfolder of image gallery folder (e.g. "images/stories/myfolder/watermark")
		$watermarkinsubfolder = $imagedirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->watermarkfolder).DIRECTORY_SEPARATOR.$watermarkimage;
		// look inside base path (e.g. "images/stories")
		$watermarkinbase = $this->config->imagesfolder.DIRECTORY_SEPARATOR.$watermarkimage;

		if (is_file($watermarkingallery)) {
			return $watermarkingallery;
		} elseif (is_file($watermarkinsubfolder)) {
			return $watermarkinsubfolder;
		} elseif (is_file($watermarkinbase)) {
			return $watermarkinbase;
		} else {
			return false;
		}
	}

	private static function getGeneratedSubfolder(SIGPlusPreviewParameters $params) {
		if ($params->width > 0 && $params->height > 0) {
			if ($params->crop) {
				$fitcode = 'x';  // center and crop
			} else {
				$fitcode = 's';  // scale to dimensions
			}
			return $params->width.$fitcode.$params->height;
		} else {
			return false;
		}
	}

	/**
	* Returns a unique filename for a generated image avoiding name conflicts.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	*/
	private function getImageHash($imageref, SIGPlusPreviewParameters $params) {
		$imagepath = is_remote_path($imageref) ? parse_url($imageref, PHP_URL_PATH) : $imageref;

		$extension = pathinfo($imagepath, PATHINFO_EXTENSION);
		if ($extension) {
			$extension = '.'.$extension;
		}

		switch ($extension) {
			case '.jpg': case '.jpeg': case '.JPG': case '.JPEG':
				$quality = '@'.$params->quality; break;
			default:
				$quality = '';
		}
		$hashbase = 'sigplus_'.self::getGeneratedSubfolder($params).$quality.'_'.$imageref;
		return md5($hashbase).$extension;
	}

	/**
	* The full path to a generated image (e.g. preview image or thumbnail) based on configuration settings.
	* No tests are performed as to whether the image actually exists.
	* @param string $generatedfolder
	*    The subfolder where the generated images are to be stored.
	*/
	private function checkGeneratedImagePath($generatedfolder, $imageref, SIGPlusPreviewParameters $params) {
		if ($this->config->thumbscache || is_remote_path($imageref)) {  // images are set to be generated in cache folder OR image is at a remote location
			$directory = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
			$path = $directory.DIRECTORY_SEPARATOR.$this->getImageHash($imageref, $params);  // hash original image file paths to avoid name conflicts
			if ($this->config->thumbscache) {
				if (self::isFileGeneratedFrom($imageref, $path)) {  // check existence of target file and compare timestamps
					return $path;
				}
			} else {
				if (is_file($path)) {  // check existence of target file
					return $path;
				}
			}
		} else {  // an absolute file system path
			$directory = dirname($imageref).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
			$path = $directory.DIRECTORY_SEPARATOR.basename($imageref);
			if (self::isFileGeneratedFrom($imageref, $path)) {
				return $path;
			}
			if ($subfolder = self::getGeneratedSubfolder($params)) {
				$path = $directory.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR.basename($imageref);
				if (self::isFileGeneratedFrom($imageref, $path)) {
					return $path;
				}
			}
		}
		return false;
	}

	/**
	* Create the full path to a generated image (e.g. preview image or thumbnail) based on configuration settings.
	* @param string $generatedfolder
	*    The subfolder where the generated images are to be stored.
	*/
	private function createGeneratedImagePath($generatedfolder, $imageref, SIGPlusPreviewParameters $params) {
		if ($this->config->thumbscache || is_remote_path($imageref)) {  // images are set to be generated in cache folder OR image is at a remote location
			$directory = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
			$this->createDirectoryOnDemand($directory);
			return $directory.DIRECTORY_SEPARATOR.$this->getImageHash($imageref, $params);  // hash original image file paths to avoid name conflicts
		} else {  // an absolute file system path
			$directory = dirname($imageref).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
			$subfolder = self::getGeneratedSubfolder($params);
			if ($subfolder) {
				$directory .= DIRECTORY_SEPARATOR.$subfolder;
			}
			$this->createDirectoryOnDemand($directory);
			return $directory.DIRECTORY_SEPARATOR.basename($imageref);
		}
	}

	/**
	* The full path to a watermarked image based on configuration settings.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to a watermarked image, or false on error.
	*/
	public function checkWatermarkedPath($imageref) {
		$params = new SIGPlusPreviewParameters();
		$params->width = 0;  // special values for watermarked image
		$params->height = 0;
		$params->crop = false;
		$params->quality = 0;
		return $this->checkGeneratedImagePath($this->config->watermarkfolder, $imageref, $params);
	}

	/**
	* Create the full path to a watermarked image based on configuration settings.
	* The directory should be writable but the file need not exist.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to a watermarked image, or false on error.
	*/
	public function createWatermarkedPath($imageref) {
		$params = new SIGPlusPreviewParameters();
		$params->width = 0;  // special values for watermarked image
		$params->height = 0;
		$params->crop = false;
		$params->quality = 0;
		return $this->createGeneratedImagePath($this->config->watermarkfolder, $imageref, $params);
	}

	/**
	* The full path to a preview image based on configuration settings.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to a preview image, or false on error.
	*/
	public function checkPreviewPath($imageref, SIGPlusPreviewParameters $params) {
		return $this->checkGeneratedImagePath($this->config->previewfolder, $imageref, $params);
	}

	/**
	* Creates the full path to a preview image based on configuration settings.
	* The directory should be writable but the file need not exist.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to a preview image, or false on error.
	*/
	public function createPreviewPath($imageref, SIGPlusPreviewParameters $params) {
		return $this->createGeneratedImagePath($this->config->previewfolder, $imageref, $params);
	}

	/**
	* The full path to an image thumbnail based on configuration settings.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to an image thumbnail, or false on error.
	*/
	public function checkThumbnailPath($imageref, SIGPlusPreviewParameters $params) {
		if ($params->isThumbnailRequired()) {
			return $this->checkGeneratedImagePath($this->config->thumbsfolder, $imageref, $params->getThumbnailParameters());
		} else {
			return $this->checkPreviewPath($imageref, $params);
		}
	}

	/**
	* Creates the full path to an image thumbnail based on configuration settings.
	* The directory should be writable but the file need not exist.
	* @param string $imageref
	*    Absolute path or URL to an image file.
	* @return The full path to an image thumbnail, or false on error.
	*/
	public function createThumbnailPath($imageref, SIGPlusPreviewParameters $params) {
		if ($params->isThumbnailRequired()) {
			return $this->createGeneratedImagePath($this->config->thumbsfolder, $imageref, $params->getThumbnailParameters());
		} else {
			return $this->createPreviewPath($imageref, $params);
		}
	}

	/**
	* The URL to a generated image based on configuration settings.
	* @param string $generatedfolder
	*    The subfolder where the generated images are located.
	*/
	private function getGeneratedImageUrl($generatedfolder, $imageref, SIGPlusPreviewParameters $params) {
		if ($this->config->thumbscache || is_remote_path($imageref)) {  // images are set to be generated in cache folder OR image is at a remote location
			return JURI::base(true).'/cache/'.$generatedfolder.'/'.$this->getImageHash($imageref, $params);
		} else {  // an absolute file system path
			$imageabspath = $this->checkGeneratedImagePath($generatedfolder, $imageref, $params);
			if (strpos($imageabspath, $this->config->imagesfolder.DIRECTORY_SEPARATOR) === 0) {  // does not walk outside image base folder
				$imagerelpath = substr($imageabspath, strlen($this->config->imagesfolder) + 1);  // cut off image base folder
				return $this->config->baseurl.'/'.pathurlencode(str_replace(DIRECTORY_SEPARATOR, '/', $imagerelpath));
			}
		}
		return false;
	}

	/**
	* The URL to a watermarked image based on configuration settings.
	* @return The URL to the watermarked image.
	*/
	public function getWatermarkedUrl($imageref) {
		$params = new SIGPlusPreviewParameters();
		$params->width = 0;
		$params->height = 0;
		$params->crop = false;
		$params->quality = 0;
		return $this->getGeneratedImageUrl($this->config->watermarkfolder, $imageref, $params);
	}

	/**
	* The URL to a preview image based on configuration settings.
	* A preview image typically has a higher resolution than a thumbnail image.
	* It is not verified whether the URL points to a valid location.
	* @return The URL to the image thumbnail.
	*/
	public function getPreviewUrl($imageref, SIGPlusPreviewParameters $params) {
		return $this->getGeneratedImageUrl($this->config->previewfolder, $imageref, $params);
	}

	/**
	* The URL to an image thumbnail based on configuration settings.
	* It is not verified whether the URL points to a valid location.
	* @return The URL to the image thumbnail.
	*/
	public function getThumbnailUrl($imageref, SIGPlusPreviewParameters $params) {
		if ($params->isThumbnailRequired()) {
			return $this->getGeneratedImageUrl($this->config->thumbsfolder, $imageref, $params->getThumbnailParameters());
		} else {
			return $this->getPreviewUrl($imageref, $params);
		}
	}

	/**
	* Directory listing.
	* @param int $depth A value of 0 for flat directory listing, a positive value for recursive directory listing with a limit, or -1 with no limit.
	*/
	public function getListing($imagedirectory, $sortcriterion = SIGPLUS_FILENAME, $sortorder = SIGPLUS_ASCENDING, $depth = 0) {
		$specialentries = array($this->config->thumbsfolder, $this->config->previewfolder, $this->config->watermarkfolder, $this->config->fullsizefolder);
		$exceptions = array();
		foreach ($specialentries as $value) {
			if ($value !== false) {
				if (strpos($value, '/') !== false) {
					throw new SIGPlusNotSupportedException();  // multi-part generated image folder names not supported in recursive mode
				}
				$exceptions[] = str_replace('/', DIRECTORY_SEPARATOR, $value);
			}
		}
		return scandirsorted($imagedirectory, $sortcriterion, $sortorder, $exceptions, $depth);
	}

	/**
	* Generates a labels file from a directory listing.
	*/
	public function getLabelsFromFilenames($imagedirectory) {
		$files = scandir($imagedirectory);
		if ($files === false) {
			return array();
		}
		$files = array_filter($files, 'is_regular_file');  // list files inside the specified path but omit hidden files
		$labels = array();
		foreach ($files as $file) {
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			switch ($extension) {
				case 'jpg': case 'jpeg': case 'JPG': case 'JPEG':
				case 'gif': case 'GIF':
				case 'png': case 'PNG':
					$labels[] = new SIGPlusImageLabel($file, pathinfo_filename($file));
			}
		}
		return $labels;
	}

	/**
	* Finds the language-specific labels file.
	* @param string $imagedirectory
	*    An absolute path or URL to a directory with a labels file.
	* @return The full path to the language-specific labels file.
	*/
	private function getLabelsFilePath($imagedirectory, $labelsfilename) {
		if (is_remote_path($imagedirectory)) {
			return false;
		}

		if (is_file($imagedirectory)) {  // a file, not a directory, do not try appending "labels.txt", which might fail with PHP directive "open_basedir"
			return false;
		}

		if ($this->config->multilingual) {  // check for language-specific labels file
			$lang = JFactory::getLanguage();
			$file = $imagedirectory.DIRECTORY_SEPARATOR.$labelsfilename.'.'.$lang->getTag().'.txt';
			if (is_file($file)) {
				return $file;
			}
		}

		// default to language-neutral labels file
		$file = $imagedirectory.DIRECTORY_SEPARATOR.$labelsfilename.'.txt';  // filesystem path to labels file
		if (is_file($file)) {
			return $file;
		}
		return false;
	}

	/**
	* Returns the language-specific labels file contents.
	* @param string $imagedirectory
	*    An absolute path or URL to a directory with a labels file.
	* @return The contents of the labels file as a string, or false if the labels file cannot be accessed.
	*/
	private function getLabelsFileContents($imagedirectory, $labelsfilename) {
		$file = $this->getLabelsFilePath($imagedirectory, $labelsfilename);
		return $file ? file_get_contents($file) : false;
	}

	private function getLabelsFileContentsRemote(array $urlparts, $labelsfilename) {
		if ($this->config->multilingual) {  // check for language-specific labels file
			$lang = JFactory::getLanguage();
			$url = http_build_url($urlparts, array('path' => $labelsfilename.'.'.$lang->getTag().'.txt'), HTTP_URL_JOIN_PATH | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);
			$contents = file_get_contents($url);
			if ($contents !== false) {
				return $contents;
			}
		}

		// default to language-neutral labels file
		$url = http_build_url($urlparts, array('path' => $labelsfilename.'.txt'), HTTP_URL_JOIN_PATH | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);
		return file_get_contents($file);
	}

	/**
	* Short captions and descriptions attached to images with a "labels.txt" file.
	* @return An array of SIGPlusImageLabel instances, or an empty array of no "labels.txt" file is found.
	*/
	public function getLabels($imagedirectory, $labelsfilename, &$defaultcaption, &$defaultdescription) {
		if ($remote = is_remote_path($imagedirectory)) {
			$urlparts = parse_url($imagedirectory);

			// read labels file
			$contents = $this->getLabelsFileContentsRemote($urlparts, $labelsfilename);
		} else {
			// read labels file
			$contents = $this->getLabelsFileContents($imagedirectory, $labelsfilename);
		}

		if ($contents === false) {
			return array();
		}

		// remove UTF-8 BOM and normalize line endings
		if (!strcmp("\xEF\xBB\xBF", substr($contents,0,3))) {  // file starts with UTF-8 BOM
			$contents = substr($contents, 3);  // remove UTF-8 BOM
		}
		$contents = str_replace("\r", "\n", $contents);  // normalize line endings

		// split into lines
		$matches = array();
		preg_match_all('/^([^|\n]+)(?:[|]([^|\n]*)(?:[|]([^\n]*))?)?$/mu', $contents, $matches, PREG_SET_ORDER);
		if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
			switch (preg_last_error()) {
				case PREG_BAD_UTF8_ERROR:
					throw new SIGPlusEncodingException($labelsfile);
			}
		}

		// parse individual entries
		$labels = array();
		foreach ($matches as $match) {
			$imagefile = $match[1];
			$caption = count($match) > 2 ? $match[2] : false;
			$description = count($match) > 3 ? $match[3] : false;

			if ($imagefile == '*') {  // set default label
				$defaultcaption = $caption;
				$defaultdescription = $description;
			} else {
				if (is_remote_path($imagefile)) {  // a URL to a remote image
					$imagefile = safeurlencode($imagefile);
				} elseif ($remote) {  // an image imported from a remote labels file
					$imagefile = http_build_url($urlparts, array('path' => $imagefile), HTTP_URL_JOIN_PATH | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);
				} else {  // a local image
					$imagefile = file_exists_case_insensitive($imagedirectory.DIRECTORY_SEPARATOR.$imagefile);
					if ($imagefile === false) {  // check that image file truly exists
						continue;
					}
				}
				$labels[] = new SIGPlusImageLabel($imagefile, $caption, $description);
			}
		}
		return $labels;
	}

	/**
	* Returns a cache key that uniquely identifies a gallery setup.
	*/
	private function getCacheKey($imagebase, SIGPlusGalleryParameters $params) {
		$labelsfile = is_dir($imagebase) ? $this->getLabelsFilePath($imagebase, $params->labels) : false;
		return md5(md5(serialize($this->config), true).md5(serialize($params), true).$imagebase.($labelsfile ? ';'.$labelsfile : ''));
	}

	/**
	* Returns the path to cached content for an image gallery.
	* @param string $suffix '.html' or '.js'.
	*/
	public function getCachedContentPath($key, $suffix) {
		if ($this->config->contentcache) {  // use cache folder
			$directory = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->scriptfolder);
			return $directory.DIRECTORY_SEPARATOR.$key.$suffix;
		}
		return false;
	}

	/**
	* Returns the URL to cached content for an image gallery.
	* @param string $suffix '.html' or '.js'.
	*/
	public function getCachedContentUrl($cachekey, $suffix) {
		if ($this->config->contentcache) {  // use cache folder
			return JURI::base(true).'/cache/'.$this->config->scriptfolder.'/'.$cachekey.$suffix;
		}
		return false;  // not supported
	}

	/**
	* Fetches cached content for the specified directory and parameters.
	* @param string $imagebase
	*    Base for computing image folder hash, typically an absolute file system path to the image or the directory where the images reside.
	* @param $params
	*    Parameters that affect how the gallery is to be displayed.
	*/
	public function getCachedContent($imagebase, SIGPlusGalleryParameters $params) {
		if ($this->config->contentcache) {  // use cache folder
			$cachekey = $this->getCacheKey($imagebase, $params);
			$cachefile = $this->getCachedContentPath($cachekey, $params->linkage == 'inline' ? '.html' : '.js');

			if (is_file($cachefile)) {  // content available in cache only if cache file exists
				if (is_remote_path($imagebase)) {
					return $cachekey;
				} elseif (is_dir($imagebase)) {
					// check if directory or labels file has changed
					$labelsfile = $this->getLabelsFilePath($imagebase, $params->labels);
					if (filemtime($cachefile) >= get_folder_last_modified($imagebase, $params->depth) && ($labelsfile === false || filemtime($cachefile) >= filemtime($labelsfile))) {
						return $cachekey;
					}
				} elseif (is_file($imagebase)) {
					// check if file has changed
					if (filemtime($cachefile) >= filemtime($imagebase)) {
						return $cachekey;
					}
				}
			}
		}
		return false;
	}

	/**
	* Persists content for the specified directory and parameters.
	* @param string $code The HTML or JavaScript code to persist.
	*/
	public function saveCachedContent($imagebase, SIGPlusGalleryParameters $params, $code) {
		if ($this->config->contentcache) {
			$cachedirectory = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->scriptfolder);
			$this->createDirectoryOnDemand($cachedirectory);
			$cachekey = $this->getCacheKey($imagebase, $params);
			$cachefile = $this->getCachedContentPath($cachekey, $params->linkage == 'inline' ? '.html' : '.js');

			if (file_put_contents($cachefile, $code) !== false) {
				return $cachekey;
			}
		}
		return false;
	}

	/**
	* Clean expired content from cache.
	*/
	public function cleanCachedContent() {
		if ($this->config->contentcache) {
			$dir = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->scriptfolder);
			if ($dh = @opendir($dir)) {
				$expiry = time() - SIGPLUS_CACHE_LIFETIME;
				while (($entry = readdir($dh)) !== false) {
					$path = $dir.DIRECTORY_SEPARATOR.$entry;
					if ($entry != '.' && $entry != '..' && is_file($path) && filemtime($path) < $expiry) {
						unlink($path);
					}
				}
				closedir($dh);
			}
		}
	}
}
