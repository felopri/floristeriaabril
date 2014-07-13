<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchFileFetch.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFileFetch {
  private $FetchRequest; // FetchRequest

  public function setFetchRequest($value){$this->FetchRequest=$value;} // FetchRequest
  public function getFetchRequest(){return $this->FetchRequest;} // FetchRequest

}

?>
