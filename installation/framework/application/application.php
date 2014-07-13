<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 *
 * This file may contain code from the Joomla! Platform, Copyright (c) 2005 -
 * 2012 Open Source Matters, Inc. This file is NOT part of the Joomla! Platform.
 * It is derivative work and clearly marked as such as per the provisions of the
 * GNU General Public License.
 */

defined('_AKEEBA') or die();

abstract class AApplication
{
	/** @var string The name (alias) of the application */
	protected $name = null;

	/** @var array The configuration parameters of the application */
	protected $config = array();

	/** @var string The name of the template's directory */
	protected $template = null;

	/** @var array An array of application instances */
	private static $instances = array();

	/** @var AInput The input object */
	public $input = null;

	/** @var ASession The session object */
	public $session = null;

	/** @var array The application message queue */
	public $messageQueue = array();

	/**
	 * Public constructor
	 *
	 * @param   array  $config  Configuration parameters
	 */
	public function __construct($config = array())
	{
		// Set the application name
		$this->name = $this->getName();

		// Load up the input
		$this->input = new AInput();

		// Create a session
		$this->session = ASession::getInstance();

		// Set up the template
		if (array_key_exists('template', $config))
		{
			$this->setTemplate($config['template']);
		}

		// If no template is specified, fall back to the default
		if (empty($this->template))
		{
			$this->setTemplate();
		}
	}

	/**
	 * Gets an instance of the application
	 *
	 * @param   string  $name    The name of the application (folder name)
	 * @param   array   $config  The configuration variables of the application
	 * @param   string  $prefix  The prefix of the class name of the application
	 *
	 * @return  AApplication
	 *
	 * @throws  AExceptionApp
	 */
	public static function getInstance($name = null, $config = array(), $prefix = 'Angie')
	{
		if (empty($name) && !empty(self::$instances))
		{
			$keys = array_keys(self::$instances);
			$name = array_shift($keys);
		}
		else
		{
			$name = 'angie';
		}

		if(!array_key_exists($name, self::$instances))
		{
			$filePath = __DIR__ . '/../../'.$name.'/application.php';
			$result = include_once($filePath);
			if ($result === false)
			{
				throw new AExceptionApp("The application '$name' was not found on this server");
			}

			$className = ucfirst($prefix) . 'Application';
			self::$instances[$name] = new $className($config);
		}

		return self::$instances[$name];
	}

	/**
	 * Initialises the application
	 */
	abstract public function initialise();

	/**
	 * Dispatches the application
	 */
	public function dispatch()
	{
		@ob_start();
		$dispatcher = ADispatcher::getAnInstance();
		$dispatcher->dispatch();
		$result = @ob_get_clean();

		$document = $this->getDocument();
		$document->setBuffer($result);
	}

	/**
	 * Renders the application
	 */
	public function render()
	{
		$this->getDocument()->render();
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   12.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is info.
	 *
	 * @return  void
	 */
	public function enqueueMessage($msg, $type = 'info')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->messageQueue))
		{
			$sessionQueue = $this->session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;
				$this->session->remove('application.queue');
			}
		}

		// Enqueue the message.
		$this->messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 */
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->messageQueue))
		{
			$sessionQueue = $this->session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;
				$this->session->remove('application.queue');
			}
		}

		return $this->messageQueue;
	}

	public function getMessageQueueFor($type = 'info')
	{
		$ret = array();

		$messageQueue = $this->getMessageQueue();

		if (count($messageQueue))
		{
			foreach ($messageQueue as $message)
			{
				if ($message['type'] == $type)
				{
					$ret[] = $message['message'];
				}
			}
		}

		return $ret;
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMessage method. If the headers have
	 * not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string   $url      The URL to redirect to. Can only be http/https URL
	 * @param   string   $msg      An optional message to display on redirect.
	 * @param   string   $msgType  An optional message type. Defaults to message.
	 * @param   boolean  $moved    True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void  Calls exit().
	 *
	 * @see     AApplication::enqueueMessage()
	 */
	public function redirect($url, $msg = '', $msgType = 'info', $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index\.php#', $url))
		{
			$url = AUri::base() . $url;
		}

		// Strip out any line breaks.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		/*
		 * If we don't start with a http we need to fix this before we proceed.
		 * We could validly start with something else (e.g. ftp), though this would
		 * be unlikely and isn't supported by this API.
		 */
		if (!preg_match('#^http#i', $url))
		{
			$uri = AURI::getInstance();
			$prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			if ($url[0] == '/')
			{
				// We just need the prefix since we have a path relative to the root.
				$url = $prefix . $url;
			}
			else
			{
				// It's relative to where we are now, so lets add that.
				$parts = explode('/', $uri->toString(array('path')));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url = $prefix . $path . $url;
			}
		}

		// If the message exists, enqueue it.
		if (trim($msg))
		{
			$this->enqueueMessage($msg, $msgType);
		}

		// Persist messages if they exist.
		if (count($this->messageQueue))
		{
			$this->session->set('application.queue', $this->messageQueue);
			$this->session->saveData();
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent())
		{
			echo "<script>document.location.href='" . htmlspecialchars($url) . "';</script>\n";
		}
		else
		{
			header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
			header('Location: ' . $url);
			header('Content-Type: text/html; charset=utf-8');
		}

		$this->close();
	}

	/**
	 * Creates and returns the document object
	 *
	 * @return  ADocument
	 */
	public function getDocument()
	{
		static $instance = null;

		if(is_null($instance))
		{
			$type = $this->input->getCmd('format', 'html');

			$instance = ADocument::getInstance($type);
		}

		return $instance;
	}

	/**
	 * Gets the name of the application by breaking down the application class'
	 * name. For example, FooApplication returns "foo".
	 *
	 * @return  string  The application name, all lowercase
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$class = get_class($this);
			$class = preg_replace('/(\s)+/', '_', $class);
			$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
			$class = explode('_', $class);

			return array_shift($class);
		}
		else
		{
			return $this->name;
		}
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function setTemplate($template = null)
	{
		if (!empty($template))
		{
			$templatePath = APATH_THEMES . '/' . $template;
			if (!is_dir($templatePath))
			{
				$template = null;
			}
		}

		if (empty($template))
		{
			$template = $this->getName();
		}

		$this->template = $template;
	}

	/**
	 * Returns the input object
	 *
	 * @return AInput
	 */
	public function getInput()
	{
		return $this->input;
	}
}