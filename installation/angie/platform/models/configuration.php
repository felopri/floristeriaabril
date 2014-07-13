<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelConfiguration extends AModel
{
	/**
	 * The Joomla! configuration variables
	 *
	 * @var array
	 */
	protected $configvars = array();

	public function __construct($config = array())
	{
		// Call the parent constructor
		parent::__construct($config);

		// Get the Joomla! version from the configuration or the session
		if (array_key_exists('jversion', $config))
		{
			$jVersion = $config['jversion'];
		}
		else
		{
			$jVersion = ASession::getInstance()->get('jversion', '2.5.0');
		}

		// Load the configuration variables from the session or the default configuration shipped with ANGIE
		$this->configvars = ASession::getInstance()->get('configuration.variables');
		if (empty($this->configvars))
		{
			// Get default configuration based on the Joomla! version
			if(version_compare($jVersion, '2.5.0', 'ge') && version_compare($jVersion, '3.0.0', 'lt'))
			{
				$v = '25';
			}
			else
			{
				$v = '30';
			}
			$className = 'J' . $v . 'Config';
			$filename = APATH_INSTALLATION . '/angie/platform/models/jconfig/j' . $v . '.php';
			$this->configvars = $this->loadFromFile($filename, $className);
			if (!empty($this->configvars))
			{
				$this->saveToSession();
			}
		}
	}

	/**
	 * Destructor. Automatically saves the configuration variables to the session
	 */
	public function __destruct()
	{
		if (!empty($this->configvars))
		{
			ASession::getInstance()->set('configuration.variables', $this->configvars);
		}
	}

	/**
	 * Saves the modified configuration variables to the session
	 */
	public function saveToSession()
	{
		ASession::getInstance()->set('configuration.variables', $this->configvars);
	}

	/**
	 * Resets the configuration variables
	 */
	public function reset()
	{
		$this->configvars = array();
		ASession::getInstance()->remove('configuration.variables');
	}


	/**
	 * Loads the configuration information from a PHP file
	 *
	 * @param   string  $file       The full path to the file
	 * @param   string  $className  The name of the configuration class
	 */
	public function loadFromFile($file, $className = 'JConfig')
	{
		$ret = array();
		include_once $file;

		if(class_exists($className))
		{
			foreach(get_class_vars($className) as $key => $value)
			{
				$ret[$key] = $value;
			}
		}

		return $ret;
	}

	/**
	 * Get the contents of the configuration.php file
	 *
	 * @param   string  $className  The name of the configuration class, by default it's JConfig
	 *
	 * @return  string  The contents of the configuration.php file
	 */
	public function getFileContents($className = 'JConfig')
	{
		$out = "<?php\nclass $className {\n";
		foreach($this->configvars as $name => $value){
			if(is_array($value))
			{
				$temp = array();
				foreach($value as $key => $data)
				{
					$data = addcslashes($data,'\'\\');
					$temp .= "'".$key."' => '".$data."'";
				}
				$value = "array (\n".implode(",\n", $pieces)."\n)";
			}
			else
			{
				// Log and temp paths in Windows systems will be forward-slash encoded
				if( (($name=='tmp_path') || ($name=='log_path')) )
				{
					$value = $this->TranslateWinPath($value);
				}
				$value = "'".addcslashes($value, '\'\\')."'";
			}
			$out .= "\tpublic $" . $name . " = ". $value .";\n";
		}

		$out .= '}' . "\n";

		return $out;
	}

	/**
	 * Gets a configuration value
	 *
	 * @param   string  $key      The key (variable name)
	 * @param   mixed   $default  The default value to return if the key doesn't exist
	 *
	 * @return  mixed  The variable's value
	 */
	function get($key, $default = null)
	{
		if(array_key_exists($key, $this->configvars))
		{
			return $this->configvars[$key];
		}
		else
		{
			// The key was not found. Set it with the default value, store and
			// return the default value
			$this->configvars[$key] = $default;
			$this->saveToSession();
			return $default;
		}
	}

	/**
	 * Sets a variable's value and stores the configuration array in the global
	 * Storage.
	 *
	 * @param   string  $key    The variable name
	 * @param   mixed   $value  The value to set it to
	 */
	function set($key, $value)
	{
		$this->configvars[$key] = $value;
		$this->saveToSession();
	}

	/**
	 * Makes a Windows path more UNIX-like, by turning backslashes to forward slashes.
	 * Since JP 2.0.b1 it takes into account UNC paths, e.g.
	 * \\myserver\some\folder becomes \\myserver/some/folder
	 *
	 * @param string $p_path The path to transform
	 * @return string
	 */
	private function TranslateWinPath( $p_path )
	{
		static $is_windows;

		if(empty($is_windows))
		{
			$is_windows =  (DIRECTORY_SEPARATOR == '\\');
		}

		$is_unc = false;

		if ($is_windows)
		{
			// Is this a UNC path?
			$is_unc = (substr($p_path, 0, 2) == '//');
			// Change potential windows directory separator
			if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\')){
				$p_path = strtr($p_path, '\\', '/');
			}
		}

		// FIX 2.1.b2: Remove multiple slashes
		$p_path = str_replace('///','/',$p_path);
		$p_path = str_replace('//','/',$p_path);

		// Fix UNC paths
		if($is_unc)
		{
			$p_path = '/'.$p_path;
		}

		return $p_path;
	}
}