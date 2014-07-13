<?php
/**
 * Class vRequest
 * Gets filtered request values.
 *
 * @package    VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2014 iStraxx UG (haftungsbeschränkt). All rights reserved.
 * @license MIT, see http://opensource.org/licenses/MIT
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 *  http://virtuemart.net
 */

class vRequest {

	public static function getUword($field, $default='', $custom=''){
		$source = self::getVar($field,$default);
		return self::filterUword($source,$custom);
	}

	//static $filters = array( '' =>);
	public static function uword($field, $default='', $custom=''){
		$source = self::getVar($field,$default);
		return self::filterUword($source,$custom);
	}

	public static function filterUword($source, $custom,$replace=''){
		if(function_exists('mb_ereg_replace')){
			//$source is string that will be filtered, $custom is string that contains custom characters
			return mb_ereg_replace('[^\w'.preg_quote($custom).']', $replace, $source);
		} else {
			return preg_replace("~[^\w".preg_quote($custom,'~')."]~", $replace, $source);	//We use Tilde as separator, and give the preq_quote function the used separator
		}
	}


	public static function getBool($name, $default = 0){
		$tmp = self::get($name, $default, FILTER_SANITIZE_NUMBER_INT);
		if($tmp){
			$tmp = true;
		} else {
			$tmp = false;
		}
		return $tmp;
	}

	public static function getInt($name, $default = 0){
		return self::get($name, $default, FILTER_SANITIZE_NUMBER_INT);
	}

	public static function getFloat($name,$default=0.0){
		return self::get($name,$default,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_SCIENTIFIC|FILTER_FLAG_ALLOW_FRACTION);
	}

	/**
	 * - Strips all characters that has a numerical value <32.
	 * - Strips all html.
	 *
	 * @param $name
	 * @param null $default
	 * @return mixed|null
	 */
	public static function getVar($name, $default = null){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW );
	}

	/**
	 * - Strips all characters that has a numerical value <32.
	 * - encodes html
	 *
	 * @param $name
	 * @param string $default
	 * @return mixed|null
	 */
	public static function getString($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_STRIP_LOW);
	}

	public static function getHtml($name, $default = ''){
		$tmp = self::get($name, $default);
		return JComponentHelper::filterText($tmp);
	}
	
	/**
	 * Gets a filtered request value
	 * - Strips all characters that has a numerical value <32 and >127.
	 * - strips html
	 * @author Max Milbers
	 * @param $name
	 * @param string $default
	 * @return mixed|null
	 */

	public static function getCmd($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
	}

	public static function getWord($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
	}

	/**
	 * Main filter function, called by the others with set Parameters
	 * The standard filter is non restrictiv.
	 *
	 * @author Max Milbers
	 * @param $name
	 * @param null $default
	 * @param int $filter
	 * @param int $flags
	 * @return mixed|null
	 */
	public static function get($name, $default = null, $filter = FILTER_UNSAFE_RAW, $flags = FILTER_FLAG_STRIP_LOW){
		//vmSetStartTime();
		if(!empty($name)){

			if(!isset($_REQUEST[$name])) return $default;

			//if(strpos($name,'[]'!==FALSE)){
			if(is_array($_REQUEST[$name])){
				return filter_var_array($_REQUEST[$name], $filter );
			}
			else {
				return filter_var($_REQUEST[$name], $filter, $flags);
			}

		} else {
			vmTrace('empty name in vRequest::get');
			return $default;
		}

	}

	/**
	 * Gets the request and filters it directly. It uses the standard php function filter_var_array,
	 * The standard filter allows all chars, also the special ones. But removes dangerous html tags.
	 *
	 * @author Max Milbers
	 * @param array $filter
	 * @return mixed cleaned $_REQUEST
	 */
	public static function getRequest( ){
		return  filter_var_array($_REQUEST, FILTER_SANITIZE_STRING);
	}
	
	public static function getPost( ){
		return  filter_var_array($_POST, FILTER_SANITIZE_STRING);
	}
	
	public static function getGet( ){
		return  filter_var_array($_GET, FILTER_SANITIZE_STRING);
	}
	
	public static function getFiles($name){
		return  filter_var_array($_FILES[$name], FILTER_SANITIZE_STRING);
	}

	public static function setVar($name, $value = null){
		if(isset($_REQUEST[$name])){
			$tmp = $_REQUEST[$name];
			$_REQUEST[$name] = $value;
			return $tmp;
		} else {
			$_REQUEST[$name] = $value;
			return null;
		}
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * @return  boolean  True if token valid
	 *
	 */
	public static function vmCheckToken($redirectMsg=0){

		$token = self::getFormToken();

		if (!self::uword($token, false)){

			if ($rToken = self::uword('token', false)){
				if($rToken == $token){
					return true;
				}
			}

			$session = JFactory::getSession();

			if ($session->isNew()){
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$app->redirect(JRoute::_('index.php'), vmText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'));
				$app->close();
				return false;
			}
			else {
				if($redirectMsg===0){
					$redirectMsg = 'Invalid Token, in ' . vRequest::getCmd('options') .' view='.vRequest::getCmd('view'). ' task='.vRequest::getCmd('task');
					//jexit('Invalid Token, in ' . vRequest::getCmd('options') .' view='.vRequest::getCmd('view'). ' task='.vRequest::getCmd('task'));
				} else {
					$redirectMsg =  vmText::_($redirectMsg);
				}
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$session->close();
				$app->redirect(JRoute::_('index.php'), $redirectMsg);
				$app->close();
				return false;
			}
		}
		else {
			return true;
		}
	}

	public static function getFormToken($fNew = false){

		$user = JFactory::getUser();
		$session = JFactory::getSession();
		if(empty($user->id)) $user->id = 0;
		$hash = JApplication::getHash($user->id . $session->getToken($fNew));

		return $hash;
	}

}