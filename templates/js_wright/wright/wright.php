<?php
/**
 * @package Joomlashack Wright Framework
 * @copyright Joomlashack 2010-2012. All Rights Reserved.
 *
 * @description Wright is a framework layer for Joomla to improve stability of Joomlashack Templates
 *
 * It would be inadvisable to alter the contents of anything inside of this folder
 *
 */
defined('_JEXEC') or die('You are not allowed to directly access this file');

//Adding a check for PHP4 to cut down on support
if (version_compare(PHP_VERSION, '5', '<'))
{
	print 'You are using an out of date version of PHP, version ' . PHP_VERSION . ' and our products require PHP 5.2 or greater. Please contact your host to use PHP 5.2 or greater. All versions of PHP prior to this are unsupported, by us and by the PHP community.';
	die();
}

// includes WrightTemplateBase class for customizations to the template
require_once(dirname(__FILE__) . '/template/wrighttemplatebase.php');


class Wright
{
	public $template;
	public $document;
	public $adapter;
	public $params;
	public $baseurl;
	public $author;

	public $revision = "3.0.4";

	// Urls
	private $_urlTemplate = null;
	private $_urlWright = null;
	private $_urlBootstrap = null;

	function Wright()
	{
		// Initialize properties
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$this->document = $document;
		$this->params = $document->params;
		$this->baseurl = $document->baseurl;

		// Urls
		$this->_urlTemplate = JURI::root(true) . '/templates/' . $this->document->template;
		$this->_urlWright = $this->_urlTemplate . '/wright';
		$this->_urlBootstrap = $this->_urlWright . '/bootstrap';
		$this->_urlFontAwesomeMore = $this->_urlWright . '/fontawesomemore';

		$this->author = simplexml_load_file(JPATH_BASE . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->document->template . DIRECTORY_SEPARATOR . 'templateDetails.xml')->author;

		if (is_file(JPATH_THEMES . DIRECTORY_SEPARATOR . $document->template . DIRECTORY_SEPARATOR . 'functions.php'))
			include_once(JPATH_THEMES . DIRECTORY_SEPARATOR . $document->template . DIRECTORY_SEPARATOR . 'functions.php');

		// Get our template for further parsing, if custom file is found
		// it will use it instead of the default file
		$path = JPATH_THEMES . '/' . $document->template . '/' . 'template.php';
		$menu = $app->getMenu();

		// If homepage, load up home.php if found
        if (version_compare(JVERSION, '1.6', 'lt')) {
            if ($menu->getActive() == $menu->getDefault() && is_file(JPATH_THEMES . '/' . $document->template . '/' . 'home.php'))
                $path = JPATH_THEMES . '/' . $document->template . '/home.php';
            elseif (is_file(JPATH_THEMES . '/' . $document->template . '/custom.php'))
                $path = JPATH_THEMES . '/' . $document->template . '/custom.php';
        }
        else {
            $lang = JFactory::getLanguage();
            if ($menu->getActive() == $menu->getDefault($lang->getTag()) && is_file(JPATH_THEMES . '/' . $document->template . '/home.php'))
                $path = JPATH_THEMES . '/' . $document->template . '/home.php';
            elseif (is_file(JPATH_THEMES . '/' . $document->template . '/custom.php'))
                $path = JPATH_THEMES . '/' . $document->template . '/custom.php';
        }


		// Include our file and capture buffer
		ob_start();
		include($path);
		$this->template = ob_get_contents();
		ob_end_clean();
	}

	static function getInstance()
	{
		static $instance = null;
		if ($instance === null)
		{
			$instance = new Wright();
		}

		return $instance;
	}

	public function display()
	{
		// Setup the header
		$this->header();

		// Parse by platform
		$this->platform();

		// Parse by doctype
		$this->doctype();

		print trim($this->template);

		return true;
	}

