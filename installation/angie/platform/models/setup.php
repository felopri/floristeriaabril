<?php

/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */
defined('_AKEEBA') or die();

class AngieModelSetup extends AModel
{
	/**
	 * Cached copy of the configuration model
	 *
	 * @var  AngieModelConfiguration
	 */
	private $configModel = null;

	/**
	 * Overridden constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->configModel = AModel::getAnInstance('Configuration', 'AngieModel');
	}

	/**
	 * Return an object containing the configuration variables we read from the
	 * state or the request.
	 *
	 * @return  stdClass
	 */
	public function getStateVariables()
	{
		static $params = array();

		if(empty($params))
		{
			$methods = array('getSiteParamsVars', 'getFTPParamsVars', 'getSuperUsersVars');
			foreach ($methods as $method)
			{
				$temp = call_user_func(array($this, $method));
				if (is_array($temp))
				{
					$params = array_merge($params, $temp);
				}
			}
		}

		return (object) $params;
	}

	/**
	 * Gets the basic site parameters
	 *
	 * @return  array
	 */
	private function getSiteParamsVars()
	{
		$defaultTmpPath	 = APATH_ROOT . '/tmp';
		$defaultLogPath	 = APATH_ROOT . '/log';

		$ret = array(
			'sitename'		 => $this->getState('sitename', $this->configModel->get('sitename', 'Restored website')),
			'siteemail'		 => $this->getState('siteemail', $this->configModel->get('mailfrom', 'no-reply@example.com')),
			'emailsender'	 => $this->getState('emailsender', $this->configModel->get('fromname', 'Restored website')),
			'livesite'		 => $this->getState('livesite', $this->configModel->get('live_site', '')),
			'cookiedomain'	 => $this->getState('cookiedomain', $this->configModel->get('cookie_domain', '')),
			'cookiepath'	 => $this->getState('cookiepath', $this->configModel->get('cookie_path', '')),
			'tmppath'		 => $this->getState('tmppath', $this->configModel->get('tmp_path', $defaultTmpPath)),
			'logspath'		 => $this->getState('logspath', $this->configModel->get('log_path', $defaultLogPath)),
			'default_tmp'	 => $defaultTmpPath,
			'default_log'	 => $defaultLogPath,
			'site_root_dir'	 => APATH_ROOT,
		);

		// Deal with tmp and logs path
		if (!@is_dir($ret['tmppath']))
		{
			$ret['tmppath'] = $defaultTmpPath;
		}
		elseif (!@is_writable($ret['tmppath']))
		{
			$ret['tmppath'] = $defaultTmpPath;
		}

		if (!@is_dir($ret['logspath']))
		{
			$ret['logspath'] = $defaultLogPath;
		}
		elseif (!@is_writable($ret['logspath']))
		{
			$ret['logspath'] = $defaultLogPath;
		}

		return $ret;
	}

	/**
	 * Gets the FTP connection parameters
	 *
	 * @return  array
	 */
	private function getFTPParamsVars()
	{
		$ret = array(
			'ftpenable'	 => $this->getState('enableftp', $this->configModel->get('ftp_enable', 0)),
			'ftphost'	 => $this->getState('ftphost', $this->configModel->get('ftp_host', '')),
			'ftpport'	 => $this->getState('ftpport', $this->configModel->get('ftp_port', 21)),
			'ftpuser'	 => $this->getState('ftpuser', $this->configModel->get('ftp_user', '')),
			'ftppass'	 => $this->getState('ftppass', $this->configModel->get('ftp_pass', '')),
			'ftpdir'	 => $this->getState('ftpdir', $this->configModel->get('ftp_root', '')),
		);

		return $ret;
	}

	/**
	 * Returns the database connection variables for the default database.
	 *
	 * @return type
	 */
	private function getDbConnectionVars()
	{
		$model		 = AModel::getAnInstance('Database', 'AngieModel');
		$keys		 = $model->getDatabaseNames();
		$firstDbKey	 = array_shift($keys);

		return $model->getDatabaseInfo($firstDbKey);
	}

