<?php
/**
 *
 * Data module for shop configuration
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @author RickG
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: config.php 6367 2012-08-22 12:23:37Z alatak $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
if(!class_exists('JModel')) require JPATH_VM_LIBRARIES.DS.'joomla'.DS.'application'.DS.'component'.DS.'model.php';

/**
 * Model class for shop configuration
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Max Milbers
 * @author RickG
 */
class VirtueMartModelConfig extends JModel {


	/**
	 * Retrieve a list of layouts from the default and chosen templates directory.
	 *
	 * @author Max Milbers
	 * @param name of the view
	 * @return object List of flypage objects
	 */
	static function getLayoutList($view) {

		$dirs[] = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.$view.DS.'tmpl';

		//This does not work, joomla takes only overrides of their standard template
		//		$tplpath = VmConfig::get('vmtemplate',0);
		//So we look for template overrides in the joomla standard template

		//This method does not work, we get the Template of the backend
		//$app = JFactory::getApplication('site');
		//$tplpath = $app->getTemplate();vmdebug('template',$tplpath);
		if (JVM_VERSION === 2) {
			$q = 'SELECT `template` FROM `#__template_styles` WHERE `client_id` ="0" AND `home`="1" ';
		} else {
			$q = 'SELECT `template` FROM `#__templates_menu` WHERE `client_id` ="0" ';
		}

		$db = JFactory::getDBO();
		$db->setQuery($q);

		$tplnames = $db->loadResult();
		if($tplnames){
			if(is_dir(JPATH_ROOT.DS.'templates'.DS.$tplnames.DS.'html'.DS.'com_virtuemart'.DS.$view)){
				$dirs[] = JPATH_ROOT.DS.'templates'.DS.$tplnames.DS.'html'.DS.'com_virtuemart'.DS.$view;
			}
		}

		$result = array();
		$emptyOption = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_ADMIN_CFG_NO_OVERRIDE'));
		$result[] = $emptyOption;

		$alreadyAddedFile = array();
		foreach($dirs as $dir){
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if(!empty($file) and strpos($file,'.')!==0 and strpos($file,'_')==0 and $file != 'index.html' and !is_Dir($file)){
						//Handling directly for extension is much cleaner
						$path_info = pathinfo($file);
						if(empty($path_info['extension'])){
							vmError('Attention file '.$file.' has no extension in view '.$view.' and directory '.$dir);
							$path_info['extension'] = '';
						}
						if ($path_info['extension'] == 'php' && !in_array($file,$alreadyAddedFile)) {
							$alreadyAddedFile[] = $file;
							//There is nothing to translate here
// 							$result[] = JHTML::_('select.option', $file, $path_info['filename']);
							$result[] = JHTML::_('select.option', $path_info['filename'], $path_info['filename']);
						}
					}
				}
			}
		}
		return $result;
	}


	/**
	 * Retrieve a list of available fonts to be used with PDF Invoice generation & PDF Product view on FE
	 *
	 * @author Nikos Zagas
	 * @return object List of available fonts
	 */
	function getTCPDFFontsList() {

		$dir = JPATH_ROOT.DS.'libraries'.DS.'tcpdf'.DS.'fonts';
		$specfiles = glob($dir.DS."*_specs.xml");
		$result = array();
		foreach ($specfiles as $file) {
			$fontxml = @simpleXML_load_file($file);
			if ($fontxml) {
				if (file_exists($dir . DS . $fontxml->filename . '.php')) {
					$result[] = JHTML::_('select.option', $fontxml->filename, JText::_($fontxml->fontname.' ('.$fontxml->fonttype.')'));
				} else {
					vmError ('A font master file is missing: ' . $dir . DS . 	$fontxml->filename . '.php');
				}
			} else {
				vmError ('Wrong structure in font XML file: '. $dir . DS . $file);
			}
		}
		return $result;
	}


	/**
	 * Retrieve a list of possible images to be used for the 'no image' image.
	 *
	 * @author RickG
	 * @author Max Milbers
	 * @return object List of image objects
	 */
	function getNoImageList() {

		//TODO set config value here
		$dirs[] = JPATH_ROOT.DS.'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'vmgeneral';

		$tplpath = VmConfig::get('vmtemplate',0);
		if(is_numeric($tplpath)){
			$db = JFactory::getDbo();
			$query = 'SELECT `template`,`params` FROM `#__template_styles` WHERE `id`="'.$tplpath.'" ';
			$db->setQuery($query);
			$res = $db->loadAssoc();
			if($res){
				$registry = new JRegistry;
				$registry->loadString($res['params']);
				$tplpath = $res['template'];
			}
		}
		if($tplpath){
			if(is_dir(JPATH_ROOT.DS.'templates'.DS.$tplpath.DS.'images'.DS.'vmgeneral')){
				$dirs[] = JPATH_ROOT.DS.'templates'.DS.$tplpath.DS.'images'.DS.'vmgeneral';
			}
		}

		$result = '';

		foreach($dirs as $dir){
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != '.svn' && $file != 'index.html') {
						if (filetype($dir.DS.$file) != 'dir') {
							$result[] = JHTML::_('select.option', $file, JText::_(str_replace('.php', '', $file)));
						}
					}
				}
			}
		}
		return $result;
	}


	/**
	 * Retrieve a list of currency converter modules from the plugins directory.
	 *
	 * @author RickG
	 * @return object List of theme objects
	 */
	function getCurrencyConverterList() {
		$dir = JPATH_VM_ADMINISTRATOR.DS.'plugins'.DS.'currency_converter';
		$result = '';

		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != '.svn') {
					$info = pathinfo($file);
					if ((filetype($dir.DS.$file) == 'file') && ($info['extension'] == 'php')) {
						$result[] = JHTML::_('select.option', $file, JText::_($file));
					}
				}
			}
		}

		return $result;
	}


	/**
	 * Retrieve a list of modules.
	 *
	 * @author RickG
	 * @return object List of module objects
	 */
	function getModuleList() {
		$db = JFactory::getDBO();

		$query = 'SELECT `module_id`, `module_name` FROM `#__virtuemart_modules` ';
		$query .= 'ORDER BY `module_id`';
		$db->setQuery($query);

		return $db->loadObjectList();
	}


	/**
	 * Retrieve a list of Joomla content items.
	 *
	 * @author RickG
	 * @return object List of content objects
	 */
	function getContentLinks() {
		$db = JFactory::getDBO();

		$query = 'SELECT `id`, CONCAT(`title`, " (", `title_alias`, ")") AS text FROM `#__content` ';
		$query .= 'ORDER BY `id`';
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/*
	 * Get the joomla list of languages
	 */
	function getActiveLanguages($active_languages) {

		$activeLangs = array() ;
		$language =JFactory::getLanguage();
		$jLangs = $language->getKnownLanguages(JPATH_BASE);

		foreach ($jLangs as $jLang) {
			$jlangTag = strtolower(strtr($jLang['tag'],'-','_'));
			$activeLangs[] = JHTML::_('select.option', $jLang['tag'] , $jLang['name']) ;
		}

		return JHTML::_('select.genericlist', $activeLangs, 'active_languages[]', 'size=10 multiple="multiple" data-placeholder="'.JText::_('COM_VIRTUEMART_DRDOWN_NOTMULTILINGUAL').'"', 'value', 'text', $active_languages );// $activeLangs;
	}


	/**
	 * Retrieve a list of preselected and existing search or order By Fields
	 * $type = 'browse_search_fields' or 'browse_orderby_fields'
	 * @author Kohl Patrick
	 * @return array of order list
	 */
	function getProductFilterFields( $type ) {

		$searchChecked = VmConfig::get($type) ;

		if (!is_array($searchChecked)) {
			$searchChecked = (array)$searchChecked;
		}
		if($type!='browse_cat_orderby_field'){
			$searchFieldsArray = ShopFunctions::getValidProductFilterArray ();
		} else {
			$searchFieldsArray = array('category_name','category_description','cx.ordering','c.published');
		}

		$searchFields= new stdClass();
		$searchFields->checkbox ='<div class="threecols"><ul>';
		foreach ($searchFieldsArray as $key => $field ) {
			if (in_array($field, $searchChecked) ) {
				$checked = 'checked="checked"';
			}
			else {
				$checked = '';
			}

			$fieldWithoutPrefix = $field;
			$dotps = strrpos($fieldWithoutPrefix, '.');
			if($dotps!==false){
				$prefix = substr($field, 0,$dotps+1);
				$fieldWithoutPrefix = substr($field, $dotps+1);
			}

			$text = JText::_('COM_VIRTUEMART_'.strtoupper($fieldWithoutPrefix)) ;

			if ($type == 'browse_orderby_fields' or $type == 'browse_cat_orderby_field'){
				$searchFields->select[] =  JHTML::_('select.option', $field, $text) ;
			}
			$searchFields->checkbox .= '<li><input type="checkbox" id="' .$type.$fieldWithoutPrefix.$key. '" name="'.$type.'[]" value="' .$field. '" ' .$checked. ' /><label for="' .$type.$fieldWithoutPrefix.$key. '">' .$text. '</label></li>';
		}
		$searchFields->checkbox .='</ul></div>';
		return $searchFields;
	}

	/**
	 * Save the configuration record
	 *
	 * @author Max Milbers
	 * @return boolean True is successful, false otherwise
	 */
	function store(&$data,$replace = FALSE) {

		JRequest::checkToken() or jexit( 'Invalid Token, in store config');

		//$data['active_languages'] = strtolower(strtr($data['active_languages'],'-','_'));
		//ATM we want to ensure that only one config is used

		$config = VmConfig::loadConfig(TRUE);

		$browse_cat_orderby_field = $config->get('browse_cat_orderby_field');
		$cat_brws_orderby_dir = $config->get('cat_brws_orderby_dir');

		$config->setParams($data,$replace);
		$confData = array();
		$query = 'SELECT * FROM `#__virtuemart_configs`';
		$this->_db->setQuery($query);
		if($this->_db->loadResult()){
			$confData['virtuemart_config_id'] = 1;
		} else {
			$confData['virtuemart_config_id'] = 0;
		}

		$urls = array('assets_general_path','media_category_path','media_product_path','media_manufacturer_path','media_vendor_path');
		foreach($urls as $urlkey){
			$url = trim($config->get($urlkey));
			$length = strlen($url);
			if(strrpos($url,'/')!=($length-1)){
				$config->set($urlkey,$url.'/');
				vmInfo('Corrected media url '.$urlkey.' added missing /');
			}
		}

		//If empty it is not sent by the form, other forms do it by using a table to store,
		//the config is like a big xparams and so we check some values for this form manually
		/*$toSetEmpty = array('active_languages','inv_os','email_os_v','email_os_s');
		foreach($toSetEmpty as $item){
			if(!isset($data[$item])) {
				$config->set($item,array());
			}
		}*/

		$checkCSVInput = array('pagseq','pagseq_1','pagseq_2','pagseq_3','pagseq_4','pagseq_5');
		foreach($checkCSVInput as $csValueKey){
			$csValue = $config->get($csValueKey);
			if(!empty($csValue)){
				$sequenceArray = explode(',', $csValue);
				foreach($sequenceArray as &$csV){
					$csV = (int)trim($csV);
				}
				$csValue = implode(',',$sequenceArray);
				$config->set($csValueKey,$csValue);
			}
		}

		$safePath = trim($config->get('forSale_path'));
		if(!empty($safePath)){
			if(DS!='/' and strpos($safePath,'/')!==false){
				$safePath=str_replace('/',DS,$safePath);
				vmdebug('$safePath',$safePath);
			}
			$length = strlen($safePath);
			if(strrpos($safePath,DS)!=($length-1)){
				$safePath = $safePath.DS;
				vmInfo('Corrected safe path added missing '.DS);
			}
			$config->set('forSale_path',$safePath);
		} else {
			$safePath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'vmfiles';
			$exists = JFolder::exists($safePath);
			if(!$exists){
				$created = JFolder::create($safePath);
				$safePath = $safePath.DS;
				if($created){
					vmInfo('COM_VIRTUEMART_SAFE_PATH_DEFAULT_CREATED',$safePath);
					/* create htaccess file */
					$fileData = "order deny, allow\ndeny from all\nallow from none";
					JLoader::import('joomla.filesystem.file');
					$fileName = $safePath.DS.'.htaccess';
					$result = JFile::write($fileName, $fileData);
					if (!$result) {
						VmWarn('COM_VIRTUEMART_HTACCESS_DEFAULT_NOT_CREATED',$safePath,$fileData);
					}
					$config->set('forSale_path',$safePath);
				} else {
					VmWarn('COM_VIRTUEMART_WARN_SAFE_PATH_NO_INVOICE',JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'));
				}
			}
		}

		if(!class_exists('shopfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
		$safePath = shopFunctions::checkSafePath($safePath);

		if(!empty($safePath)){

			$exists = JFolder::exists($safePath.'invoices');
			if(!$exists){
				$created = JFolder::create($safePath.'invoices');
				if($created){
					vmInfo('COM_VIRTUEMART_SAFE_PATH_INVOICE_CREATED');
				} else {
					VmWarn('COM_VIRTUEMART_WARN_SAFE_PATH_NO_INVOICE',JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'));
				}
			}
		}

		$confData['config'] = $config->toString();

		$confTable = $this->getTable('configs');
		if (!$confTable->bindChecknStore($confData)) {
			vmError($confTable->getError());
		}

		// Load the newly saved values into the session.
		$config = VmConfig::loadConfig(true);

		if(!class_exists('GenericTableUpdater')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
		$updater = new GenericTableUpdater();
		$result = $updater->createLanguageTables();

		$newbrowse_cat_orderby_field = $config->get('browse_cat_orderby_field');
		$newcat_brws_orderby_dir = $config->get('cat_brws_orderby_dir');
		if($browse_cat_orderby_field!=$newbrowse_cat_orderby_field or $newcat_brws_orderby_dir!=$cat_brws_orderby_dir){
			$cache = JFactory::getCache('com_virtuemart_cats','callback');
			$cache->clean();
		}

		return true;
	}

	public static function checkConfigTableExists(){

		$db = JFactory::getDBO();
		$query = 'SHOW TABLES LIKE "'.$db->getPrefix().'virtuemart_configs"';
		$db->setQuery($query);
		$configTable = $db->loadResult();
		$err = $db->getErrorMsg();
		if(!empty($err) or !$configTable){
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Dangerous tools get disabled after execution an operation which needed that rights.
	 * This is the function actually doing it.
	 *
	 * @author Max Milbers
	 */
	function setDangerousToolsOff(){

// 		VmConfig::loadConfig(true);
		$dangerousTools = VmConfig::readConfigFile(true);

		if( $dangerousTools){
			$uri = JFactory::getURI();
			$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
			$lang = JText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_STILL_ENABLED',JText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'),$link);
			VmInfo($lang);
		} else {
			$data['dangeroustools'] = 0;
			$data['virtuemart_config_id'] = 1;
			$this->store($data);
		}

	}

	public function remove() {

		$table = $this->getTable('configs');
		$id = 1;
		if (!$table->delete($id)) {
			vmError(get_class( $this ).'::remove '.$id.' '.$table->getError(),'Cannot delete config');
			return false;
		}

		return true;
	}

	/**
	 * This function deletes a config stored in the database
	 *
	 * @author Max Milbers
	 */
	function deleteConfig(){

		if($this->remove()){
			return VmConfig::loadConfig(true,true);
		} else {
			return false;
		}

	}

}

//pure php no closing tag