<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelMain extends AModel
{
	/**
	 * Try to detect the Joomla! version in use
	 */
	public function detectVersion()
	{
		$ret = '2.5.0';

		$filename = APATH_LIBRARIES . '/cms/version/version.php';

		if (file_exists($filename))
		{
			include_once $filename;
			$jv = new JVersion();
			$ret =$jv->getShortVersion();
		}

		$jVersion = ASession::getInstance()->set('jversion', $ret);
		ASession::getInstance()->saveData();
	}

	/**
	 * Are all required settings met?
	 *
	 * @staticvar   null|bool  $ret  The cached result
	 *
	 * @return  bool
	 */
	public function isRequiredMet()
	{
		static $ret = null;

		if (is_null($ret))
		{
			$required = $this->getRequired();
			$ret = true;
			foreach ($required as $setting)
			{
				if ($setting['warning'])
				{
					continue;
				}

				$ret = $ret && $setting['current'];
				if (!$ret)
				{
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Are all recommended settings met?
	 *
	 * @staticvar   null|bool  $ret  The cached result
	 *
	 * @return  bool
	 */
	public function isRecommendedMet()
	{
		static $ret = null;

		if (is_null($ret))
		{
			$required = $this->getRequired();
			$ret = true;
			foreach ($required as $setting)
			{
				$ret = $ret && ($setting['current'] == $setting['recommended']);
				if (!$ret)
				{
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Get the required settings analysis
	 *
	 * @return  array
	 */
	public function getRequired()
	{
		static $phpOptions = array();

		if (empty($phpOptions))
		{
			$jVersion = ASession::getInstance()->get('jversion');
			if (version_compare($jVersion, '3.0.0', 'lt'))
			{
				$minPHPVersion = '5.2.4';
			}
			else
			{
				$minPHPVersion = '5.3.1';
			}

			$phpOptions[] = array (
				'label'		=> AText::sprintf('MAIN_LBL_REQ_PHP_VERSION', $minPHPVersion),
				'current'	=> version_compare(phpversion(), $minPHPVersion, 'ge'),
				'warning'	=> false,
			);

			if(version_compare($jVersion, '3.0.0', 'gt'))
			{
				$phpOptions[] = array (
					'label'		=> AText::_('MAIN_LBL_REQ_MCGPCOFF'),
					'current'	=> (ini_get('magic_quotes_gpc') == false),
					'warning'	=> false,
				);

				$phpOptions[] = array (
					'label'		=> AText::_('MAIN_LBL_REQ_REGGLOBALS'),
					'current'	=> (ini_get('register_globals') == false),
					'warning'	=> false,
				);
			}

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_ZLIB'),
				'current'	=> extension_loaded('zlib'),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_XML'),
				'current'	=> extension_loaded('xml'),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_DATABASE'),
				'current'	=> (function_exists('mysql_connect') || function_exists('mysqli_connect') || function_exists('pg_connect') || function_exists('sqlsrv_connect')),
				'warning'	=> false,
			);

			if (extension_loaded( 'mbstring' ))
			{
				$option = array (
					'label'		=> AText::_( 'MAIN_REQ_MBLANGISDEFAULT' ),
					'current'	=> (strtolower(ini_get('mbstring.language')) == 'neutral'),
					'warning'	=> false,
				);
				$option['notice'] = $option['current'] ? null : AText::_('MAIN_MSG_NOTICEMBLANGNOTDEFAULT');
				$phpOptions[] = $option;

				$option = array (
					'label'		=> AText::_('MAIN_REQ_MBSTRINGOVERLOAD'),
					'current'	=> (ini_get('mbstring.func_overload') == 0),
					'warning'	=> false,
				);
				$option['notice'] = $option['current'] ? null : AText::_('MAIN_MSG_NOTICEMBSTRINGOVERLOAD');
				$phpOptions[] = $option;
			}

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_INIPARSER'),
				'current'	=> $this->getIniParserAvailability(),
				'warning'	=> false,
			);

			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_JSON'),
				'current'	=> function_exists('json_encode') && function_exists('json_decode'),
				'warning'	=> false,
			);

			$cW = (@ file_exists('../configuration.php') && @is_writable('../configuration.php')) || @is_writable('../');
			$phpOptions[] = array (
				'label'		=> AText::_('MAIN_LBL_REQ_CONFIGURATIONPHP'),
				'current'	=> $cW,
				'notice'	=> $cW ? null : AText::_('MAIN_MSG_CONFIGURATIONPHP'),
				'warning'	=> true
			);
		}

		return $phpOptions;
	}

	public function getRecommended()
	{
		static $phpOptions = array();

		if (empty($phpOptions))
		{
			$jVersion = ASession::getInstance()->get('jversion');

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_SAFEMODE'),
				'current'		=> (bool) ini_get('safe_mode'),
				'recommended'	=> false,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_DISPERRORS'),
				'current'		=> (bool) ini_get('display_errors'),
				'recommended'	=> false,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_UPLOADS'),
				'current'		=> (bool) ini_get('file_uploads'),
				'recommended'	=> true,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_MCR'),
				'current'		=> (bool) ini_get('magic_quotes_runtime'),
				'recommended'	=> false,
			);

			if (version_compare($jVersion, '3.0.0', 'lt'))
			{
				$phpOptions[] = array(
					'label'			=> AText::_('MAIN_REC_MCGPC'),
					'current'		=> (bool) ini_get('magic_quotes_gpc'),
					'recommended'	=> false,
				);
			}

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_OUTBUF'),
				'current'		=> (bool) ini_get('output_buffering'),
				'recommended'	=> false,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_SESSIONAUTO'),
				'current'		=> (bool) ini_get('session.auto_start'),
				'recommended'	=> false,
			);

			$phpOptions[] = array(
				'label'			=> AText::_('MAIN_REC_NATIVEZIP'),
				'current'		=> function_exists('zip_open') && function_exists('zip_read'),
				'recommended'	=> true,
			);

		}

		return $phpOptions;
	}

	/**
	 * Checks the availability of the parse_ini_file and parse_ini_string functions.
	 *
	 * @return	boolean
	 */
	public function getIniParserAvailability()
	{
		$disabled_functions = ini_get('disable_functions');

		if (!empty($disabled_functions))
		{
			// Attempt to detect them in the disable_functions black list
			$disabled_functions = explode(',', trim($disabled_functions));
			$number_of_disabled_functions = count($disabled_functions);

			for ($i = 0; $i < $number_of_disabled_functions; $i++)
			{
				$disabled_functions[$i] = trim($disabled_functions[$i]);
			}

			$result = !in_array('parse_ini_string', $disabled_functions);
		} else {
			// Attempt to detect their existence; even pure PHP implementation of them will trigger a positive response, though.
			$result = function_exists('parse_ini_string');
		}

		return $result;
	}

	public function getExtraInfo()
	{
		static $extraInfo = null;

		if (is_null($extraInfo))
		{
			$filename = APATH_INSTALLATION . '/extrainfo.ini';
			if (file_exists($filename))
			{
				$parsedData = parse_ini_file($filename, false);
				$extraInfo = array();

				if(array_key_exists('host', $parsedData))
				{
					$extraInfo['host'] = array(
						'label'		=> AText::_('MAIN_EXTRAINFO_HOST'),
						'current'	=> $parsedData['host']
					);
				}
				if(array_key_exists('backup_date', $parsedData))
				{
					$extraInfo['backup_date'] = array(
						'label'		=> AText::_('MAIN_EXTRAINFO_BACKUPDATE'),
						'current'	=> $parsedData['backup_date'] . ' UTC'
					);
				}
				if(array_key_exists('akeeba_version', $parsedData))
				{
					$extraInfo['akeeba_version'] = array(
						'label'		=> AText::_('MAIN_EXTRAINFO_AKEEBAVERSION'),
						'current'	=> $parsedData['akeeba_version']
					);
				}
				if(array_key_exists('php_version', $parsedData))
				{
					$extraInfo['php_version'] = array(
						'label'		=> AText::_('MAIN_EXTRAINFO_PHPVERSION'),
						'current'	=> $parsedData['php_version']
					);
				}
			}
		}

		return $extraInfo;
	}

	/**
	 * Resets the database connection information of all databases
	 */
	public function resetDatabaseConnectionInformation()
	{
		$model = AModel::getAnInstance('Database', 'AngieModel');
		$databasesIni = $model->getDatabasesIni();

		$temp = array();
		foreach ($databasesIni as $key => $data)
		{
			$data['dbhost'] = '';
			$data['dbuser'] = '';
			$data['dbpass'] = '';
			$data['dbname'] = '';

			$model->setDatabaseInfo($key, $data);
		}

		$model->saveDatabasesIni();

		ASession::getInstance()->set('main.resetdbinfo', true);
	}
}