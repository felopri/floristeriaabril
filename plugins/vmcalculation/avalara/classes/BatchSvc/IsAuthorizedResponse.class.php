<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * IsAuthorizedResponse.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class IsAuthorizedResponse {
  private $IsAuthorizedResult; // IsAuthorizedResult

  public function setIsAuthorizedResult($value){$this->IsAuthorizedResult=$value;} // IsAuthorizedResult
  public function getIsAuthorizedResult(){return $this->IsAuthorizedResult;} // IsAuthorizedResult

}

?>