	public function header()
	{
		// Remove mootools if set
		if ($this->document->params->get('mootools', '1') == '0')
		{
			$dochead = $this->document->getHeadData();
			reset($dochead['scripts']);
			foreach ($dochead['scripts'] as $script => $type)
			{
				if (strpos($script, 'media/system/js/caption.js') || strpos($script, 'media/system/js/mootools.js') || strpos($script, 'media/system/js/mootools-core.js') || strpos($script, 'media/system/js/mootools-more.js'))
				{
					unset($dochead['scripts'][$script]);
				}
			}
			$this->document->setHeadData($dochead);
		}

		// load jQuery ?
		if ($loadJquery = $this->document->params->get('jquery', 0))
		{
            switch ($loadJquery) {
                // load jQuery locally
                case 1:
                    $jquery = JURI::root().'templates/' . $this->document->template . '/wright/js/jquery-1.8.0.min.js';
                    break;
                // load jQuery from Google
                default:
                    $jquery = 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js';
                    break;
            }
            $this->document->addScript($jquery);
            // ensure that jQuery loads in noConflict mode to avoid mootools conflicts
            $this->document->addScriptDeclaration('jQuery.noConflict();');
		}

		// load bootstrap JS
		$this->document->addScript($this->_urlBootstrap . '/js/bootstrap.min.js');

		// Add header script if set
		if (trim($this->document->params->get('headerscript', '')) !== '')
		{
            $this->document->addScriptDeclaration($this->document->params->get('headerscript'));
		}

		// set custom template theme for user
		$user = JFactory::getUser();
		if (!is_null(JRequest::getVar('templateTheme', NULL)))
		{
			$user->setParam('theme', JRequest::getVar('templateTheme'));
			$user->save(true);
		}
		if ($user->getParam('theme'))
		{
			$this->document->params->set('style', $user->getParam('theme'));
		}

		// Build css
		$this->css();
	}

	private function css()
	{
		$styles = $this->loadCSSList();

		if ($this->document->params->get('csscache', 'no') == 'yes' && is_writable(JPATH_THEMES . '/' . $this->document->template . '/css'))
		{
			$this->processCSSCache($styles);
		}
		else
		{
			$this->addCSSToHead($styles);
		}
	}

	private function processCSSCache($styles)
	{
		// Combine css into one file if files have been altered since cached copy
		$rebuild = false;

		if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/' . $this->document->template . '.css'))
			$cachetime = filemtime(JPATH_THEMES . '/' . $this->document->template . '/css/' . $this->document->template . '.css');
		else
			$cachetime = 0;

		foreach ($styles as $folder => $files)
		{
			if (count($files))
			{
				foreach ($files as $style)
				{
					if ($folder == 'wright')
						$file = JPATH_THEMES . '/' . $this->document->template . '/wright/css/' . $style;
					elseif ($folder == 'template')
						$file = JPATH_THEMES . '/' . $this->document->template . '/css/' . $style;
					else
						$file = JPATH_THEMES . '/' . $this->document->template . '/css/' . $style;

					if (filemtime($file) > $cachetime)
						$rebuild = true;
				}
			}
		}

		if ($rebuild)
		{
			$css = '';
			foreach ($styles as $folder => $files)
			{
				if (count($files))
				{
					foreach ($files as $style)
					{
						if ($folder == 'wright')
							$css .= file_get_contents(JPATH_THEMES . '/' . $this->document->template . '/wright/css/' . $style);
						elseif ($folder == 'template')
							$css .= file_get_contents(JPATH_THEMES . '/' . $this->document->template . '/css/' . $style);
						else
							$css .= file_get_contents(JPATH_THEMES . '/' . $this->document->template . '/css/' . $style);
					}
				}
			}

			// Clean out any charsets
			$css = str_replace('@charset "utf-8";', '', $css);
			// Strip comments
			$css = preg_replace('/\/\*.*?\*\//s', '', $css);

			include('css/csstidy/class.csstidy.php');

			$tidy = new csstidy();
			$tidy->parse($css);

			file_put_contents(JPATH_THEMES . '/' . $this->document->template . '/css/' . $this->document->template . '.css', $tidy->print->plain());
		}
		$this->document->addStyleSheet(JURI::root().'templates/' . $this->document->template . '/css/' . $this->document->template . '.css');
	}

