<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * TextCase.class.php
 */

/**
 * The casing to apply to the valid address(es) returned in the validation result.
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */

if(!class_exists('Enum')) require (VMAVALARA_CLASS_PATH.DS.'Enum.class.php');

class TextCase extends Enum
{
	public static $Default 	= 'Default';
	public static $Upper 	= 'Upper';
	public static $Mixed 	= 'Mixed';
    
	public static function Values()
	{
		return array(
			TextCase::$Default,
			TextCase::$Upper,
			TextCase::$Mixed,
		);
	}

    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>