<?php

/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');
jimport('joomla.application.component.helper');
jimport('joomla.error.error');

class JFormFieldb2jhidden extends JFormField {

    protected $type = 'b2jhidden';

    protected function getLabel() {
        return null;
    }

    protected function getInput() {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/css/style.css');
        $doc->addStyleSheet(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/css/b2j_selectbox.css');
        $doc->addScript(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/js/jquery.min.js');
        $doc->addScript(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/js/script.js');
        $doc->addScript(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/js/jscolor.js');
        $doc->addScript(JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/js/b2j_selectbox.js');
        $cache_dir = dirname(dirname(dirname(realpath(__FILE__)))) . "/cache/";
        $files = glob($cache_dir . '*', GLOB_MARK);
        foreach ($files as $file)
            if(strpos($file,"index.html")==false)
            unlink($file);
        return null;
    }

}

?>
