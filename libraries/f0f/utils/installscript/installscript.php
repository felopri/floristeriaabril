<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('F0F_INCLUDED') or die;

JLoader::import('joomla.filesystem.folder');
JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.installer.installer');
JLoader::import('joomla.utilities.date');

/**
 * A helper class which you can use to create component installation scripts
 */
abstract class F0FUtilsInstallscript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_foobar';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Foobar Component';

	/**
	 * The list of extra modules and plugins to install on component installation / update and remove on component
	 * uninstallation.
	 *
	 * @var   array
	 */
	protected $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(),
			'site'  => array()
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'system' => array(),
		)
	);

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = array(
		// modules => { (folder) => { (module) }* }*
		'modules' => array(
			'admin' => array(),
			'site'  => array()
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array(
			'system' => array(),
		)
	);

	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	protected $removeFilesFree = array(
		'files'   => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		),
		'folders' => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		)
	);

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'   => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
		),
		'folders' => array(
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
		)
	);

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = array(
		// Use just the filename, e.g.
		// 'my-cron-script.php'
	);

	/**
	 * The path inside your package where cli scripts are stored
	 *
	 * @var   string
	 */
	protected $cliSourcePath = 'cli';

	/**
	 * The path inside your package where FOF is stored
	 *
	 * @var   string
	 */
	protected $fofSourcePath = 'fof';

	/**
	 * The path inside your package where Akeeba Strapper is stored
	 *
	 * @var   string
	 */
	protected $strapperSourcePath = 'strapper';

	/**
	 * The path inside your package where extra modules are stored
	 *
	 * @var   string
	 */
	protected $modulesSourcePath = 'modules';

	/**
	 * The path inside your package where extra plugins are stored
	 *
	 * @var   string
	 */
	protected $pluginsSourcePath = 'plugins';

	/**
	 * Is the schemaXmlPath class variable a relative path? If set to true the schemaXmlPath variable contains a path
	 * relative to the component's back-end directory. If set to false the schemaXmlPath variable contains an absolute
	 * filesystem path.
	 *
	 * @var   boolean
	 */
	protected $schemaXmlPathRelative = true;

	/**
	 * The path where the schema XML files are stored. Its contents depend on the schemaXmlPathRelative variable above
	 * true        => schemaXmlPath contains a path relative to the component's back-end directory
	 * false    => schemaXmlPath contains an absolute filesystem path
	 *
	 * @var string
	 */
	protected $schemaXmlPath = 'sql/xml';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.3.3';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '2.5.6';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '3.9.99';

	/**
	 * Is this the paid version of the extension? This only determines which files / extensions will be removed.
	 *
	 * @var   boolean
	 */
	protected $isPaid = false;

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string     $type   Installation type (install, update, discover_install)
	 * @param   JInstaller $parent Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		// Check the minimum PHP version
		if (!empty($this->minimumPHPVersion))
		{
			if (defined('PHP_VERSION'))
			{
				$version = PHP_VERSION;
			}
			elseif (function_exists('phpversion'))
			{
				$version = phpversion();
			}
			else
			{
				$version = '5.0.0'; // all bets are off!
			}

			if (!version_compare($version, $this->minimumPHPVersion, 'ge'))
			{
				$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this component</p>";

				if (version_compare(JVERSION, '3.0', 'gt'))
				{
					JLog::add($msg, JLog::WARNING, 'jerror');
				}
				else
				{
					JError::raiseWarning(100, $msg);
				}

				return false;
			}
		}

		// Check the minimum Joomla! version
		if (!empty($this->minimumJoomlaVersion) && !version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";

			if (version_compare(JVERSION, '3.0', 'gt'))
			{
				JLog::add($msg, JLog::WARNING, 'jerror');
			}
			else
			{
				JError::raiseWarning(100, $msg);
			}

			return false;
		}

		// Check the maximum Joomla! version
		if (!empty($this->maximumJoomlaVersion) && !version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";

			if (version_compare(JVERSION, '3.0', 'gt'))
			{
				JLog::add($msg, JLog::WARNING, 'jerror');
			}
			else
			{
				JError::raiseWarning(100, $msg);
			}

			return false;
		}

		// Workarounds for notorious JInstaller bugs we submitted patches for but were rejected – yet the bugs were never
		// fixed. Way to go, Joomla!...
		if (in_array($type, array('install')))
		{
			// Bugfix for "Database function returned no error"
			$this->bugfixDBFunctionReturnedNoError();
		}
		elseif ($type != 'discover_install')
		{
			// Bugfix for "Can not build admin menus"
			$this->bugfixCantBuildAdminMenus();
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string     $type   install, update or discover_update
	 * @param   JInstaller $parent Parent object
	 */
	public function postflight($type, $parent)
	{
		// Install or update database
		$dbInstaller = new F0FDatabaseInstaller(array(
			'dbinstaller_directory' =>
				($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
				$this->schemaXmlPath
		));
		$dbInstaller->updateSchema();

		// Install subextensions
		$status = $this->installSubextensions($parent);

		// Uninstall obsolete subextensions
		$uninstall_status = $this->uninstallObsoleteSubextensions($parent);

		// Install FOF
		$fofInstallationStatus = $this->installFOF($parent);

		// Install Akeeba Straper
		$strapperInstallationStatus = $this->installStrapper($parent);

		// Which files should I remove?
		if ($this->isPaid)
		{
			// This is the paid version, only remove the removeFilesAllVersions files
			$removeFiles = $this->removeFilesAllVersions;
		}
		else
		{
			// This is the free version, remove the removeFilesAllVersions and removeFilesFree files
			$removeFiles = array('files' => array(), 'folders' => array());

			if (isset($this->removeFilesAllVersions['files']))
			{
				if (isset($this->removeFilesFree['files']))
				{
					$removeFiles['files'] = array_merge($this->removeFilesAllVersions['files'], $this->removeFilesFree['files']);
				}
				else
				{
					$removeFiles['files'] = $this->removeFilesAllVersions['files'];
				}
			}
			elseif (isset($this->removeFilesFree['files']))
			{
				$removeFiles['files'] = $this->removeFilesFree['files'];
			}

			if (isset($this->removeFilesAllVersions['folders']))
			{
				if (isset($this->removeFilesFree['folders']))
				{
					$removeFiles['folders'] = array_merge($this->removeFilesAllVersions['folders'], $this->removeFilesFree['folders']);
				}
				else
				{
					$removeFiles['folders'] = $this->removeFilesAllVersions['folders'];
				}
			}
			elseif (isset($this->removeFilesFree['folders']))
			{
				$removeFiles['folders'] = $this->removeFilesFree['folders'];
			}
		}

		$this->removeFilesAndFolders($removeFiles);

		// Copy the CLI files (if any)
		$this->copyCliFiles($parent);

		// Show the post-installation page
		$this->renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent);

		// Clear the FOF cache
		$platform = F0FPlatform::getInstance();

		if (method_exists($platform, 'clearCache'))
		{
			F0FPlatform::getInstance()->clearCache();
		}
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   JInstaller $parent The parent object
	 */
	public function uninstall($parent)
	{
		// Uninstall database
		$dbInstaller = new F0FDatabaseInstaller(array(
			'dbinstaller_directory' =>
				($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
				$this->schemaXmlPath
		));
		$dbInstaller->removeSchema();

		// Uninstall modules and plugins
		$status = $this->uninstallSubextensions($parent);

		// Show the post-uninstallation page
		$this->renderPostUninstallation($status, $parent);
	}

	/**
	 * Copies the CLI scripts into Joomla!'s cli directory
	 *
	 * @param JInstaller $parent
	 */
	protected function copyCliFiles($parent)
	{
		$src = $parent->getParent()->getPath('source');

		foreach ($this->cliScriptFiles as $script)
		{
			if (JFile::exists(JPATH_ROOT . '/cli/' . $script))
			{
				JFile::delete(JPATH_ROOT . '/cli/' . $script);
			}

			if (JFile::exists($src . '/cli/' . $script))
			{
				JFile::copy($src . '/cli/' . $script, JPATH_ROOT . '/cli/' . $script);
			}
		}
	}

	/**
	 * Renders the message after installing or upgrading the component
	 */
	protected function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
	{
		$rows = 0;
		?>
		<table class="adminlist table table-striped" width="100%">
			<thead>
			<tr>
				<th class="title" colspan="2">Extension</th>
				<th width="30%">Status</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="3"></td>
			</tr>
			</tfoot>
			<tbody>
			<tr class="row<?php echo($rows++ % 2); ?>">
				<td class="key" colspan="2"><?php echo $this->componentTitle ?></td>
				<td><strong style="color: green">Installed</strong></td>
			</tr>
			<?php if ($fofInstallationStatus['required']): ?>
			<tr class="row<?php echo($rows++ % 2); ?>">
				<td class="key" colspan="2">
					<strong>Framework on Framework (FOF) <?php echo $fofInstallationStatus['version'] ?></strong>
					[<?php echo $fofInstallationStatus['date'] ?>]
				</td>
				<td><strong>
							<span
								style="color: <?php echo $fofInstallationStatus['required'] ? ($fofInstallationStatus['installed'] ? 'green' : 'red') : '#660' ?>; font-weight: bold;">
		<?php echo $fofInstallationStatus['required'] ? ($fofInstallationStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date'; ?>
							</span>
					</strong></td>
			</tr>
			<?php endif; ?>
			<?php if ($strapperInstallationStatus['required']): ?>
			<tr class="row<?php echo($rows++ % 2); ?>">
				<td class="key" colspan="2">
					<strong>Akeeba Strapper <?php echo $strapperInstallationStatus['version'] ?></strong>
					[<?php echo $strapperInstallationStatus['date'] ?>]
				</td>
				<td><strong>
							<span
								style="color: <?php echo $strapperInstallationStatus['required'] ? ($strapperInstallationStatus['installed'] ? 'green' : 'red') : '#660' ?>; font-weight: bold;">
				<?php echo $strapperInstallationStatus['required'] ? ($strapperInstallationStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date'; ?>
							</span>
					</strong></td>
			</tr>
			<?php endif; ?>
			<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
					<tr class="row<?php echo($rows++ % 2); ?>">
						<td class="key"><?php echo $module['name']; ?></td>
						<td class="key"><?php echo ucfirst($module['client']); ?></td>
						<td><strong
								style="color: <?php echo ($module['result']) ? "green" : "red" ?>"><?php echo ($module['result']) ? 'Installed' : 'Not installed'; ?></strong>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
					<tr class="row<?php echo($rows++ % 2); ?>">
						<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
						<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
						<td><strong
								style="color: <?php echo ($plugin['result']) ? "green" : "red" ?>"><?php echo ($plugin['result']) ? 'Installed' : 'Not installed'; ?></strong>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders the message after uninstalling the component
	 */
	protected function renderPostUninstallation($status, $parent)
	{
		$rows = 1;
		?>
		<table class="adminlist table table-striped" width="100%">
			<thead>
			<tr>
				<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
				<th width="30%"><?php echo JText::_('Status'); ?></th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="3"></td>
			</tr>
			</tfoot>
			<tbody>
			<tr class="row<?php echo($rows++ % 2); ?>">
				<td class="key" colspan="2"><?php echo $this->componentTitle; ?></td>
				<td><strong style="color: green">Removed</strong></td>
			</tr>
			<?php if (count($status->modules)) : ?>
				<tr>
					<th>Module</th>
					<th>Client</th>
					<th></th>
				</tr>
				<?php foreach ($status->modules as $module) : ?>
					<tr class="row<?php echo($rows++ % 2); ?>">
						<td class="key"><?php echo $module['name']; ?></td>
						<td class="key"><?php echo ucfirst($module['client']); ?></td>
						<td><strong
								style="color: <?php echo ($module['result']) ? "green" : "red" ?>"><?php echo ($module['result']) ? 'Removed' : 'Not removed'; ?></strong>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if (count($status->plugins)) : ?>
				<tr>
					<th>Plugin</th>
					<th>Group</th>
					<th></th>
				</tr>
				<?php foreach ($status->plugins as $plugin) : ?>
					<tr class="row<?php echo($rows++ % 2); ?>">
						<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
						<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
						<td><strong
								style="color: <?php echo ($plugin['result']) ? "green" : "red" ?>"><?php echo ($plugin['result']) ? 'Removed' : 'Not removed'; ?></strong>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	<?php
	}

	/**
	 * Bugfix for "DB function returned no error"
	 */
	protected function bugfixDBFunctionReturnedNoError()
	{
		$db = JFactory::getDbo();

		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('menutype') . ' = ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__menu')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}
	}

	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 */
	protected function bugfixCantBuildAdminMenus()
	{
		$db = JFactory::getDbo();

		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			return;
		}


		if (count($ids) > 1)
		{
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$ids = $db->loadObjectList();

		if (count($ids) > 1)
		{
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id

			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('menutype') . ' = ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName));
		$db->setQuery($query);

		try
		{
			$ids1 = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			$ids1 = array();
		}

		if (empty($ids1))
		{
			$ids1 = array();
		}

		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('menutype') . ' = ' . $db->q('main'))
			->where($db->qn('link') . ' LIKE ' . $db->q('index.php?option=' . $this->componentName . '&%'));
		$db->setQuery($query);

		try
		{
			$ids2 = $db->loadColumn();
		}
		catch (Exception $exc)
		{
			$ids2 = array();
		}

		if (empty($ids2))
		{
			$ids2 = array();
		}

		$ids = array_merge($ids1, $ids2);

		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$query = $db->getQuery(true);
				$query->delete('#__menu')
					->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $exc)
				{
					// Nothing
				}
			}
		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 *
	 * @return JObject The subextension installation status
	 */
	protected function installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$db = JFactory::getDbo();

		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();

		// Modules installation
		if (isset($this->installation_queue['modules']) && count($this->installation_queue['modules']))
		{
			foreach ($this->installation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Install the module
						if (empty($folder))
						{
							$folder = 'site';
						}

						$path = "$src/" . $this->modulesSourcePath . "/$folder/$module";

						if (!is_dir($path))
						{
							$path = "$src/" . $this->modulesSourcePath . "/$folder/mod_$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/" . $this->modulesSourcePath . "/$module";
						}

						if (!is_dir($path))
						{
							$path = "$src/" . $this->modulesSourcePath . "/mod_$module";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the module already installed?
						$sql = $db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
						$db->setQuery($sql);

						try
						{
							$count = $db->loadResult();
						}
						catch (Exception $exc)
						{
							$count = 0;
						}

						$installer = new JInstaller;
						$result = $installer->install($path);
						$status->modules[] = array(
							'name'   => 'mod_' . $module,
							'client' => $folder,
							'result' => $result
						);

						// Modify where it's published and its published state
						if (!$count)
						{
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;

							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position') . ' = ' . $db->q($modulePosition))
								->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));

							if ($modulePublished)
							{
								$sql->set($db->qn('published') . ' = ' . $db->q('1'));
							}

							$db->setQuery($sql);

							try
							{
								$db->execute();
							}
							catch (Exception $exc)
							{
								// Nothing
							}

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder == 'admin')
							{
								try
								{
									$query = $db->getQuery(true);
									$query->select('MAX(' . $db->qn('ordering') . ')')
										->from($db->qn('#__modules'))
										->where($db->qn('position') . '=' . $db->q($modulePosition));
									$db->setQuery($query);
									$position = $db->loadResult();
									$position++;

									$query = $db->getQuery(true);
									$query->update($db->qn('#__modules'))
										->set($db->qn('ordering') . ' = ' . $db->q($position))
										->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
									$db->setQuery($query);
									$db->execute();
								}
								catch (Exception $exc)
								{
									// Nothing
								}
							}

							// C. Link to all pages
							try
							{
								$query = $db->getQuery(true);
								$query->select('id')->from($db->qn('#__modules'))
									->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
								$db->setQuery($query);
								$moduleid = $db->loadResult();

								$query = $db->getQuery(true);
								$query->select('*')->from($db->qn('#__modules_menu'))
									->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
								$db->setQuery($query);
								$assignments = $db->loadObjectList();
								$isAssigned = !empty($assignments);

								if (!$isAssigned)
								{
									$o = (object)array(
										'moduleid' => $moduleid,
										'menuid'   => 0
									);
									$db->insertObject('#__modules_menu', $o);
								}
							}
							catch (Exception $exc)
							{
								// Nothing
							}
						}
					}
				}
			}
		}

		// Plugins installation
		if (isset($this->installation_queue['plugins']) && count($this->installation_queue['plugins']))
		{
			foreach ($this->installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$path = "$src/" . $this->pluginsSourcePath . "/$folder/$plugin";

						if (!is_dir($path))
						{
							$path = "$src/" . $this->pluginsSourcePath . "/$folder/plg_$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/" . $this->pluginsSourcePath . "/$plugin";
						}

						if (!is_dir($path))
						{
							$path = "$src/" . $this->pluginsSourcePath . "/plg_$plugin";
						}

						if (!is_dir($path))
						{
							continue;
						}

						// Was the plugin already installed?
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($query);

						try
						{
							$count = $db->loadResult();
						}
						catch (Exception $exc)
						{
							$count = 0;
						}

						$installer = new JInstaller;
						$result = $installer->install($path);

						$status->plugins[] = array('name' => 'plg_' . $plugin, 'group' => $folder, 'result' => $result);

						if ($published && !$count)
						{
							$query = $db->getQuery(true)
								->update($db->qn('#__extensions'))
								->set($db->qn('enabled') . ' = ' . $db->q('1'))
								->where($db->qn('element') . ' = ' . $db->q($plugin))
								->where($db->qn('folder') . ' = ' . $db->q($folder));
							$db->setQuery($query);

							try
							{
								$db->execute();
							}
							catch (Exception $exc)
							{
								// Nothing
							}
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller   $parent  The parent object
	 *
	 * @return  stdClass  The subextension uninstallation status
	 */
	protected function uninstallSubextensions($parent)
	{
		$db = JFactory::getDBO();

		$status = new stdClass();
		$status->modules = array();
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if (isset($this->installation_queue['modules']) && count($this->installation_queue['modules']))
		{
			foreach ($this->installation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module => $modulePreferences)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);

						try
						{
							$id = $db->loadResult();
						}
						catch (Exception $exc)
						{
							$id = 0;
						}

						// Uninstall the module
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (isset($this->installation_queue['plugins']) && count($this->installation_queue['plugins']))
		{
			foreach ($this->installation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin => $published)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						try
						{
							$id = $db->loadResult();
						}
						catch (Exception $exc)
						{
							$id = 0;
						}

						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('plugin', $id, 1);
							$status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeList  The files and directories to remove
	 */
	protected function removeFilesAndFolders($removeList)
	{
		// Remove files
		if (isset($removeList['files']) && !empty($removeList['files']))
		{
			foreach ($removeList['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if (!JFile::exists($f))
				{
					continue;
				}

				JFile::delete($f);
			}
		}

		// Remove folders
		if (isset($removeList['folders']) && !empty($removeList['folders']))
		{
			foreach ($removeList['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if (!JFolder::exists($f))
				{
					continue;
				}

				JFolder::delete($f);
			}
		}
	}

	/**
	 * Installs FOF if necessary
	 *
	 * @param   JInstaller  $parent  The parent object
	 *
	 * @return  array  The installation status
	 */
	protected function installFOF($parent)
	{
		// Get the source path
		$src = $parent->getParent()->getPath('source');
		$source = $src . '/' . $this->fofSourcePath;

		if (!JFolder::exists($source))
		{
			return array(
				'required'  => false,
				'installed' => false,
				'version'   => '0.0.0',
				'date'      => '2011-01-01',
			);
		}

		// Get the target path
		if (!defined('JPATH_LIBRARIES'))
		{
			$target = JPATH_ROOT . '/libraries/f0f';
		}
		else
		{
			$target = JPATH_LIBRARIES . '/f0f';
		}

		// Do I have to install FOF?
		$haveToInstallFOF = false;

		if (!JFolder::exists($target))
		{
			// FOF is not installed; install now
			$haveToInstallFOF = true;
		}
		else
		{
			// FOF is already installed; check the version
			$fofVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new JDate(trim($info[1]))
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new JDate('2011-01-01')
				);
			}

			$rawData = @file_get_contents($source . '/version.txt');
			$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
			$info = explode("\n", $rawData);

			$fofVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;

		if ($haveToInstallFOF)
		{
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedFOF = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($fofVersion))
		{
			$fofVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = @file_get_contents($source . '/version.txt');
				$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new JDate(trim($info[1]))
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new JDate('2011-01-01')
				);
			}

			$rawData = @file_get_contents($source . '/version.txt');
			$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
			$info = explode("\n", $rawData);

			$fofVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new JDate(trim($info[1]))
			);

			$versionSource = 'installed';
		}

		if (!($fofVersion[$versionSource]['date'] instanceof JDate))
		{
			$fofVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'  => $haveToInstallFOF,
			'installed' => $installedFOF,
			'version'   => $fofVersion[$versionSource]['version'],
			'date'      => $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * Installs Akeeba Strapper if necessary
	 *
	 * @param   JInstaller  $parent  The parent object
	 *
	 * @return  array  The installation status
	 */
	protected function installStrapper($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$source = $src . '/' . $this->strapperSourcePath;

		$target = JPATH_ROOT . '/media/akeeba_strapper';

		if (!JFolder::exists($source))
		{
			return array(
				'required'  => false,
				'installed' => false,
				'version'   => '0.0.0',
				'date'      => '2011-01-01',
			);
		}

		$haveToInstallStrapper = false;

		if (!JFolder::exists($target))
		{
			$haveToInstallStrapper = true;
		}
		else
		{
			$strapperVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
				$info = explode("\n", $rawData);
				$strapperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new JDate(trim($info[1]))
				);
			}
			else
			{
				$strapperVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
			$info = explode("\n", $rawData);
			$strapperVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new JDate(trim($info[1]))
			);

			$haveToInstallStrapper = $strapperVersion['package']['date']->toUNIX() > $strapperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;

		if ($haveToInstallStrapper)
		{
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedStraper = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($strapperVersion))
		{
			$strapperVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
				$info = explode("\n", $rawData);
				$strapperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date'    => new JDate(trim($info[1]))
				);
			}
			else
			{
				$strapperVersion['installed'] = array(
					'version' => '0.0',
					'date'    => new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$rawData = ($rawData === false) ? "0.0.0\n2011-01-01\n" : $rawData;
			$info = explode("\n", $rawData);

			$strapperVersion['package'] = array(
				'version' => trim($info[0]),
				'date'    => new JDate(trim($info[1]))
			);

			$versionSource = 'installed';
		}

		if (!($strapperVersion[$versionSource]['date'] instanceof JDate))
		{
			$strapperVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required'  => $haveToInstallStrapper,
			'installed' => $installedStraper,
			'version'   => $strapperVersion[$versionSource]['version'],
			'date'      => $strapperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * Uninstalls obsolete subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  The parent object
	 *
	 * @return  stdClass The subextension uninstallation status
	 */
	protected function uninstallObsoleteSubextensions($parent)
	{
		JLoader::import('joomla.installer.installer');

		$db = JFactory::getDBO();

		$status = new stdClass();
		$status->modules = array();
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if (isset($this->uninstallation_queue['modules']) && count($this->uninstallation_queue['modules']))
		{
			foreach ($this->uninstallation_queue['modules'] as $folder => $modules)
			{
				if (count($modules))
				{
					foreach ($modules as $module)
					{
						// Find the module ID
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('element') . ' = ' . $db->q('mod_' . $module))
							->where($db->qn('type') . ' = ' . $db->q('module'));
						$db->setQuery($sql);
						$id = $db->loadResult();
						// Uninstall the module
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('module', $id, 1);
							$status->modules[] = array(
								'name'   => 'mod_' . $module,
								'client' => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		// Plugins uninstallation
		if (isset($this->uninstallation_queue['plugins']) && count($this->uninstallation_queue['plugins']))
		{
			foreach ($this->uninstallation_queue['plugins'] as $folder => $plugins)
			{
				if (count($plugins))
				{
					foreach ($plugins as $plugin)
					{
						$sql = $db->getQuery(true)
							->select($db->qn('extension_id'))
							->from($db->qn('#__extensions'))
							->where($db->qn('type') . ' = ' . $db->q('plugin'))
							->where($db->qn('element') . ' = ' . $db->q($plugin))
							->where($db->qn('folder') . ' = ' . $db->q($folder));
						$db->setQuery($sql);

						$id = $db->loadResult();
						if ($id)
						{
							$installer = new JInstaller;
							$result = $installer->uninstall('plugin', $id, 1);
							$status->plugins[] = array(
								'name'   => 'plg_' . $plugin,
								'group'  => $folder,
								'result' => $result
							);
						}
					}
				}
			}
		}

		return $status;
	}
} 