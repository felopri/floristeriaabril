<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchDelete.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchDelete {
  private $DeleteRequest; // DeleteRequest

  public function setDeleteRequest($value){$this->DeleteRequest=$value;} // DeleteRequest
  public function getDeleteRequest(){return $this->DeleteRequest;} // DeleteRequest

}

?>
