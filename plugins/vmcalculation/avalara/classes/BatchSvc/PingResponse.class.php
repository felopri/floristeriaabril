<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * PingResponse.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class PingResponse {
  private $PingResult; // PingResult

  public function setPingResult($value){$this->PingResult=$value;} // PingResult
  public function getPingResult(){return $this->PingResult;} // PingResult

}

?>
