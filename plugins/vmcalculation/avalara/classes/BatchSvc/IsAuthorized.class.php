<?php
if (!defined('_JEXEC'))
die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 * IsAuthorized.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class IsAuthorized {
  private $Operations; // string

  public function setOperations($value){$this->Operations=$value;} // string
  public function getOperations(){return $this->Operations;} // string

}

?>