	private function addCSSToHead($styles)
	{
		foreach ($styles as $folder => $files)
		{
			if (count($files))
			{
				foreach ($files as $style)
				{
					if ($folder == 'wright')
						$sheet = JURI::root().'templates/' . $this->document->template . '/wright/css/' . $style;
					elseif ($folder == 'template')
						$sheet = JURI::root().'templates/' . $this->document->template . '/css/' . $style;
					elseif ($folder == 'bootstrap') {
						if ($style == 'style-' . $this->document->params->get('style') . '.bootstrap.min.css') {
							$sheet = JURI::root().'templates/' . $this->document->template . '/css/' . $style;
						}
						else {
							$sheet = $this->_urlBootstrap . '/css/' . $style;						
						}
					}
					elseif ($folder == 'fontawesomemore')
						$sheet = $this->_urlFontAwesomeMore . '/css/' . $style;
					else
						$sheet = JURI::root().'templates/' . $this->document->template . '/css/' . $style;

					$this->document->addStyleSheet($sheet);
				}
			}
		}
	}

	private function loadCSSList()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.environment.browser');

		$browser = JBrowser::getInstance();

		$styles['bootstrap'] = array('bootstrap.min.css');
		if ($this->document->params->get('responsive',1)) {
			$styles['bootstrap'][] = 'bootstrap-responsive.min.css';
		}
		$styles['fontawesomemore'] = array('font-awesome.css');
        $version = explode('.', JVERSION);
        $version = $version[0].$version[1];
        if (is_file(JPATH_THEMES . '/' . $this->document->template .'/wright/css/joomla'.$version.'.css'))
        {
            $styles['wright'][] = 'joomla'.$version.'.css';
        }

		$styles['template'] = JFolder::files(JPATH_THEMES . '/' . $this->document->template . '/css', '\d{1,2}_.*.css');

		// Load up a specific bootstrap style if set
		if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/' . 'style-' . $this->document->params->get('style') . '.bootstrap.min.css'))
			$styles['bootstrap'][0] = 'style-' . $this->document->params->get('style') . '.bootstrap.min.css';

		// Load up a specific style if set
		if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/' . 'style-' . $this->document->params->get('style') . '.css'))
			$styles['template'][] = 'style-' . $this->document->params->get('style') . '.css';

		// Add some stuff for lovely IE if needed
		if ($browser->getBrowser() == 'msie')
		{
			$this->document->addScript(JURI::root().'templates/' . $this->document->template . '/wright/js/html5.js');

			if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/ie.css'))
			{
				$styles['ie'][] = 'ie.css';
			}

			// Switch to allow specific versions of IE to have additional sheets
			$major = $browser->getMajor();

			switch ($major)
			{
				case '6' :
					if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/ie6.css'))
						$styles['ie'][] = 'ie6.css';
					$this->document->addScript(JURI::root().'templates/' . $this->document->template . '/wright/js/dd_belatedpng.js');
					if ($this->document->params->get('doctype') == 'html5')
						$this->document->addScript(JURI::root().'templates/' . $this->document->template . '/js/html5.js');
					break;
				case '7' :
					$styles['fontawesomemore'][] = 'font-awesome-ie7.css';
					// does not break for leaving defaults
				default :
					if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/ie' . $major . '.css'))
						$styles['ie'][] = 'ie' . $major . '.css';
					if ($this->document->params->get('doctype') == 'html5')
						$this->document->addScript(JURI::root().'templates/' . $this->document->template . '/wright/js/html5.js');
					break;
			}
		}

		if ($this->document->direction == 'rtl' && is_file(JPATH_THEMES . '/' . $this->document->template . '/css/rtl.css'))
			$styles['template'][] = 'rtl.css';

		//Check to see if custom.css file is present, and if so add it after all other css files
			if (is_file(JPATH_THEMES . '/' . $this->document->template . '/css/custom.css'))
				$styles['template'][] = 'custom.css';

