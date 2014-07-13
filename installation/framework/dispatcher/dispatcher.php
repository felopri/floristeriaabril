<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

class ADispatcher
{
	/** @var array Configuration variables */
	protected $config = array();

	/** @var AInput Input variables */
	protected $input = array();

	/** @var string The name of the default view, in case none is specified */
	public $defaultView = 'main';
	
	/**
	 * Get a static (Singleton) instance of a particular Dispatcher
	 * 
	 * @staticvar array $instances Holds the array of Dispatchers ANGI knows about
	 * 
	 * @param   string  $option  The component name
	 * @param   string  $view    The View name
	 * @param   array   $config  Configuration data
	 * 
	 * @return  ADispatcher
	 */
	public static function &getAnInstance($option = null, $view = null, $config = array())
	{
		static $instances = array();

		$hash = $option.$view;
		if (!array_key_exists($hash, $instances))
		{
			$instances[$hash] = self::getTmpInstance($option, $view, $config);
		}

		return $instances[$hash];
	}
	
	/**
	 * Gets a temporary instance of a Dispatcher
	 * 
	 * @param   string  $option  The component name
	 * @param   string  $view    The View name
	 * @param   array   $config  Configuration data
	 * 
	 * @return ADispatcher
	 */
	public static function &getTmpInstance($option = null, $view = null, $config = array())
	{
		if(array_key_exists('input', $config))
		{
			if ($config['input'] instanceof AInput)
			{
				$input = $config['input'];
			}
			else 
			{
				if (!is_array($config['input']))
				{
					$config['input'] = (array)$config['input'];
				}
				$config['input'] = array_merge($_REQUEST, $config['input']);
				$input = new AInput($config['input']);
			}
		}
		else
		{
			$input = new AInput();
		}
		
		$defaultApp = AApplication::getInstance()->getName();
		
		if (!is_null($option))
		{
			$config['option'] = $option;
		}
		else
		{
			$config['option'] = $input->getCmd('option', $defaultApp);
		}
		if (!is_null($view))
		{
			$config['view'] = $view;
		}
		else
		{
			$config['view'] = $input->getCmd('view','');
		}
		
		$input->set('option',	$config['option']);
		$input->set('view',		$config['view']);
		$config['input'] = $input;

		$className = ucfirst($config['option']).'Dispatcher';

		if (!class_exists( $className ))
		{
			$basePath = APATH_INSTALLATION;

			$searchPaths = array(
				$basePath.'/'.$config['option'] . '/platform',
				$basePath.'/'.$config['option'].'/platform/dispatchers',
				$basePath.'/'.$config['option'],
				$basePath.'/'.$config['option'].'/dispatchers',
			);
			if (array_key_exists('searchpath', $config))
			{
				array_unshift($searchPaths, $config['searchpath']);
			}

			$path = AUtilsPath::find(
				$searchPaths,
				'dispatcher.php'
			);

			if ($path)
			{
				require_once $path;
			}
		}

		if (!class_exists($className) && class_exists($className . 'Default'))
		{
			$className = $className . 'Default';
		}
		elseif (!class_exists( $className ))
		{
			$className = 'ADispatcher';
		}

		$instance = new $className($config);

		return $instance;
	}
	
	/**
	 * Public constructor
	 * 
	 * @param   array  $config  The configuration variables
	 */
	public function __construct($config = array()) {
		// Cache the config
		$this->config = $config;

		// Get the input for this MVC triad
		if(array_key_exists('input', $config)) {
			$this->input = $config['input'];
		} else {
			$this->input = new AInput();
		}

		// Get the default values for the component and view names
		$defaultApp = AApplication::getInstance()->getName();
		$this->component	= $this->input->getCmd('option', $defaultApp);
		$this->view			= $this->input->getCmd('view', $this->defaultView);
		$this->layout		= $this->input->getCmd('layout', null);
		if (empty($this->view))
		{
			$this->view = $this->defaultView;
		}

		// Overrides from the config
		if (array_key_exists('option', $config))
		{
			$this->component = $config['option'];
		}
		if (array_key_exists('view', $config))
		{
			if (empty($config['view']))
			{
				$this->view = $this->view;
			}
			else
			{
				$this->view = $config['view'];
			}
		}
		if (array_key_exists('layout', $config))
		{
			$this->layout = $config['layout'];
		}

		$this->input->set('option',	$this->component);
		$this->input->set('view',	$this->view);
		$this->input->set('layout',	$this->layout);
	}
	
	/**
	 * The main code of the Dispatcher. It spawns the necessary controller and
	 * runs it.
	 * 
	 * @return  null
	 * 
	 * @throws  Exception
	 */
	public function dispatch()
	{
		if (!$this->onBeforeDispatch())
		{
			// For json, don't use normal 403 page, but a json encoded message
			if ($this->input->get('format', '') == 'json')
			{
				echo json_encode(array('code' => '403', 'error' => $this->getError()));
				exit();
			}

			throw new Exception(AText::_('ANGI_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Get and execute the controller
		$defaultApp = AApplication::getInstance()->getName();
		$option	= $this->input->getCmd('option', $defaultApp);
		$view	= $this->input->getCmd('view', $this->defaultView);
		$task	= $this->input->getCmd('task', 'default');
		if (empty($task))
		{
			$task = $this->getTask($view);
		}
		
		$this->input->set('view',$view);
		$this->input->set('task',$task);

		$config = $this->config;
		$config['input'] = $this->input;

		$controller = AController::getTmpInstance($option, $view, $config);
		$status = $controller->execute($task);

		if($status === false) {
			throw new Exception(AText::_('ANGI_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		if(!$this->onAfterDispatch()) {
			throw new Exception(AText::_('ANGI_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		$controller->redirect();
	}

	/**
	 * Executes right before the dispatcher tries to instantiate and run the
	 * controller.
	 * 
	 * @return  boolean  Return false to abort
	 */
	public function onBeforeDispatch()
	{
		return true;
	}

	/**
	 * Executes right after the dispatcher runs the controller.
	 * 
	 * @return  boolean  Return false to abort
	 */
	public function onAfterDispatch()
	{
		return true;
	}
	

}