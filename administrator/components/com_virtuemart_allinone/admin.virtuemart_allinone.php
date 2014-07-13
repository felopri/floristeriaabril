<?php
/**
 * @package LiveUpdate
 * @copyright Copyright ©2011 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 *
 * One-click updater for Joomla! extensions
 * Copyright (C) 2011  Nicholas K. Dionysopoulos / AkeebaBackup.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

require_once dirname(__FILE__).'/classes/abstractconfig.php';
require_once dirname(__FILE__).'/config.php';

$task = JRequest::getCmd('task');
if($task=='updateDatabase'){
	$data = JRequest::get('get');
	JRequest::setVar($data['token'], '1', 'post');
	JRequest::checkToken() or jexit('Invalid Token, in ' . JRequest::getWord('task'));

	//Update Tables
	if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
	if(!class_exists('Permissions'))
	require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart' . DS . 'helpers' . DS . 'permissions.php');
	if(!Permissions::getInstance()->check('admin')){
		$msg = 'Forget IT';
		$this->setRedirect('index.php?option=com_virtuemart_allinone', $msg);
	} else {
		if(!class_exists('com_virtuemart_allinoneInstallerScript')) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart_allinone'.DS.'script.vmallinone.php');
		$updater = new com_virtuemart_allinoneInstallerScript();
		$updater->vmInstall();
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_virtuemart_allinone', 'Database updated');
	}

}

?>
<script type="text/javascript">
<!--
function confirmation(message, destnUrl) {
	var answer = confirm(message);
	if (answer) {
		window.location = destnUrl;
	}
}
//-->
</script>

<table>
<tr>
	<td>
<?php LiveUpdate::handleRequest(); ?>
	</td>
</tr>
<tr>
<td align="center">
<?php
$jlang = JFactory::getLanguage();
		$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, 'en-GB', true); // Load English (British)
		$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, $jlang->getDefault(), true); // Load the site's default language
		$jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, null, true); // Load the currently selected language

		?>
<?php $link=JROUTE::_('index.php?option=com_virtuemart_allinone&task=updateDatabase&token='.JUtility::getToken() ); ?>
	    <button onclick="javascript:confirmation('<?php echo addslashes( JText::_('COM_VIRTUEMART_UPDATE_VMPLUGINTABLES') ); ?>', '<?php echo $link; ?>');">

            <?php echo JText::_('COM_VIRTUEMART_UPDATE_VMPLUGINTABLES'); ?>
		</button>
	</td>
    </tr>
</table>

<?php


class LiveUpdate
{
	/**
	 * Loads the translation strings -- this is an internal function, called automatically
	 */
	private static function loadLanguage()
	{
		// Load translations
		$basePath = dirname(__FILE__);
		$jlang = JFactory::getLanguage();
		$jlang->load('liveupdate', $basePath, 'en-GB', true); // Load English (British)
		$jlang->load('liveupdate', $basePath, $jlang->getDefault(), true); // Load the site's default language
		$jlang->load('liveupdate', $basePath, null, true); // Load the currently selected language
	}

	/**
	 * Handles requests to the "liveupdate" view which is used to display
	 * update information and perform the live updates
	 */
	public static function handleRequest()
	{
		// Load language strings
		self::loadLanguage();

		// Load the controller and let it run the show
		require_once dirname(__FILE__).'/classes/controller.php';
		$controller = new LiveUpdateController();
		$controller->execute(JRequest::getCmd('task','overview'));
		$controller->redirect();
	}

	/**
	 * Returns update information about your extension, based on your configuration settings
	 * @return stdClass
	 */
	public static function getUpdateInformation($force = false)
	{
		require_once dirname(__FILE__).'/classes/updatefetch.php';
		$update = new LiveUpdateFetch();
		$info = $update->getUpdateInformation($force);
		$hasUpdates = $update->hasUpdates();
		$info->hasUpdates = $hasUpdates;

		$config = LiveUpdateConfig::getInstance();
		$extInfo = $config->getExtensionInformation();

		$info->extInfo = (object)$extInfo;

		return $info;
	}

	public static function getIcon($config=array())
	{
		// Load language strings
		self::loadLanguage();

		$defaultConfig = array(
			'option'			=> JRequest::getCmd('option',''),
			'view'				=> 'liveupdate',
			'mediaurl'			=> JURI::base().'components/'.JRequest::getCmd('option','').'/liveupdate/assets/'
		);
		$c = array_merge($defaultConfig, $config);

		$url = 'index.php?option='.$c['option'].'&view='.$c['view'];
		$img = $c['mediaurl'];

		$updateInfo = self::getUpdateInformation();
		if(!$updateInfo->supported) {
			// Unsupported
			$class = 'liveupdate-icon-notsupported';
			$img .= 'nosupport-32.png';
			$lbl = JText::_('LIVEUPDATE_ICON_UNSUPPORTED');
		} elseif($updateInfo->stuck) {
			// Stuck
			$class = 'liveupdate-icon-crashed';
			$img .= 'nosupport-32.png';
			$lbl = JText::_('LIVEUPDATE_ICON_CRASHED');
		} elseif($updateInfo->hasUpdates) {
			// Has updates
			$class = 'liveupdate-icon-updates';
			$img .= 'update-32.png';
			$lbl = JText::_('LIVEUPDATE_ICON_UPDATES');
		} else {
			// Already in the latest release
			$class = 'liveupdate-icon-noupdates';
			$img .= 'current-32.png';
			$lbl = JText::_('LIVEUPDATE_ICON_CURRENT');
		}

		return '<div class="icon"><a href="'.$url.'">'.
			'<div><img src="'.$img.'" width="32" height="32" border="0" align="middle" style="float: none" /></div>'.
			'<span class="'.$class.'">'.$lbl.'</span></a></div>';
	}
}