<?php

/**
 *
 * Dropline menu class
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

if (!defined ('_GK_DROPLINE_MENU_CLASS')) {
	define ('_GK_DROPLINE_MENU_CLASS', 1);
	require_once (dirname(__file__) . DS . "GKBase.class.php");
		
	class GKDropline extends GKMenuBase{
		function __construct ($params) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root().'templates/' . $document->template . '/css/menu.gkdropline.css');
			$document->addScript(JURI::root().'templates/' . $document->template . '/js/menu.gkdropline.js');
			parent::__construct($params);
			$this->showSeparatedSub = true;
		}

		function genMenu($startlevel=0, $endlevel = 10, $check = false){
            if($check == true) {
                return true;
            } else {                
    			if ($startlevel == 0) parent::genMenu(0,0);
    			else {
    				$this->setParam('startlevel', $startlevel);
    				$this->setParam('endlevel', $endlevel);
    				$this->beginMenu($startlevel, $endlevel);
    				//Sub level
    				$pid = $this->getParentId($startlevel - 1);
    				if (@$this->children[$pid]) {					
    					foreach ($this->children[$pid] as $row) {
    						if (@$this->children[$row->id]) {
                                $this->genMenuItems1($row->id, $startlevel);
    						} else {
    							echo "<ul id=\"gkDropSub{$row->id}\"><li class=\"empty\">&nbsp;</li></ul>";
    						}
    					}
    				}
    				$this->endMenu($startlevel, $endlevel);
    			}
			}
		}
		
		function genMenuItems1($pid, $level, $check = false) {
            if($check == true) {
                return true;
			} else {		
                if (@$this->children[$pid]) {
                    $active_submenu = false;
		   		 
    				foreach ($this->children[$pid] as $row) {
    					if($this->checkActive($row, $level, '')) {
    						$active_submenu = true;
    					}
    				}
                    
                    $this->beginMenuItems($pid, $level, $active_submenu);
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
		
        function beginMenuItems($pid=0, $level=0, $active = false){
            if(!$level) echo "<ul>";
			else echo "<ul id=\"gkDropSub$pid\"" . (($active) ? ' class="active"' : '') . ">";
        }

        function endMenuItems($pid=0, $level=0){
            echo "</ul>";
        }
        
        function beginMenuItem($mitem=null, $level = 0, $pos = ''){
			$active = $this->genClass($mitem, $level, $pos);
			$class_item = " class=\"";
			if ($active) $class_item .= $active;
			if(@$this->children[$mitem->id]) $class_item .= ' haschild';
			$class_item .= '"';
	        if(!$level) echo "<li id=\"gkDropMain{$mitem->id}\"$class_item>";
			else echo "<li id=\"gkDropSubIt{$mitem->id}\"$class_item>";
	    }
        
        function endMenuItem($pid=0, $level=0, $pos = ''){
            echo "</li>";
        }

        function beginMenu($startlevel=0, $endlevel = 10){
            if(!$startlevel) echo "<div id=\"gkDropMain\">";
            else echo "<div id=\"gkDropSub\">";			
        }

		function endMenu($startlevel=0, $endlevel = 10){
			echo "</div>";
		}
		
		function checkActive($mitem=null, $level = 0, $pos = '') {
			$active = $this->genClass($mitem, $level, $pos);
			return (stripos($active, 'active')) ? true : false;
		}
	}	
}