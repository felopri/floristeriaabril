<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchSave.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchSave {
  private $Batch; // Batch

  public function setBatch($value){$this->Batch=$value;} // Batch
  public function getBatch(){return $this->Batch;} // Batch

}

?>
