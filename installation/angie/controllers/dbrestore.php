<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerDbrestore extends AController
{
	public function start()
	{
		$key = $this->input->get('key', null);
		$data = $this->input->get('dbinfo', null, 'array');

		if (empty($key) || empty($data['dbtype']))
		{
			$result = array(
				'percent'	=> 0,
				'restored'	=> 0,
				'total'		=> 0,
				'eta'		=> 0,
				'error'		=> AText::_('DATABASE_ERR_INVALIDKEY'),
				'done'		=> 1,
			);
			echo json_encode($result);
			return;
		}

		$model = AModel::getAnInstance('Database', 'AngieModel');
		$savedData = $model->getDatabaseInfo($key);
		if (is_object($savedData))
		{
			$savedData = (array)$savedData;
		}
		if (!is_array($savedData))
		{
			$savedData = array();
		}

		$data = array_merge($savedData, $data);

		$model->setDatabaseInfo($key, $data);
		$model->saveDatabasesIni();

		try
		{
			$restoreEngine = ADatabaseRestore::getInstance($key, $data);
			$result = array(
				'percent'	=> 0,
				'restored'	=> 0,
				'total'		=> $restoreEngine->getTotalSize(true),
				'eta'		=> '–––',
				'error'		=> '',
				'done'		=> 0,
			);
		}
		catch (Exception $exc)
		{
			$result = array(
				'percent'	=> 0,
				'restored'	=> 0,
				'total'		=> 0,
				'eta'		=> 0,
				'error'		=> $exc->getMessage(),
				'done'		=> 1,
			);
		}

		echo json_encode($result);
	}

	public function step()
	{
		$key = $this->input->get('key', null);

		$model = AModel::getAnInstance('Database', 'AngieModel');
		$data = $model->getDatabaseInfo($key);

		try
		{
			$restoreEngine = ADatabaseRestore::getInstance($key, $data);
			$result = $restoreEngine->stepRestoration();
		}
		catch (Exception $exc)
		{
			$result = array(
				'percent'	=> 0,
				'restored'	=> 0,
				'total'		=> 0,
				'eta'		=> 0,
				'error'		=> $exc->getMessage(),
				'done'		=> 1,
			);
		}

		echo json_encode($result);
	}
}