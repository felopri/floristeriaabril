<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelFinalise extends AModel
{
	public function cleanup()
	{
		$result = true;

		// Remove installation directory
		$result = $this->recursive_remove_directory(APATH_INSTALLATION);

		// Rename the backup .htaccess and php.ini files
		$files_map = array(
			APATH_ROOT . '/htaccess.bak'	=> APATH_ROOT . '/.htaccess',
			APATH_ROOT . '/web.config.bak'	=> APATH_ROOT . '/web.config',
			APATH_ROOT . '/php.ini.bak'		=> APATH_ROOT . '/php.ini',
		);

		foreach ($files_map as $from => $to)
		{
			if (!file_exists($from))
			{
				continue;
			}

			if (file_exists($to))
			{
				if (!@unlink($to))
				{
					continue;
				}
			}

			@rename($from, $to);
		}

		return $result;
	}

	/**
	 * Recursively remove a directory from the server
	 *
	 * @param   string   $directory  The path to the directory to remove
	 * @param   boolean  $empty      Set to true to only empty the directory but not completely delete it
	 *
	 * @return  boolean  True on success, false on failure
	 */
	function recursive_remove_directory($directory, $empty=false)
	{
		// If the path has a slash at the end we remove it here
		if (substr($directory, -1) == '/')
		{
			$directory = substr($directory, 0, -1);
		}

		// If the path is not valid or is not a directory ...
		if (!file_exists($directory) || !is_dir($directory))
		{
			// ... we return false and exit the function
			return false;
		}
		// If the path is not readable...
		elseif (!is_readable($directory))
		{
			// ... we return false and exit the function
			return false;
		}
		// ... else if the path is readable
		else
		{
			// We open the directory
			$handle = opendir($directory);

			// and scan through the items inside
			while (false !== ($item = readdir($handle)))
			{
				// if the filepointer is not the current directory
				// or the parent directory
				if ($item != '.' && $item != '..')
				{
					// We build the new path to delete
					$path = $directory.'/'.$item;

					// If the new path is a directory...
					if(is_dir($path))
					{
						// ...we call this method with the new path
						$this->recursive_remove_directory($path);
					}
					// If the new path is a file...
					else
					{
						// ...we remove the file
						@unlink($path);
					}
				}
			}

			// Close the directory
			closedir($handle);

			// If the option to empty is not set to true
			if ($empty == false)
			{
				// Try to delete the now empty directory
				if (!@rmdir($directory))
				{
					// return false if not possible
					return false;
				}
			}
			// return success
			return true;
		}
	}
}