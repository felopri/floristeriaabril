<?php
defined('JPATH_BASE') or die();
/**
 * virtuemart encrypt class, with some additional behaviours.
 *
 *
 * @package    VirtueMart
 * @subpackage Helpers
 * @author Max Milbers, Valérie Isaksen
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class vmCrypt {

	const ENCRYPT_SAFEPATH="keys";

	static function encrypt ($string) {

		$key = self::_getKey ();

		if(function_exists('mcrypt_encrypt')){
			// create a random IV to use with CBC encoding
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

			return base64_encode ($iv.mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC,$iv));
		} else {
			return base64_encode ($string);
		}

	}

	static function decrypt ($string,$date=0) {

		$key = self::_getKey ($date);
		if(!empty($key)){
			$ciphertext_dec = base64_decode($string);
			if(function_exists('mcrypt_encrypt')){
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
				// retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
				$iv_dec = substr($ciphertext_dec, 0, $iv_size);
				//retrieves the cipher text (everything except the $iv_size in the front)
				$ciphertext_dec = substr($ciphertext_dec, $iv_size);
				return rtrim (mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec), "\0");
			} else {
				return $ciphertext_dec;
			}

		} else {
			return $string;
		}

	}

	private static function _getKey ($date = 0) {

		$key = self::_checkCreateKeyFile($date);

		return base64_decode ($key);

	}

	private static function _checkCreateKeyFile($date){
		jimport('joomla.filesystem.file');

		vmSetStartTime('check');
		static $existingKeys = false;

		$keyPath = self::_getEncryptSafepath ();

		if(!$existingKeys){
			$dir = opendir($keyPath);
			if(is_resource($dir)){
				$existingKeys = array();
				while(false !== ( $file = readdir($dir)) ) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if ( !is_dir($keyPath .DS. $file)) {
							$ext = Jfile::getExt($file);
							if($ext=='ini' and file_exists($keyPath .DS. $file)){
								$content = parse_ini_file($keyPath .DS. $file);
								if($content and is_array($content) and isset($content['unixtime'])){
									$key = $content['unixtime'];
									unset($content['unixtime']);
									$existingKeys[$key] = $content;
									//vmdebug('Reading '.$keyPath .DS. $file,$content);
								}

							} else {
								vmdebug('Resource says there is file, but does not exists? '.$keyPath .DS. $file);
							}
						} else {
							//vmdebug('Directory in they keyfolder?  '.$keyPath .DS. $file);
						}
					} else {
						//vmdebug('Directory in the keyfolder '.$keyPath .DS. $file);
					}
				}
			} else {
				static $warn = false;
				if(!$warn)vmWarn('Key folder in safepath unaccessible '.$keyPath);
				$warn = true;
			}
		}

		if($existingKeys and is_array($existingKeys) and count($existingKeys)>0){
			ksort($existingKeys);

			if(!empty($date)){
				$key = '';
				foreach($existingKeys as $unixDate=>$values){
					if(($unixDate-30) >= $date ){
						vmdebug('$unixDate '.$unixDate.' >= $date '.$date);
						continue;
					}
					vmdebug('$unixDate < $date');
					//$usedKey = $values;
					$key = $values['key'];
				}

				vmdebug('Use key file ',$key);
				//include($keyPath .DS. $usedKey.'.php');
			} else {
				$usedKey = end($existingKeys);
				$key = $usedKey['key'];
			}
			vmTime('my time','check');
			return $key;
		} else {

			$usedKey = date("ymd");
			$filename = $keyPath . DS . $usedKey . '.ini';
			if (!JFile::exists ($filename)) {

				$token = JUtility::getHash(JUserHelper::genRandomPassword());
				$salt = JUserHelper::getSalt('crypt-md5');
				$hashedToken = md5($token . $salt)  ;
				$key = base64_encode($hashedToken);
				//$options = array('costs'=>VmConfig::get('cryptCost',8));

				/*if(!function_exists('password_hash')){
					require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'password_compat.php');
				}

				if(function_exists('password_hash')){
					$key = password_hash($key, PASSWORD_BCRYPT, $options);
				}*/

				$date = JFactory::getDate();
				$today = $date->toUnix();
				//$key = pack('H*',$key);
				$content = ';<?php die(); */
						[keys]
						key = "'.$key.'"
						unixtime = "'.$today.'"
						date = "'.date("Y-m-d H:i:s").'"
						; */ ?>';
				$result = JFile::write($filename, $content);
				vmTime('my time','check');
				return $key;
			}
		}
		vmTime('my time','check');
		//return pack('H*',$key);
	}

	private static function _getEncryptSafepath () {

		if (!class_exists('ShopFunctions'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
		$safePath = ShopFunctions::checkSafePath();
		if (empty($safePath)) {
			return NULL;
		}
		$encryptSafePath = $safePath . self::ENCRYPT_SAFEPATH;
		//echo 'my $encryptSafePath '.$encryptSafePath;
		//if(!JFolder::exists($encryptSafePath)){
			self::createEncryptFolder($encryptSafePath);
		//}
		return $encryptSafePath;
	}

	private static function createEncryptFolder ($folderName) {

		//$folderName = self::_getEncryptSafepath ();

		$exists = JFolder::exists ($folderName);
		if ($exists) {
			return TRUE;
		}
		$created = JFolder::create ($folderName);
		if ($created) {
			return TRUE;
		}
		$uri = JFactory::getURI ();
		$link = $uri->root () . 'administrator/index.php?option=com_virtuemart&view=config';
		VmError (JText::sprintf ('COM_VIRTUEMART_CANNOT_STORE_CONFIG', $folderName, '<a href="' . $link . '">' . $link . '</a>', JText::_ ('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH')));
		return FALSE;
	}



}