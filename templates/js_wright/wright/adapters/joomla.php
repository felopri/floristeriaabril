<?php
/**
 * Abstract Adapter Class
 *
 * This class is to set the default platform parsing tags, and each subclass
 * can customize the output depending on the version of Joomla.
 *
 * @package Wright
 * @author Jeremy Wilken
 */

class WrightAdapterJoomla
{

	protected $version;

	public function __construct($version)
	{
		$this->version = $version;
	}

	/**
	 * Handles the tag processing
	 *
	 * @param array $tag
	 * @return string
	 */

	public function get($config)
	{
		$tag = key($config);
		$file = dirname(__FILE__).'/'.'joomla'.'/'.$tag.'.php';
		$class = 'WrightAdapterJoomla'.ucfirst($tag);

		if (is_file(dirname(__FILE__).'/'.'joomla'.'/'.'joomla_'.$this->getVersion().'/'.$tag.'.php'))
		{
			$file = dirname(__FILE__).'/'.'joomla'.'/'.'joomla_'.$this->getVersion().'/'.$tag.'.php';
			$class = 'WrightAdapterJoomla'.$this->getVersion().ucfirst($tag);
		}
		
		require_once $file;

		$item = new $class();

		return $item->render($config[$tag]);
	}

	public function getVersion()
	{
		return $this->version;
	}
	
}
