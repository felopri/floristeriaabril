<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * SeverityLevel.class.php
 */

/**
 * Severity of the result {@link Message}.
 *
 * Defines the constants used to specify SeverityLevel in {@link Message}
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */

if(!class_exists('Enum')) require (VMAVALARA_CLASS_PATH.DS.'Enum.class.php');

class SeverityLevel extends Enum
{
    public static $Success = 'Success';
    public static $Warning = 'Warning';
    public static $Error = 'Error';
    public static $Exception = 'Exception';
 
	
	public static function Values()
	{
		return array(
			SeverityLevel::$Success,
			SeverityLevel::$Warning,
			SeverityLevel::$Error,
			SeverityLevel::$Tax
		);
	}
	
    // Unfortunate boiler plate due to polymorphism issues on static functions
    public static function Validate($value) { self::__Validate($value,self::Values(),__CLASS__); }
}

?>