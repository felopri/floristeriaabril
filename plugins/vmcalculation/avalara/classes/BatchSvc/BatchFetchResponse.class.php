<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchFetchResponse.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFetchResponse {
  private $BatchFetchResult; // BatchFetchResult

  public function setBatchFetchResult($value){$this->BatchFetchResult=$value;} // BatchFetchResult
  public function getBatchFetchResult(){return $this->BatchFetchResult;} // BatchFetchResult

}

?>
