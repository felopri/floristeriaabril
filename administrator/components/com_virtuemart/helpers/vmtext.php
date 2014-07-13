<?php
/**
 * Class for getting with language keys translated text. The original code was written by joomla Platform 11.1
 *
 * @package    VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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

/**
 * Text handling class.
 *
 * @package     Joomla.Platform
 * @subpackage  Language
 * @since       11.1
 */
class vmText
{
	/**
	 * javascript strings
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $strings = array();

	/**
	 * Translates a string into the current language. This just jText of joomla 2.5.x
	 *
	 * Examples:
	 * <script>alert(Joomla.vmText._('<?php echo vmText::_("JDEFAULT", array("script"=>true));?>'));</script>
	 * will generate an alert message containing 'Default'
	 * <?php echo vmText::_("JDEFAULT");?> it will generate a 'Default' string
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key is $script is true
	 *
	 * @since   11.1
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		$lang = JFactory::getLanguage();
		if (is_array($jsSafe))
		{
			if (array_key_exists('interpretBackSlashes', $jsSafe))
			{
				$interpretBackSlashes = (boolean) $jsSafe['interpretBackSlashes'];
			}
			if (array_key_exists('script', $jsSafe))
			{
				$script = (boolean) $jsSafe['script'];
			}
			if (array_key_exists('jsSafe', $jsSafe))
			{
				$jsSafe = (boolean) $jsSafe['jsSafe'];
			}
			else
			{
				$jsSafe = false;
			}
		}
		if ($script)
		{
			self::$strings[$string] = $lang->_($string, $jsSafe, $interpretBackSlashes);
			return $string;
		}
		else
		{
			return $lang->_($string, $jsSafe, $interpretBackSlashes);
		}
	}

	/**
	 * Passes a string thru a sprintf.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 *
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
	 *
	 * where:
	 *
	 * jsSafe is a boolean to generate a javascript safe strings.
	 * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 * script is a boolean to indicate that the string will be push in the javascript language store.
	 *
	 * @param   string  $string  The format string.
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options.
	 *
	 * @since   11.1
	 */
	public static function sprintf($string)
	{
		$lang = JFactory::getLanguage();
		$args = func_get_args();
		$count = count($args);
		if ($count > 0)
		{
			if (is_array($args[$count - 1]))
			{
				$args[0] = $lang->_(
					$string, array_key_exists('jsSafe', $args[$count - 1]) ? $args[$count - 1]['jsSafe'] : false,
					array_key_exists('interpretBackSlashes', $args[$count - 1]) ? $args[$count - 1]['interpretBackSlashes'] : true
				);

				if (array_key_exists('script', $args[$count - 1]) && $args[$count - 1]['script'])
				{
					self::$strings[$string] = call_user_func_array('sprintf', $args);
					return $string;
				}
			}
			else
			{
				$args[0] = $lang->_($string);
			}
			$args[0] = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $args[0]);
			return call_user_func_array('sprintf', $args);
		}
		return '';
	}

}