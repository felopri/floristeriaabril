<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 * BatchFileSave.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFileSave {
  private $BatchFile; // BatchFile

  public function setBatchFile($value){$this->BatchFile=$value;} // BatchFile
  public function getBatchFile(){return $this->BatchFile;} // BatchFile

}

?>