		return $styles;
	}

	private function doctype()
	{
		require(dirname(__FILE__) . '/doctypes/' . $this->document->params->get('doctype', 'html5') . '.php');
		$adapter_name = 'HtmlAdapter' . $this->document->params->get('doctype', 'html5');
		$adapter = new $adapter_name($this->document->params);

		foreach ($adapter->getTags() as $name => $regex)
		{
			$action = 'get' . ucfirst($name);
			$this->template = preg_replace_callback($regex, array($adapter, $action), $this->template);
		}

		// reorder columns based on the order
        $this->reorderContent();

        if (trim($this->document->params->get('footerscript')) != '') {
            $this->template = str_replace('</body>', '<script type="text/javascript">'.$this->document->params->get('footerscript').'</script></body>', $this->template);
        }
		$this->template = str_replace('__cols__', $adapter->cols, $this->template);
	}

	/**
	 * Searches for platform specific tags, and has a callback function to
	 * handle the processing of each match
	 *
	 * @access private
	 */
	private function platform()
	{
		// Get Joomla's version to get proper platform
		jimport('joomla.version');
		$version = new JVersion();
		$file = ucfirst(str_replace('.', '', $version->RELEASE));

		// Load up the proper adapter
		require_once(dirname(__FILE__) . '/adapters/joomla.php');
		$this->adapter = new WrightAdapterJoomla($file);
		$this->template = preg_replace_callback("/<w:(.*)\/>/i", array(get_class($this), 'platformTags'), $this->template);
		return true;
	}

	private function platformTags($matches)
	{
		// Grab first match since there should only be one
		$match = $matches[1];

		// @TODO Craft a regex to better handle syntax possibilities
		if (strpos($match, '='))
		{
			$tag = substr($match, 0, strpos($match, ' '));
			$list = explode('" ', substr($match, trim(strpos($match, ' ') + 1)));

			$attributes = array();

			foreach ($list as $item)
			{
				if (strlen($item) > strrchr($item, '"'))
				{
					$name = substr($item, 0, strpos($item, '='));
					$value = substr($item, strpos($item, '"') + 1);
					$attributes[$name] = $value;
				}
			}
			$config = array(trim($tag) => $attributes);
		}
		else
		{
			$config = array(trim($match) => array());
		}
		return $this->adapter->get($config);
	}

	// Borrowed from JDocumentHtml for compatibility
	function countModules($condition)
	{
		jimport('joomla.application.module.helper');

		$result = '';
		$words = explode(' ', $condition);
		for ($i = 0; $i < count($words); $i+=2)
		{
			// odd parts (modules)
			$name = strtolower($words[$i]);
			$words[$i] = ((isset($this->_buffer['modules'][$name])) && ($this->_buffer['modules'][$name] === false)) ? 0 : count(JModuleHelper::getModules($name));
		}

		$str = 'return ' . implode(' ', $words) . ';';

		return eval($str);
	}

    /**
     * Reorder main content / sidebars in the order selected by the user
     */
	private function reorderContent() {

	    /**
	     * regular patterns to identify every column
	     * Added id to avoid the annoying bug that avoids the user to use HTML5 tags
	     */
	    $patterns = array(  'sidebar1' => '/<aside(.*)id="sidebar1">(.*)<\/aside>/isU',
	                        'sidebar2' => '/<aside(.*)id="sidebar2">(.*)<\/aside>/isU',
	                        'main' => '/<section(.*)id="main"(.*)>(.*)<\/section>/isU'
	    );

	    // only this columns
	    $allowedColNames = array_keys($patterns);
	    $reorderedCols = array();
	    $reorderedContent = '';

	    // get column configuration
	    $columnCfg = $this->document->params->get('columns', 'sidebar1:3;main:6;sidebar2:3');
	    $colStrings = explode(';', $columnCfg);
	    if ($colStrings) {
	        foreach ($colStrings as $colString) {
	            list ($colName, $colWidth) = explode(':', $colString);
	            if(in_array($colName, $allowedColNames)) {
	                $reorderedCols[] = $colName;
	            }
	        }
	    }

	    // get column contents with regular expressions
	    $patternFound = false;
	    foreach ($patterns as $column => $pattern) {

	        // save the content into a variable
	        $$column = null;
	        if (preg_match($pattern, $this->template, $matches)) {
	            $$column = $matches[0];

	            $replacement = '';
	            // replace first column found with string '##wricolumns##' to reorder content later
	            if (!$patternFound) {
	                $replacement = '##wricolumns##';
	                $patternFound = true;
	            }
	            $this->template = preg_replace($pattern, $replacement, $this->template);
	        }
	    }

	    // if columns reordered and column content found replace contents
	    if ($reorderedCols && $patternFound) {
	        foreach ($reorderedCols as $colName) {
	            if (!is_null($$colName)) {
	                $reorderedContent .= $$colName;
	            }
	        }
	    }

	    $this->template = preg_replace('/##wricolumns##/isU', $reorderedContent, $this->template);

	    return $reorderedContent;

	}
}
