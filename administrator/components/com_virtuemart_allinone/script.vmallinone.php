<?php
defined ('_JEXEC') or die('Restricted access');

/**
 * VirtueMart script file
 *
 * This file is executed during install/upgrade and uninstall
 *
 * @author Patrick Kohl, Max Milbers
 * @package VirtueMart
 */

defined ('DS') or define('DS', DIRECTORY_SEPARATOR);

$max_execution_time = ini_get('max_execution_time');
if((int)$max_execution_time<120) {
	@ini_set( 'max_execution_time', '120' );
}
$memory_limit = (int) substr(ini_get('memory_limit'),0,-1);
if($memory_limit<128)  @ini_set( 'memory_limit', '128M' );

// hack to prevent defining these twice in 1.6 installation
if (!defined ('_VM_SCRIPT_INCLUDED')) {

	define('_VM_SCRIPT_INCLUDED', TRUE);

	class com_virtuemart_allinoneInstallerScript {

		public function preflight () {
			//$this->vmInstall();
		}

		public function install () {
			//$this->vmInstall();
		}

		public function discover_install () {
			//$this->vmInstall();
		}

		public function postflight () {

			$this->vmInstall ();
		}

		public function vmInstall () {

			jimport ('joomla.filesystem.file');
			jimport ('joomla.installer.installer');

			$this->createIndexFolder (JPATH_ROOT . DS . 'plugins' . DS . 'vmcalculation');
			$this->createIndexFolder (JPATH_ROOT . DS . 'plugins' . DS . 'vmcustom');
			$this->createIndexFolder (JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment');
			$this->createIndexFolder (JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment');

			$this->path = JInstaller::getInstance ()->getPath ('extension_administrator');

			$this->updateShipperToShipment ();
			$this->installPlugin ('Standard', 'plugin',  'standard', 'vmpayment');
			$this->installPlugin ('Paypal',    'plugin', 'paypal',    'vmpayment');
			$this->installPlugin ('PayZen', 'plugin', 'payzen', 'vmpayment');
			$this->installPlugin ('SystemPay', 'plugin', 'systempay', 'vmpayment');
			//moneybookers
			$src = $this->path . DS . 'plugins' . DS . 'vmpayment' . DS . 'moneybookers';
			$dst = JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'moneybookers';
			$this->recurse_copy ($src, $dst, 'plugins');
			$this->installPlugin ('Moneybookers Credit Cards', 'plugin', 'moneybookers_acc', 'vmpayment');
			$this->installPlugin ('Moneybookers Lastschrift', 'plugin', 'moneybookers_did', 'vmpayment');
			$this->installPlugin ('Moneybookers iDeal', 'plugin', 'moneybookers_idl', 'vmpayment');
			$this->installPlugin ('Moneybookers Giropay', 'plugin', 'moneybookers_gir', 'vmpayment');
			$this->installPlugin ('Moneybookers Sofortueberweisung', 'plugin', 'moneybookers_sft', 'vmpayment');
			$this->installPlugin ('Moneybookers Przelewy24', 'plugin', 'moneybookers_pwy', 'vmpayment');
			$this->installPlugin ('Moneybookers Online Bank Transfer', 'plugin', 'moneybookers_obt', 'vmpayment');
			$this->installPlugin ('Moneybookers Skrill Digital Wallet', 'plugin', 'moneybookers_wlt', 'vmpayment');
			$this->installPlugin ('Authorize.net', 'plugin', 'authorizenet', 'vmpayment');
			$this->installPlugin ('Klarna', 'plugin', 'klarna', 'vmpayment');
			$this->installPlugin ('Heidelpay', 'plugin', 'heidelpay', 'vmpayment');

			$this->installPlugin ('By weight, ZIP and countries', 'plugin', 'weight_countries', 'vmshipment');

			$this->installPlugin ('Customer text input', 'plugin', 'textinput', 'vmcustom');
			$this->installPlugin ('Product specification', 'plugin', 'specification', 'vmcustom');
			$this->installPlugin ('Stockable variants', 'plugin', 'stockable', 'vmcustom');
			$this->installPlugin ('Avalara Tax', 'plugin', 'avalara', 'vmcalculation', 0);

			// 			$table = '#__virtuemart_customs';
			// 			$fieldname = 'field_type';
			// 			$fieldvalue = 'G';
			// 			$this->addToRequired($table,$fieldname,$fieldvalue,"INSERT INTO `#__virtuemart_customs`
			// 					(`custom_parent_id`, `admin_only`, `custom_title`, `custom_tip`, `custom_value`, `custom_field_desc`,
			// 					 `field_type`, `is_list`, `is_hidden`, `is_cart_attribute`, `published`) VALUES
			// 						(0, 0, 'COM_VIRTUEMART_STOCKABLE_PRODUCT', 'COM_VIRTUEMART_STOCKABLE_PRODUCT_TIP', NULL,
			// 					'COM_VIRTUEMART_STOCKABLE_PRODUCT_DESC', 'G', 0, 0, 0, 1 );");

			$this->installPlugin ('Virtuemart Product', 'plugin', 'virtuemart', 'search');

			$task = JRequest::getCmd ('task');
			if ($task != 'updateDatabase') {

				// modules auto move
				$src = $this->path . DS . "modules";
				$dst = JPATH_ROOT . DS . "modules";
				$this->recurse_copy ($src, $dst);

				echo "Checking VirtueMart2 modules...";
				if (!$this->VmModulesAlreadyInstalled ()) {
					echo "Installing VirtueMart2 modules<br/ >";
					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"text_before":"","product_currency":"","cache":"1","moduleclass_sfx":"","class_sfx":""}';
					} else {
						$defaultParams = "text_before=\nproduct_currency=\ncache=1\nmoduleclass_sfx=\nclass_sfx=\n";
					}
					$this->installModule ('VM - Currencies Selector', 'mod_virtuemart_currencies', 4, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"product_group":"featured","max_items":"1","products_per_row":"1","display_style":"list","show_price":"1","show_addtocart":"1","headerText":"Best products","footerText":"","filter_category":"0","virtuemart_category_id":"0","cache":"0","moduleclass_sfx":"","class_sfx":""}';

					} else {
						$defaultParams = "product_group=featured\nmax_items=1\nproducts_per_row=1\ndisplay_style=list\nshow_price=1\nshow_addtocart=1\nheaderText=Best products\nfooterText=\nfilter_category=0\ncategory_id=1\ncache=0\nmoduleclass_sfx=\nclass_sfx=\n";
					}
					$this->installModule ('VM - Featured products', 'mod_virtuemart_product', 3, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"product_group":"topten","max_items":"1","products_per_row":"1","display_style":"list","show_price":"1","show_addtocart":"1","headerText":"","footerText":"","filter_category":"0","virtuemart_category_id":"0","cache":"0","moduleclass_sfx":"","class_sfx":""}';
					} else {
						$defaultParams = "product_group=topten\nmax_items=1\nproducts_per_row=1\ndisplay_style=list\nshow_price=1\nshow_addtocart=1\nheaderText=\nfooterText=\nfilter_category=0\ncategory_id=1\ncache=0\nmoduleclass_sfx=\nclass_sfx=\n";
					}
					$this->installModule ('VM - Best Sales', 'mod_virtuemart_product', 1, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {

						$defaultParams = '{"width":"20","text":"","button":"","button_pos":"right","imagebutton":"","button_text":""}';
					} else {
						$defaultParams = "width=20\ntext=\nbutton=\nbutton_pos=right\nimagebutton=\nbutton_text=\nmoduleclass_sfx=\ncache=1\ncache_time=900\n";
					}
					$this->installModule ('VM - Search in Shop', 'mod_virtuemart_search', 2, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"show":"all","display_style":"list","manufacturers_per_row":"1","headerText":"","footerText":""}';
					} else {
						$defaultParams = "show=all\ndisplay_style=div\nmanufacturers_per_row=1\nheaderText=\nfooterText=\ncache=0\nmoduleclass_sfx=\nclass_sfx=";
					}
					$this->installModule ('VM - Manufacturer', 'mod_virtuemart_manufacturer', 5, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"moduleclass_sfx":"","show_price":"1","show_product_list":"1"}';
					} else {
						$defaultParams = "moduleclass_sfx=\nshow_price=1\nshow_product_list=1\n";
					}
					$this->installModule ('VM - Shopping cart', 'mod_virtuemart_cart', 0, $defaultParams);

					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$defaultParams = '{"Parent_Category_id":"0","layout":"default","cache":"0","moduleclass_sfx":"","class_sfx":""}';
					} else {
						$defaultParams = "moduleclass_sfx=\nclass_sfx=\ncategory_name=default\ncache=no\n";
					}
					$this->installModule ('VM - Category', 'mod_virtuemart_category', 6, $defaultParams);
				} else {
					echo "VirtueMart2 modules already installed<br/ >";
				}
				// language auto move
				$src = $this->path . DS . "languageFE";
				$dst = JPATH_ROOT . DS . "language";
				$this->recurse_copy ($src, $dst);
				echo " VirtueMart2 language moved to the joomla language FE folder<br/ >";

				// language auto move
				$src = $this->path . DS . "languageBE";
				$dst = JPATH_ADMINISTRATOR . DS . "language";
				$this->recurse_copy ($src, $dst);
				echo " VirtueMart2 language moved to the joomla language BE folder<br/ >";

				// libraries auto move
				$src = $this->path . DS . "libraries";
				$dst = JPATH_ROOT . DS . "libraries";
				$this->recurse_copy ($src, $dst);
				echo " VirtueMart2 pdf moved to the joomla libraries folder<br/ >";

				//update plugins, make em loggable
				/*			$loggables = array(	'created_on' => 'DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"',
				'created_by' => 'INT(11) NOT NULL DEFAULT "0"',
				'modified_on'=> 'DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"',
				'modified_by'=> 'INT(11) NOT NULL DEFAULT "0"',
				'locked_on' =>'DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"',
				'locked_by' =>'INT(11) NOT NULL DEFAULT "0"'
				);
				foreach($loggables as $key => $value){
				$this->checkAddFieldToTable('#__virtuemart_payment_paypal',$key,$value);
				$this->checkAddFieldToTable('#__virtuemart_payment_standard',$key,$value);
				$this->checkAddFieldToTable('#__virtuemart_shipment_weight_countries',$key,$value);
				}*/

				echo "<H3>Installing Virtuemart Plugins and modules Success.</h3>";
				echo "<H3>You may directly uninstall this component. Your plugins will remain</h3>";

				echo "<H3>Ignore the message " . JText::_ ('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED') . "</h3>";
			} else {
				echo "<H3>Updated Virtuemart Plugin tables</h3>";
			}
			return TRUE;

		}

		/**
		 * Installs a vm plugin into the database
		 *
		 */
		private function installPlugin ($name, $type, $element, $group, $published=1) {

			$task = JRequest::getCmd ('task');

			if ($task != 'updateDatabase') {
				$data = array();

				if (version_compare (JVERSION, '1.7.0', 'ge')) {

					// Joomla! 1.7 code here
					$table = JTable::getInstance ('extension');
					$data['enabled'] = $published;
					$data['access'] = 1;
					$tableName = '#__extensions';
					$idfield = 'extension_id';
				} elseif (version_compare (JVERSION, '1.6.0', 'ge')) {

					// Joomla! 1.6 code here
					$table = JTable::getInstance ('extension');
					$data['enabled'] = $published;
					$data['access'] = 1;
					$tableName = '#__extensions';
					$idfield = 'extension_id';
				} else {

					// Joomla! 1.5 code here
					$table = JTable::getInstance ('plugin');
					$data['published'] = $published;
					$data['access'] = 0;
					$tableName = '#__plugins';
					$idfield = 'id';
				}

				$data['name'] = $name;
				$data['type'] = $type;
				$data['element'] = $element;
				$data['folder'] = $group;

				$data['client_id'] = 0;

				$src = $this->path . DS . 'plugins' . DS . $group . DS . $element;

				$db = JFactory::getDBO ();
				$q = 'SELECT COUNT(*) FROM `' . $tableName . '` WHERE `element` = "' . $element . '" and folder = "' . $group . '" ';
				$db->setQuery ($q);
				$count = $db->loadResult ();

				//We write only in the table, when it is not installed already
				if ($count == 0) {
					// 				$table->load($count);
					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$data['manifest_cache'] = json_encode (JApplicationHelper::parseXMLInstallFile ($src . DS . $element . '.xml'));
					}

					if (!$table->bind ($data)) {
						$app = JFactory::getApplication ();
						$app->enqueueMessage ('VMInstaller table->bind throws error for ' . $name . ' ' . $type . ' ' . $element . ' ' . $group);
					}

					if (!$table->check ($data)) {
						$app = JFactory::getApplication ();
						$app->enqueueMessage ('VMInstaller table->check throws error for ' . $name . ' ' . $type . ' ' . $element . ' ' . $group);

					}

					if (!$table->store ($data)) {
						$app = JFactory::getApplication ();
						$app->enqueueMessage ('VMInstaller table->store throws error for ' . $name . ' ' . $type . ' ' . $element . ' ' . $group);
					}

					$errors = $table->getErrors ();
					foreach ($errors as $error) {
						$app = JFactory::getApplication ();
						$app->enqueueMessage (get_class ($this) . '::store ' . $error);
					}
					// remove duplicated
				} elseif ($count == 2) {
					$q = 'SELECT ' . $idfield . ' FROM `' . $tableName . '` WHERE `element` = "' . $element . '" ORDER BY  `' . $idfield . '` DESC  LIMIT 0,1';
					$db->setQuery ($q);
					$duplicatedPlugin = $db->loadResult ();
					$q = 'DELETE FROM `' . $tableName . '` WHERE ' . $idfield . ' = ' . $duplicatedPlugin;
					$db->setQuery ($q);
					$db->query ();
				}
			}

			if (version_compare (JVERSION, '1.7.0', 'ge')) {
				// Joomla! 1.7 code here
				$dst = JPATH_ROOT . DS . 'plugins' . DS . $group . DS . $element;

			} elseif (version_compare (JVERSION, '1.6.0', 'ge')) {
				// Joomla! 1.6 code here
				$dst = JPATH_ROOT . DS . 'plugins' . DS . $group . DS . $element;
			} else {
				// Joomla! 1.5 code here
				$dst = JPATH_ROOT . DS . 'plugins' . DS . $group;
			}

			if ($task != 'updateDatabase') {
				$this->recurse_copy ($src, $dst);
			}

			if ($group != 'search') {
				$this->updatePluginTable ($name, $type, $element, $group, $dst);
			} else {
				if (version_compare (JVERSION, '1.6.0', 'ge')) {
					$this->updatePluginTable ($name, $type, $element, $group, $dst);
				}
			}

		}


		public function updatePluginTable ($name, $type, $element, $group, $dst) {

			$app = JFactory::getApplication ();

			//Update Tables
			if (!class_exists ('VmConfig')) {
				require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
			}

			if (class_exists ('VmConfig')) {
				$pluginfilename = $dst . DS . $element . '.php';
				require ($pluginfilename);

				//plgVmpaymentPaypal
				$pluginClassname = 'plg' . ucfirst ($group) . ucfirst ($element);

				//Let's get the global dispatcher
				$dispatcher = JDispatcher::getInstance ();
				$config = array('type'=> $group, 'name'=> $group, 'params'=> '');
				$plugin = new $pluginClassname($dispatcher, $config);
				;
				// 				$updateString = $plugin->getVmPluginCreateTableSQL();
				//if(function_exists($plugin->getTableSQLFields)){
				$_psType = substr ($group, 2);

				$tablename = '#__virtuemart_' . $_psType . '_plg_' . $element;
				$db = JFactory::getDBO ();
				$prefix=$db->getPrefix();
				$query = 'SHOW TABLES LIKE "' . str_replace ('#__', $prefix, $tablename) . '"';
				$db->setQuery ($query);
				$result = $db->loadResult ();
				//$app -> enqueueMessage( get_class( $this ).'::  '.$query.' '.$result);
				if ($result) {
					$SQLfields = $plugin->getTableSQLFields ();
					$loggablefields = $plugin->getTableSQLLoggablefields ();
					$tablesFields = array_merge ($SQLfields, $loggablefields);
					$update[$tablename] = array($tablesFields, array(), array());
vmdebug('install plugin',$update );
					$app->enqueueMessage (get_class ($this) . ':: VirtueMart2 update ' . $tablename);

					if (!class_exists ('GenericTableUpdater')) {
						require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
					}
					$updater = new GenericTableUpdater();

					$updater->updateMyVmTables ($update);
				}
				//}
				// 				} else {

				// 					$app = JFactory::getApplication();
				// 					$app -> enqueueMessage( get_class( $plugin ).':: VirtueMart2 function getTableSQLFields not found');

				// 				}

			} else {
				$app = JFactory::getApplication ();
				$app->enqueueMessage (get_class ($this) . ':: VirtueMart2 must be installed, or the tables cant be updated ' . $error);

			}

		}

		public function installModule ($title, $module, $ordering, $params) {

			$params = '';

			$table = JTable::getInstance ('module');

			$db = $table->getDBO ();
			$q = 'SELECT id FROM `#__modules` WHERE `module` = "' . $module . '" ';
			$db->setQuery ($q);
			$id = $db->loadResult ();
			$src = JPATH_ROOT . DS . 'modules' . DS . $module;

			if (!empty($id)) {
				return;
			}
			$table->load ();
			if (version_compare (JVERSION, '1.7.0', 'ge')) {
				// Joomla! 1.7 code here
				$position = 'position-4';
				$access = 1;
			} else {
				if (version_compare (JVERSION, '1.6.0', 'ge')) {
					// Joomla! 1.6 code here
					$access = 1;
				} else {
					// Joomla! 1.5 code here
					$position = 'left';
					$access = 0;
				}
			}

			if (empty($table->title)) {
				$table->title = $title;
			}
			if (empty($table->ordering)) {
				$table->ordering = $ordering;
			}
			if (empty($table->published)) {
				$table->published = 1;
			}
			if (empty($table->module)) {
				$table->module = $module;
			}
			if (empty($table->params)) {
				$table->params = $params;
			}
			if (empty($table->access)) {
				$table->access = $access;
			}
			if (empty($table->position)) {
				$table->position = $position;
			}
			if (empty($table->client_id)) {
				$table->client_id = $client_id = 0;
			}

			// 			$data['manifest_cache'] ='';
			// 			if(!empty($id)){
			// 				unset($data['manifest_cache']);
			// 				$table->load($id);
			// 				if(empty($table->manifest_cache)){
			// 					if(version_compare(JVERSION,'1.6.0','ge')) {
			// 						$data['manifest_cache'] = json_encode(JApplicationHelper::parseXMLInstallFile($src.DS.$module.'.xml'));
			// 					}
			// 				}
			// 			}

			// 			if(empty($count)){
			// 			if(!$table->bind($data)){
			// 				$app = JFactory::getApplication();
			// 				$app -> enqueueMessage('VMInstaller table->bind throws error for '.$title.' '.$module.' '.$params);
			// 			}

			if (!$table->check ()) {
				$app = JFactory::getApplication ();
				$app->enqueueMessage ('VMInstaller table->check throws error for ' . $title . ' ' . $module . ' ' . $params);
			}

			if (!$table->store ()) {
				$app = JFactory::getApplication ();
				$app->enqueueMessage ('VMInstaller table->store throws error for for ' . $title . ' ' . $module . ' ' . $params);
			}

			$errors = $table->getErrors ();
			foreach ($errors as $error) {
				$app = JFactory::getApplication ();
				$app->enqueueMessage (get_class ($this) . '::store ' . $error);
			}
			// 			}

			$lastUsedId = $table->id;

			$q = 'SELECT moduleid FROM `#__modules_menu` WHERE `moduleid` = "' . $lastUsedId . '" ';
			$db->setQuery ($q);
			$moduleid = $db->loadResult ();

			$action = '';
			if (empty($moduleid)) {
				$q = 'INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES( "' . $lastUsedId . '" , "0");';
			} else {
				//$q = 'UPDATE `#__modules_menu` SET `menuid`= "0" WHERE `moduleid`= "'.$moduleid.'" ';
			}
			$db->setQuery ($q);
			$db->query ();

			if (version_compare (JVERSION, '1.6.0', 'ge')) {

				$q = 'SELECT extension_id FROM `#__extensions` WHERE `element` = "' . $module . '" ';
				$db->setQuery ($q);
				$ext_id = $db->loadResult ();

				//				$manifestCache = str_replace('"', '\'', $data["manifest_cache"]);
				$action = '';
				if (empty($ext_id)) {
					if (version_compare (JVERSION, '1.6.0', 'ge')) {
						$manifest_cache = json_encode (JApplicationHelper::parseXMLInstallFile ($src . DS . $module . '.xml'));
					}
					$q = 'INSERT INTO `#__extensions` 	(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `ordering`) VALUES
																	( "' . $module . '" , "module", "' . $module . '", "", "0", "1","' . $access . '", "0", "' . $db->getEscaped ($manifest_cache) . '", "' . $params . '","' . $ordering . '");';
				} else {

					/*					$q = 'UPDATE `#__extensions` SET 	`name`= "'.$module.'",
																						`type`= "module",
																						`element`= "'.$module.'",
																						`folder`= "",
																						`client_id`= "'.$client_id.'",
																						`enabled`= "1",
																						`access`= "'.$access.'",
																						`protected`= "0",
																						`ordering`= "'.$ordering.'"

										WHERE `extension_id`= "'.$ext_id.'" ';*/
				}
				$db->setQuery ($q);
				if (!$db->query ()) {
					$app = JFactory::getApplication ();
					$app->enqueueMessage (get_class ($this) . '::  ' . $db->getErrorMsg ());
				}

			}
		}

		public function VmModulesAlreadyInstalled () {

			// when the modules are already installed publish=-2
			$table = JTable::getInstance ('module');
			$db = $table->getDBO ();
			$q = 'SELECT count(*) FROM `#__modules` WHERE `module` LIKE "mod_virtuemart_%"';
			$db->setQuery ($q);
			$count = $db->loadResult ();
			return $count;
		}

		/**
		 * @author Max Milbers
		 * @param string $tablename
		 * @param string $fields
		 * @param string $command
		 */
		private function alterTable ($tablename, $fields, $command = 'CHANGE') {

			if (empty($this->db)) {
				$this->db = JFactory::getDBO ();
			}

			$query = 'SHOW COLUMNS FROM `' . $tablename . '` ';
			$this->db->setQuery ($query);
			$columns = $this->db->loadResultArray (0);

			foreach ($fields as $fieldname => $alterCommand) {
				if (in_array ($fieldname, $columns)) {
					$query = 'ALTER TABLE `' . $tablename . '` ' . $command . ' COLUMN `' . $fieldname . '` ' . $alterCommand;

					$this->db->setQuery ($query);
					$this->db->query ();
				}
			}

		}

		/**
		 *
		 * @author Max Milbers
		 * @param string $table
		 * @param string $field
		 * @param string $fieldType
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function checkAddFieldToTable ($table, $field, $fieldType) {

			$query = 'SHOW COLUMNS FROM `' . $table . '` ';
			$this->db->setQuery ($query);
			$columns = $this->db->loadResultArray (0);

			if (!in_array ($field, $columns)) {

				$query = 'ALTER TABLE `' . $table . '` ADD ' . $field . ' ' . $fieldType;
				$this->db->setQuery ($query);
				if (!$this->db->query ()) {
					$app = JFactory::getApplication ();
					$app->enqueueMessage ('Install checkAddFieldToTable ' . $this->db->getErrorMsg ());
					return FALSE;
				} else {
					return TRUE;
				}
			}
			return FALSE;
		}

		private function updateShipperToShipment () {

			if (empty($this->db)) {
				$this->db = JFactory::getDBO ();
			}
			if (version_compare (JVERSION, '1.6.0', 'ge')) {
				// Joomla! 1.6 code here
				$table = JTable::getInstance ('extension');
				$tableName = '#__extensions';
				$idfield = 'extension_id';
			} else {

				// Joomla! 1.5 code here
				$table = JTable::getInstance ('plugin');
				$tableName = '#__plugins';
				$idfield = 'id';
			}

			$q = 'SELECT ' . $idfield . ' FROM ' . $tableName . ' WHERE `folder` = "vmshipper" ';
			$this->db->setQuery ($q);
			$result = $this->db->loadResult ();
			if ($result) {
				$q = 'UPDATE `' . $tableName . '` SET `folder`="vmshipment" WHERE `extension_id`= ' . $result;
				$this->db->setQuery ($q);
				$this->db->query ();
			}
		}

		/**
		 * copy all $src to $dst folder and remove it
		 *
		 * @author Max Milbers
		 * @param String $src path
		 * @param String $dst path
		 * @param String $type modules, plugins, languageBE, languageFE
		 */
		private function recurse_copy ($src, $dst) {

			$dir = opendir ($src);
			$this->createIndexFolder ($dst);

			if (is_resource ($dir)) {
				while (FALSE !== ($file = readdir ($dir))) {
					if (($file != '.') && ($file != '..')) {
						if (is_dir ($src . DS . $file)) {
							$this->recurse_copy ($src . DS . $file, $dst . DS . $file);
						} else {
							if (JFile::exists ($dst . DS . $file)) {
								if (!JFile::delete ($dst . DS . $file)) {
									$app = JFactory::getApplication ();
									$app->enqueueMessage ('Couldnt delete ' . $dst . DS . $file);
								}
							}
							if (!JFile::move ($src . DS . $file, $dst . DS . $file)) {
								$app = JFactory::getApplication ();
								$app->enqueueMessage ('Couldnt move ' . $src . DS . $file . ' to ' . $dst . DS . $file);
							}
						}
					}
				}
				closedir ($dir);
				if (is_dir ($src)) {
					JFolder::delete ($src);
				}
			} else {
				$app = JFactory::getApplication ();
				$app->enqueueMessage ('Couldnt read dir ' . $dir . ' source ' . $src);
			}

		}


		public function uninstall () {

			return TRUE;
		}

		/**
		 * creates a folder with empty html file
		 *
		 * @author Max Milbers
		 *
		 */
		public function createIndexFolder ($path) {

			if (JFolder::create ($path)) {
				if (!JFile::exists ($path . DS . 'index.html')) {
					JFile::copy (JPATH_ROOT . DS . 'components' . DS . 'index.html', $path . DS . 'index.html');
				}
				return TRUE;
			}
			return FALSE;
		}

	}


	// PLZ look in #vminstall.php# to add your plugin and module
	function com_install () {

		if (!version_compare (JVERSION, '1.6.0', 'ge')) {
			$vmInstall = new com_virtuemart_allinoneInstallerScript();
			$vmInstall->vmInstall ();
		}
		return TRUE;
	}

	function com_uninstall () {

		return TRUE;
	}

} //if defined
// pure php no tag
