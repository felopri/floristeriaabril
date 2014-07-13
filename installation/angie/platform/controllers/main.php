<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerMain extends AController
{
	/**
	 * Try to detect the Joomla! version
	 */
	public function detectversion()
	{
		$this->getThisModel()->detectVersion();

		echo json_encode(true);
	}

	/**
	 * Try to read configuration.php
	 */
	public function getconfig()
	{
		// Load the default configuration and save it to the session
		$data = $this->input->getData();

        /** @var AngieModelConfiguration $model */
		$model = AModel::getAnInstance('Configuration', 'AngieModel');
		$this->input->setData($data);
		ASession::getInstance()->saveData();

		// Try to load the configuration from the site's configuration.php
		$filename = APATH_SITE . '/configuration.php';
		if (file_exists($filename))
		{
			$vars = $model->loadFromFile($filename);
			foreach ($vars as $k => $v)
			{
				$model->set($k, $v);
			}
			ASession::getInstance()->saveData();

			echo json_encode(true);
		}
		else
		{
			echo json_encode(false);
		}

		//AApplication::getInstance()->close();
	}

	public function startover()
	{
		ASession::getInstance()->reset();
		ASession::getInstance()->saveData();
		$this->setRedirect('index.php?view=main');
	}
}