	private function getSuperUsersVars()
	{
		$ret = array();

		// Connect to the database
		$connectionVars = $this->getDbConnectionVars();
		try
		{
			$name = $connectionVars->dbtype;
			$options = array(
				'database'	 => $connectionVars->dbname,
				'select'	 => 1,
				'host'		 => $connectionVars->dbhost,
				'user'		 => $connectionVars->dbuser,
				'password'	 => $connectionVars->dbpass,
				'prefix'	 => $connectionVars->prefix,
				//'port'				=> $connectionVars->dbport,
			);
			$db		 = ADatabaseFactory::getInstance()->getDriver($name, $options);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Find the Super User groups
		try
		{
			$query = $db->getQuery(true)
				->select($db->qn('rules'))
				->from($db->qn('#__assets'))
				->where($db->qn('parent_id') . ' = ' . $db->q(0));
			$db->setQuery($query, 0, 1);
			$rulesJSON	 = $db->loadResult();
			$rules		 = json_decode($rulesJSON, true);

			$rawGroups = $rules['core.admin'];
			$groups = array();

			if (empty($rawGroups))
			{
				return $ret;
			}

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->q($g);
				}
			}

			if (empty($groups))
			{
				return $ret;
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user IDs of users belonging to the SA groups
		try
		{
			$query = $db->getQuery(true)
				->select($db->qn('user_id'))
				->from($db->qn('#__user_usergroup_map'))
				->where($db->qn('group_id') . ' IN(' . implode(',', $groups) . ')' );
			$db->setQuery($query);
			$rawUserIDs = $db->loadColumn(0);

			if (empty($rawUserIDs))
			{
				return $ret;
			}

			$userIDs = array();

			foreach ($rawUserIDs as $id)
			{
				$userIDs[] = $db->q($id);
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Administrator users
		try
		{
			$query = $db->getQuery(true)
				->select(array(
					$db->qn('id'),
					$db->qn('username'),
					$db->qn('email'),
				))->from($db->qn('#__users'))
				->where($db->qn('id'). ' IN(' . implode(',', $userIDs) . ')');
			$db->setQuery($query);
			$ret['superusers'] = $db->loadObjectList(0);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}

	/**
	 * Apply the settings to the configuration.php file and the database
	 */
	public function applySettings()
	{
		// Apply the Super Administrator changes
		$this->applySuperAdminChanges();

		// Get the state variables and update the global configuration
		$stateVars = $this->getStateVariables();
		// -- General settings
		$this->configModel->set('sitename', $stateVars->sitename);
		$this->configModel->set('mailfrom', $stateVars->siteemail);
		$this->configModel->set('fromname', $stateVars->emailsender);
		$this->configModel->set('live_site', $stateVars->livesite);
		$this->configModel->set('cookie_domain', $stateVars->cookiedomain);
		$this->configModel->set('cookie_path', $stateVars->cookiepath);
		$this->configModel->set('tmp_path', $stateVars->tmppath);
		$this->configModel->set('log_path', $stateVars->logspath);

		// -- FTP settings
		$this->configModel->set('ftp_enable', ($stateVars->ftpenable ? 1 : 0));
		$this->configModel->set('ftp_host', $stateVars->ftphost);
		$this->configModel->set('ftp_port', $stateVars->ftpport);
		$this->configModel->set('ftp_user', $stateVars->ftpuser);
		$this->configModel->set('ftp_pass', $stateVars->ftppass);
		$this->configModel->set('ftp_root', $stateVars->ftpdir);

		// -- Database settings
		$connectionVars = $this->getDbConnectionVars();
		$this->configModel->set('dbtype', $connectionVars->dbtype);
		$this->configModel->set('host', $connectionVars->dbhost);
		$this->configModel->set('user', $connectionVars->dbuser);
		$this->configModel->set('password', $connectionVars->dbpass);
		$this->configModel->set('db', $connectionVars->dbname);
		$this->configModel->set('dbprefix', $connectionVars->prefix);

        // Let's get the old secret key, since we need it to update encrypted stored data
        $oldsecret = $this->configModel->get('secret', '');
        $newsecret = $this->genRandomPassword(32);

		// -- Override the secret key
		$this->configModel->set('secret', $newsecret);

        $this->updateEncryptedData($oldsecret, $newsecret);

		$this->configModel->saveToSession();

		// Get the configuration.php file and try to save it
		$configurationPHP = $this->configModel->getFileContents();
		$filepath = APATH_SITE . '/configuration.php';

		if (! @file_put_contents($filepath, $configurationPHP))
		{
			if ($this->configModel->get('ftp_enable', 0))
			{
				// Try with FTP
				$ftphost = $this->configModel->get('ftp_host', '');
				$ftpport = $this->configModel->get('ftp_port', '');
				$ftpuser = $this->configModel->get('ftp_user', '');
				$ftppass = $this->configModel->get('ftp_pass', '');
				$ftproot = $this->configModel->get('ftp_root', '');
				try
				{
					$ftp = AFtp::getInstance($ftphost, $ftpport, array('type' => FTP_AUTOASCII), $ftpuser, $ftppass);
					$ftp->chdir($ftproot);
					$ftp->write('configuration.php', $configurationPHP);
					$ftp->chmod('configuration.php', 0644);
				}
				catch (Exception $exc)
				{
					// Fail gracefully
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		return true;
	}

    /**
     * This method will update the data encrypted with the old secret key, encrypting it again using
     * the new secret key
     *
     * @param   string  $oldsecret  Old secret key
     * @param   string  $newsecret  New secret key
     *
     * @return  void
     */
    private function updateEncryptedData($oldsecret, $newsecret)
    {
        $this->updateTFA($oldsecret, $newsecret);
    }

    private function updateTFA($oldsecret, $newsecret)
    {
        ASession::getInstance()->set('tfa_warning', false);

        // There is no TFA in Joomla < 3.2
        $jversion = ASession::getInstance()->get('jversion');
        if(version_compare($jversion, '3.2', 'lt'))
        {
            return;
        }

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
                    ->select('COUNT(extension_id)')
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('type').' = '.$db->q('plugin'))
                    ->where($db->qn('folder').' = '.$db->q('twofactorauth'))
                    ->where($db->qn('enabled').' = '.$db->q('1'));
        $count = $db->setQuery($query)->loadResult();

        // No enabled plugin, there is no point in continuing
        if(!$count)
        {
            return;
        }

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->qn('#__users'))
                    ->where($db->qn('otpKey').' != '.$db->q(''))
                    ->where($db->qn('otep').' != '.$db->q(''));

        $users = $db->setQuery($query)->loadObjectList();

        // There are no users with TFA configured, let's stop here
        if(!$users)
        {
            return;
        }

        // Otherwise I'll get a blank page
        if(!defined('FOF_INCLUDED'))
        {
            define('FOF_INCLUDED', 1);
        }

        include_once APATH_LIBRARIES.'/fof/encrypt/aes.php';

        // Does this host support AES?
        if(!FOFEncryptAes::isSupported())
        {
            // If not, set a flag, so we will display a big, fat warning in the finalize screen
            ASession::getInstance()->set('tfa_warning', true);

            // Let's disable them
            $query = $db->getQuery(true)
                        ->update($db->qn('#__extensions'))
                        ->set($db->qn('enabled').' = '.$db->q('0'))
                        ->where($db->qn('type').' = '.$db->q('plugin'))
                        ->where($db->qn('folder').' = '.$db->q('twofactorauth'));
            $db->setQuery($query)->execute();

            return;
        }

        $oldaes = new FOFEncryptAes($oldsecret, 256);
        $newaes = new FOFEncryptAes($newsecret, 256);

        foreach($users as $user)
        {
            $update = (object) array(
                'id'     => $user->id,
                'otpKey' => '',
                'otep'   => ''
            );

            list($method, $otpKey) = explode(':', $user->otpKey);
            $update->otpKey = $oldaes->decryptString($otpKey);
            $update->otpKey = $method.':'.$newaes->encryptString($update->otpKey);

            $update->otep = $oldaes->decryptString($user->otep);
            $update->otep = $newaes->encryptString($update->otep);

            $db->updateObject('#__users', $update, 'id');
        }
    }

	private function applySuperAdminChanges()
	{
		// Get the Super User ID. If it's empty, skip.
		$id = $this->getState('superuserid', 0);
		if (!$id)
		{
			return false;
		}

		// Get the Super User email and password
		$email = $this->getState('superuseremail', '');
		$password1 = $this->getState('superuserpassword', '');
		$password2 = $this->getState('superuserpasswordrepeat', '');

		// If the email is empty but the passwords are not, fail
		if (empty($email))
		{
			if(empty($password1) && empty($password2))
			{
				return false;
			}
			else
			{
				throw new Exception(AText::_('SETUP_ERR_EMAILEMPTY'));
			}
		}

		// If the passwords are empty, skip
		if (empty($password1) && empty($password2))
		{
			return false;
		}

		// Make sure the passwords match
		if ($password1 != $password2)
		{
			throw new Exception(AText::_('SETUP_ERR_PASSWORDSDONTMATCH'));
		}

		// Connect to the database
		$connectionVars = $this->getDbConnectionVars();
		$name = $connectionVars->dbtype;
		$options = array(
			'database'	 => $connectionVars->dbname,
			'select'	 => 1,
			'host'		 => $connectionVars->dbhost,
			'user'		 => $connectionVars->dbuser,
			'password'	 => $connectionVars->dbpass,
			'prefix'	 => $connectionVars->prefix,
			//'port'				=> $connectionVars->dbport,
		);
		$db		 = ADatabaseFactory::getInstance()->getDriver($name, $options);

		// Create a new salt and encrypted password
		$salt = $this->genRandomPassword(32);
		$crypt = md5($password1.$salt);
		$cryptpass = $crypt.':'.$salt;

		// Update the database record
		$query = $db->getQuery(true)
			->update($db->qn('#__users'))
			->set($db->qn('password') . ' = ' . $db->q($cryptpass))
			->set($db->qn('email') . ' = ' . $db->q($email))
			->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	private function genRandomPassword($length = 8)
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';

		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;
	}

    private function getDatabase()
    {
        $connectionVars = $this->getDbConnectionVars();
        $name = $connectionVars->dbtype;
        $options = array(
            'database'	 => $connectionVars->dbname,
            'select'	 => 1,
            'host'		 => $connectionVars->dbhost,
            'user'		 => $connectionVars->dbuser,
            'password'	 => $connectionVars->dbpass,
            'prefix'	 => $connectionVars->prefix,
            //'port'				=> $connectionVars->dbport,
        );
        $db		 = ADatabaseFactory::getInstance()->getDriver($name, $options);

        return $db;
    }
}