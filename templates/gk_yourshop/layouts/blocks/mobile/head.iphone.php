<?php

// This is the code which will be placed in the head section

// No direct access.
defined('_JEXEC') or die;

$this->addCSS($this->URLtemplate() . '/css/mobile/iphone.css');
// include JavaScript
$this->addJS($this->URLtemplate() . '/js/mobile/zepto.js');
$this->addJS($this->URLtemplate() . '/js/mobile/gk.iphone.js');
// remove mootools and other template scripts
GKParser::$customRules['/<script type="text\/javascript">(.*?)<\/script>/mis'] = '';
GKParser::$customRules['/<script type="text\/javascript" src="(.*?)media\/system\/js(.*?)"><\/script>/mi'] = '';