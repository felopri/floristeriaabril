<?php

/**
 *
 * Split menu class
 *
 * based on T3 Framework menu class
 *
 * @version             1.0.0
 * @package             Gavern Framework
 * @copyright			Copyright (C) 2010 - 2011 GavickPro. All rights reserved.
 *               
 */
 
// No direct access.
defined('_JEXEC') or die;

if (!defined ('_GK_SPLIT_MENU_CLASS')) {
	define ('_GK_SPLIT_MENU_CLASS', 1);
    require_once (dirname(__file__) . DS . "GKBase.class.php");

	class GKSplit extends GKMenuBase{

		function __construct (&$params) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root().'templates/' . $document->template . '/css/menu.gksplit.css');
			parent::__construct($params);
			$this->showSeparatedSub = true;
		}
		
		function genMenu($startlevel=0, $endlevel = 10, $check = false){
            if($check == true) {
                $this->setParam('startlevel', $startlevel);
				$this->setParam('endlevel', $endlevel);
				$pid = $this->getParentId($startlevel - 1);
				if (@$this->children[$pid]) {
                    foreach ($this->children[$pid] as $row) {
                        if (@$this->children[$row->id]) {
							if($row->id == JRequest::getCmd('Itemid', '')) {
							     return true;
							} else if($this->genMenuItems1($row->id, $startlevel, true)) {
							     return true;
							}
						}
					}
					return false;
				}
            } else {
                if ($startlevel == 0) parent::genMenu(0,0);
    			else {
    				$this->setParam('startlevel', $startlevel);
    				$this->setParam('endlevel', $endlevel);
    				$this->beginMenu($startlevel, $endlevel);
                    // Sublevel
    				$pid = $this->open[0];
    				//
                    if (@$this->children[$pid]) {
       	                $this->genMenuItems1($pid, $startlevel);
    				}
    				$this->endMenu($startlevel, $endlevel);
                }
			}
		}
		
        function genMenuItems1($pid, $level, $check = false) {
            if($check == true) {
			     if (@$this->children[$pid]) { 
                    foreach ($this->children[$pid] as $row) {
                        if($row->id == JRequest::getCmd('Itemid', '')) {
                            return true;
                        } else if ($level < $this->getParam('endlevel', 10)) {
                            if($this->genMenuItems1( $row->id, $level+1, true )) {
                                return true;
                            }
						}
					}
                
                    return false;
    			}
			} else {		
                if (@$this->children[$pid]) {
					$this->beginMenuItems($pid, $level);
					$i = 0;
					
					foreach ($this->children[$pid] as $row) {
						$pos = ($i == 0 ) ? 'first' : (($i == count($this->children[$pid])-1) ? 'last' :'');
		
						$this->beginMenuItem($row, $level, $pos);
						$this->genMenuItem( $row, $level, $pos);
			
						// show menu with menu expanded - submenus visible
                        $this->genMenuItems1( $row->id, $level+1 );
						$i++;
		
						$this->endMenuItem($row, $level, $pos);
					}
					$this->endMenuItems($pid, $level);
                }
			}
		}
		
	    function beginMenuItems($pid=0, $level=0){
	        if(!$level) echo "<ul>";
			else echo "<ul id=\"gkDropSub$pid\">";
	    }
	
	    function beginMenuItem($mitem=null, $level = 0, $pos = '') {			
			$active = $this->genClass($mitem, $level, $pos);
			$class_item = " class=\"";
			if ($active) $class_item .= $active;
			if(@$this->children[$mitem->id]) $class_item .= ' haschild';
			$class_item .= '"';
			if(!$level) echo "<li id=\"gkDropMain{$mitem->id}\"$class_item>";
			else echo "<li id=\"gkDropSubIt{$mitem->id}\"$class_item>";
	    }
	    
	    function checkActive($mitem=null, $level = 0, $pos = '') {
            $active = $this->genClass($mitem, $level, $pos);
	    	return (stripos($active, 'active')) ? true : false;
	    }
	
	    function beginMenu($startlevel=0, $endlevel = 10){
	        if(!$startlevel) echo "<div id=\"gkDropMain\">";
	        else echo "<div id=\"gkDropSub\">";			
	    }
	
		function endMenu($startlevel=0, $endlevel = 10){
			echo "</div>";
		}
	}
}