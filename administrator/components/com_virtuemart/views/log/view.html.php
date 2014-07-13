<?php
/**
 *
 * List/edit/remove Log Files
 *
 * @package    VirtueMart
 * @subpackage Log
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 6307 2012-08-07 07:39:45Z alatak $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if (!class_exists('VmView')) {
	require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmview.php');
}

/**
 * HTML View class for log files
 *
 * @package    VirtueMart
 * @subpackage Log
 * @author Valérie isaksen
 */
class VirtuemartViewLog extends VmView {

	function display ($tpl = null) {

		// Load the helper(s)


		jimport('joomla.filesystem.file');
		$config = JFactory::getConfig();
		$log_path = $config->get('log_path', JPATH_ROOT . "/log");
		$layoutName = JRequest::getWord('layout', 'default');
		VmConfig::loadJLang('com_virtuemart_log');

		if ($layoutName == 'edit') {
			$logFile = JRequest::getString('logfile', '');
			$this->SetViewTitle('LOG', $logFile);
			$fileContent = file_get_contents($log_path . DS . $logFile);
			$fileContentByLine = explode("\n", $fileContent);

			$this->assignRef('fileContentByLine', $fileContentByLine);
			JToolBarHelper::cancel();

		} else {
			$logFiles = JFolder::files($log_path, $filter = '.', true, false, array('index.html'));

			$this->SetViewTitle('LOG');
			$this->assignRef('logFiles', $logFiles);
			$this->assignRef('path', $log_path);
		}

		parent::display($tpl);
	}
}

//No Closing Tag
