<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchProcessResponse.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchProcessResponse {
  private $BatchProcessResult; // BatchProcessResult

  public function setBatchProcessResult($value){$this->BatchProcessResult=$value;} // BatchProcessResult
  public function getBatchProcessResult(){return $this->BatchProcessResult;} // BatchProcessResult

}

?>
