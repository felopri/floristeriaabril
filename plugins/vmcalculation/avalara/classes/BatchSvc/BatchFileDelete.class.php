<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchFileDelete.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFileDelete {
  private $DeleteRequest; // DeleteRequest

  public function setDeleteRequest($value){$this->DeleteRequest=$value;} // DeleteRequest
  public function getDeleteRequest(){return $this->DeleteRequest;} // DeleteRequest

}

?>
