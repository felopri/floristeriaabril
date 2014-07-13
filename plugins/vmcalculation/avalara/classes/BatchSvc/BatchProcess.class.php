<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchProcess.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchProcess {
  private $BatchProcessRequest; // BatchProcessRequest

  public function setBatchProcessRequest($value){$this->BatchProcessRequest=$value;} // BatchProcessRequest
  public function getBatchProcessRequest(){return $this->BatchProcessRequest;} // BatchProcessRequest

}

?>
