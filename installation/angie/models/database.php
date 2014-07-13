<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelDatabase extends AModel
{
	/**
	 * The databases.ini contents
	 *
	 * @var array
	 */
	private $dbini = array();

	/**
	 * Returns the cached databases.ini information, parsing the databases.ini
	 * file if necessary.
	 *
	 * @return array
	 */
	public function getDatabasesIni()
	{
		if (empty($this->dbini))
		{
			$this->dbini = ASession::getInstance()->get('databases.dbini', null);
			if (empty($this->dbini))
			{
				$filename = APATH_INSTALLATION . '/sql/databases.ini';
				if (file_exists($filename))
				{
					$this->dbini = $this->_parse_ini_file($filename, true);
				}

				if(!empty($this->dbini))
				{
					// Add the custom options
					$temp = array();
					$siteSQL = null;
					foreach($this->dbini as $key => $data)
					{
						if(!array_key_exists('dbtech', $data))
						{
							$data['dbtech'] = null;
						}
						if(!array_key_exists('existing', $data))
						{
							$data['existing'] = 'drop';
						}
						if(!array_key_exists('prefix', $data))
						{
							$data['prefix'] = 'jos_';
						}
						if(!array_key_exists('foreignkey', $data))
						{
							$data['foreignkey'] = true;
						}
						if(!array_key_exists('noautovalue', $data))
						{
							$data['noautovalue'] = true;
						}
						if(!array_key_exists('replace', $data))
						{
							$data['replace'] = false;
						}
						if(!array_key_exists('utf8db', $data))
						{
							$data['utf8db'] = false;
						}
						if(!array_key_exists('utf8tables', $data))
						{
							$data['utf8tables'] = false;
						}
						if(!array_key_exists('maxexectime', $data))
						{
							$data['maxexectime'] = 5;
						}
						if(!array_key_exists('throttle', $data))
						{
							$data['throttle'] = 250;
						}

						if ($key == 'site.sql')
						{
							$siteSQL = $data;
						}
						else
						{
							$temp[$key] = $data;
						}
					}

					$temp = array_merge(array('site.sql' => $siteSQL), $temp);

					$this->dbini = $temp;
				}

				ASession::getInstance()->set('databases.dbini', $this->dbini);
			}
		}

		return $this->dbini;
	}

	/**
	 * Saves the (modified) databases information to the session
	 */
	public function saveDatabasesIni()
	{
		ASession::getInstance()->set('databases.dbini', $this->dbini);
	}

	/**
	 * Returns the keys of all available database definitions
	 *
	 * @return array
	 */
	public function getDatabaseNames()
	{
		$dbini = $this->getDatabasesIni();

		return array_keys($dbini);
	}

	/**
	 * Returns an object with a database's connection information
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  null|stdClass
	 */
	public function getDatabaseInfo($key)
	{
		$dbini = $this->getDatabasesIni();

		if(array_key_exists($key, $dbini))
		{
			return (object)$dbini[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Sets a database's connection information
	 *
	 * @param   string  $key   The database's key (name of SQL file)
	 * @param   mixed   $data  The database's data (stdObject or array)
	 */
	public function setDatabaseInfo($key, $data)
	{
		$dbini = $this->getDatabasesIni();

		$this->dbini[$key] = (array) $data;

		$this->saveDatabasesIni();
	}

	/**
	* A PHP based INI file parser.
	*
	* Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
	* the parse_ini_file page on http://gr.php.net/parse_ini_file
	*
	* @param string $file Filename to process
	* @param bool $process_sections True to also process INI sections
	* @param bool $rawdata If true, the $file contains raw INI data, not a filename
	* @return array An associative array of sections, keys and values
	* @access private
	*/
   private function _parse_ini_file($file, $process_sections = false, $rawdata = false)
   {
	   $process_sections = ($process_sections !== true) ? false : true;

	   if(!$rawdata)
	   {
		   $ini = @file($file);
	   }
	   else
	   {
		   $file = str_replace("\r","",$file);
		   $ini = explode("\n", $file);
	   }

	   if (count($ini) == 0) {return array();}
	   if(empty($ini)) return array();

	   $sections = array();
	   $values = array();
	   $result = array();
	   $globals = array();
	   $i = 0;
	   foreach ($ini as $line) {
		   $line = trim($line);
		   $line = str_replace("\t", " ", $line);

		   // Comments
		   if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

		   // Sections
		   if ($line{0} == '[') {
			   $tmp = explode(']', $line);
			   $sections[] = trim(substr($tmp[0], 1));
			   $i++;
			   continue;
		   }

		   // Key-value pair
		   list($key, $value) = explode('=', $line, 2);
		   $key = trim($key);
		   $value = trim($value);
		   if (strstr($value, ";")) {
			   $tmp = explode(';', $value);
			   if (count($tmp) == 2) {
				   if ((($value{0} != '"') && ($value{0} != "'")) ||
				   preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
				   preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
					   $value = $tmp[0];
				   }
			   } else {
				   if ($value{0} == '"') {
					   $value = preg_replace('/^"(.*)".*/', '$1', $value);
				   } elseif ($value{0} == "'") {
					   $value = preg_replace("/^'(.*)'.*/", '$1', $value);
				   } else {
					   $value = $tmp[0];
				   }
			   }
		   }
		   $value = trim($value);
		   $value = trim($value, "'\"");

		   if ($i == 0) {
			   if (substr($line, -1, 2) == '[]') {
				   $globals[$key][] = $value;
			   } else {
				   $globals[$key] = $value;
			   }
		   } else {
			   if (substr($line, -1, 2) == '[]') {
				   $values[$i-1][$key][] = $value;
			   } else {
				   $values[$i-1][$key] = $value;
			   }
		   }
	   }

	   for($j = 0; $j < $i; $j++) {
		   if ($process_sections === true) {
			   $result[$sections[$j]] = $values[$j];
		   } else {
			   $result[] = $values[$j];
		   }
	   }

	   return $result + $globals;
   }
}