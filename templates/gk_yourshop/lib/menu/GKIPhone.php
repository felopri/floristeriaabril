<?php

/**
 *
 * iPhone menu class
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

jimport( 'joomla.html.parameter' );

if (!defined('_GK_IPHONE_MENU_CLASS')) {
    define('_GK_IPHONE_MENU_CLASS', 1);

    class GKIPhone extends JObject {
        var $_params = null;
        var $children = null;
        var $open = null;
        var $items = null;
        var $Itemid = 0;
        var $_tmpl = null;

        function __construct(&$params) {
            $acl = JFactory::getACL();
            $app = JFactory::getApplication();
          //  $menu = JSite::getMenu();
             $menu = $app->getMenu();
            $active = $menu->getActive();
            $active_id = isset($active) ? $active->id : $menu->getDefault()->id;      
            $this->_params = $params;
            $this->Itemid = $active_id;     
        }

        function getParam($paramName, $default = null) {
            $val = $this->_params->get($paramName, null);
            return (!$val) ? $default : $val;
        }

        function loadMenu($menuname = 'mainmenu') {
            $list = array();
            $db = JFactory::getDbo();
            $acl =JFactory::getACL();
            $user = JFactory::getUser();
            $app = JFactory::getApplication();
            $menu = $app->getMenu();
            $aid = $user->get('aid');
            //find active element or set default
            $active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
            $this->open = $active->tree;
            $rows = $menu->getItems('menutype', $menuname);
            if (!count($rows)) return;
            $children = array();
            $this->items = array();

            foreach ($rows as $index => $v) {
                if (isset($v->title)) $v->name = $v->title;
                if (isset($v->parent_id)) $v->parent = $v->parent_id;

                $v->name = str_replace('&', '&amp;', str_replace('&amp', '&', $v->name));
                if ($v->access >= $aid) {
                    $ptr = $v->parent;
                    $list = @$children[$ptr] ? $children[$ptr] : array();	
                    // friendly links
                    $v->flink = $v->link;

                    switch ($v->type) {
                        case 'separator':
                            continue;
                        case 'url':
                            if ((strpos($v->link, 'index.php?') === 0) && (strpos($v->link, 'Itemid=') === false)) {
                                $v->flink = $v->link . '&Itemid=' . $v->id;
                            }
                            break;
                        case 'alias':
                            $v->flink = 'index.php?Itemid=' . $v->params->get('aliasoptions');
                            break;
                        default:
                            $router = JSite::getRouter();
                            if ($router->getMode() == JROUTER_MODE_SEF) {
                                $v->flink = 'index.php?Itemid=' . $v->id;
                            } else {
                                $v->flink .= '&Itemid=' . $v->id;
                            }
                            break;
                    }
                    $v->url = $v->flink = JRoute::_($v->flink);

                    if ($v->home == 1) {
                        $v->url = JURI::base();
                    }

                    $v->_idx = count($list);
                    array_push($list, $v);
                    $children[$ptr] = $list;
                    $this->items[$v->id] = $v;
                }
            }

            $this->children = $children;
        }

        function remove_item($item) {
            $result = array();
            foreach ($this->children[$item->parent] as $o) {
                if ($o->id != $item->id) $result[] = $o;
            }
            $this->children[$item->parent] = $result;
        }

        function genMenuItem($item, $level = 0, $pos = '', $ret = 0) {
            $data = '';
            $tmp = $item;
            $tmpname = $tmp->name;
            $active = $this->genClass($tmp, $level, $pos);
            if ($active)
                $active = " class=\"$active\"";

            $id = 'id="menu' . $tmp->id . '"';
            $txt = $tmpname;

            $title = "title=\"$tmpname\"";

            if ($tmp->type == 'menulink') {
                $menu = &JSite::getMenu();
                $alias_item = clone ($menu->getItem($tmp->query['Itemid']));
                if (!$alias_item) return false;
                else $tmp->url = $alias_item->link;
            }

            if ($tmpname) {
                if ($tmp->type == 'separator') {
                    $data = '<a href="#" ' . $active . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
                } else {
                    if ($tmp->url != null) {
                        switch ($tmp->browserNav) {
                            default:
                            case 0:
                                // _top
                                $data = '<a href="' . $tmp->url . '" ' . $active . ' ' . $id . ' ' . $title .
                                    '>' . $txt . '</a>';
                                break;
                            case 1:
                                // _blank
                                $data = '<a href="' . $tmp->url . '" target="_blank" ' . $active . ' ' . $id .
                                    ' ' . $title . '>' . $txt . '</a>';
                                break;
                            case 2:
                                $data = '<a href="' . $tmp->url . '" target="_blank" ' . $active . ' ' . $id .
                                    ' ' . $title . '>' . $txt . '</a>';
                                break;
                        }
                    } else {
                        $data = '<a ' . $active . ' ' . $id . ' ' . $title . '>' . $txt . '</a>';
                    }
                }
            }


            if ($this->getParam('gkmenu')) {
                if (isset($item->content) && $item->content) {
                	$data .= $this->beginMenuItems($item->id, $level + 1, true);
                    $data .= $item->content;
                    $data .= $this->endMenuItems($item->id, $level + 1, true);
                }
            }
            
            if ($ret) return $data;
            else echo $data;
        }
        
        function setParam($paramName, $paramValue) {
            return $this->_params->set($paramName, $paramValue);
        }

        function beginMenuItems($pid = 0, $level = 0) {
            echo "<ul>";
        }
        
        function endMenuItems($pid = 0, $level = 0) {
            echo "</ul>";
        }

        function beginMenuItem($mitem = null, $level = 0, $pos = '') {
            $active = $this->genClass($mitem, $level, $pos);
            if ($active) $active = " class=\"$active\"";
            echo "<li $active>";
        }
        
        function endMenuItem($mitem = null, $level = 0, $pos = '') {
            echo "</li>";
        }
        
        function genClass($mitem, $level, $pos) {
            $active = in_array($mitem->id, $this->open);
            $cls = ($level ? "" : "menu-item{$mitem->_idx}") . ($active ? " active" : "") . ($pos ? " $pos-item" : "");
            if (@$this->children[$mitem->id] && (!$level || $level < $this->getParam('endlevel'))) $cls .= " haschild";
            return $cls;
        }
        
        function genMenu($startlevel = 0, $endlevel = -1) {
            $this->setParam('startlevel', $startlevel);
            $this->setParam('endlevel', $endlevel == -1 ? 10 : $endlevel);

            if ($this->getParam('startlevel') == 0) {
                //First level
                $this->genMenuItems(1, 0);
            } else {
                //Sub level
                $pid = $this->getParentId($this->getParam('startlevel'));
                if ($pid) $this->genMenuItems($pid, $this->getParam('startlevel'));
            }
        }
        
        function genMenuItems($pid, $level) {
            if (@$this->children[$pid]) {
                $j = 0;
                $cols = $pid && $this->getParam('gkmenu') && isset($this->items[$pid]) && isset($this->
                    items[$pid]->cols) && $this->items[$pid]->cols ? $this->items[$pid]->cols : 1;
                $total = count($this->children[$pid]);
                $tmp = $pid && isset($this->items[$pid]) ? $this->items[$pid] : new stdclass();
                if ($cols > 1) {
                    $fixitems = count($tmp->col);
                    if ($fixitems < $cols) {
                        $fixitem = array_sum($tmp->col);
                        $leftitem = $total - $fixitem;
                        $items = ceil($leftitem / ($cols - $fixitems));
                        for ($m = 0; $m < $cols && $leftitem > 0; $m++) {
                            if (!isset($tmp->col[$m]) || !$tmp->col[$m]) {
                                if ($leftitem > $items) {
                                    $tmp->col[$m] = $items;
                                    $leftitem -= $items;
                                } else {
                                    $tmp->col[$m] = $leftitem;
                                    $leftitem = 0;
                                }
                            }
                        }

                        $cols = count($tmp->col);
                        $tmp->cols = $cols;
                    }
                } else {
                    $tmp->col = array($total);
                }

                $this->beginMenuItems($pid, $level);
                for ($col = 0; $col < $cols && $j < $total; $col++) {
                    $pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : ''); 
                    $i = 0;
                    while ($i < $tmp->col[$col] && $j < $total) {
                      
                        $row = $this->children[$pid][$j];
                        $pos = ($i == 0) ? 'first' : (($i == count($this->children[$pid]) - 1) ? 'last' :
                            '');

                        $this->beginMenuItem($row, $level, $pos);
                        $this->genMenuItem($row, $level, $pos);

                        if ($level < $this->getParam('endlevel')) {
                        	$this->genMenuItems($row->id, $level + 1);
                        }

                        $this->endMenuItem($row, $level, $pos);
                        $j++;
                        $i++;
                    }
                }
                $this->endMenuItems($pid, $level);
            }
        }

        function getParentId($level) {
            if (!$level || (count($this->open) < $level)) return 1;
            return $this->open[count($this->open) - $level];
        }
    }
}