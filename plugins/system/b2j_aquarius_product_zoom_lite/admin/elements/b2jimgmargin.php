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
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldb2jimgmargin extends JFormField {

    protected $type = 'b2jimgmargin';

    protected function getInput() {
        $name = str_replace("_editor","",$this->element['name'][0]);
        $doc = JFactory::$document;
        $script='
                jQuery(window).load(function(){
			    jQuery("#jform_params_'.$name.'_top").appendTo(jQuery("#'.$name.'_top_wrap"));
                jQuery("#jform_params_'.$name.'_left").appendTo(jQuery("#'.$name.'_left_wrap"));
                jQuery("#jform_params_'.$name.'_right").appendTo(jQuery("#'.$name.'_right_wrap"));
                jQuery("#jform_params_'.$name.'_bottom").appendTo(jQuery("#'.$name.'_bottom_wrap"));

                jQuery("#jform_params_'.$name.'_top-lbl").parent().css("display","none");
                jQuery("#jform_params_'.$name.'_left-lbl").parent().css("display","none");
                jQuery("#jform_params_'.$name.'_right-lbl").parent().css("display","none");
                jQuery("#jform_params_'.$name.'_bottom-lbl").parent().css("display","none");
                });';
        $doc->addScriptDeclaration($script);
        $style='#'.$name.' { width: 361px; float: left}
                #'.$name.' input{font-size: 12px;  padding: 0px; border: 1px solid white; width: 24px; margin-right:2px;text-align:right;height:13px;}
                #'.$name.'_top_wrap,
                #'.$name.'_bottom_wrap,
                #'.$name.'_left_wrap,
                #'.$name.'_right_wrap { width: 46px; line-height: 36px }
                #'.$name.'_top_wrap,
                #'.$name.'_bottom_wrap { margin: 0 auto; }
                #'.$name.'_left_wrap,
                #'.$name.'_right_wrap { float: left; margin-top: 90px; }
                #'.$name.'_left_wrap { margin-right: 10px; }
                #'.$name.'_right_wrap { margin-left: 10px; }
                .'.$name.'_top,
                .'.$name.'_bottom,
                .'.$name.'_left,
                .'.$name.'_right { font-size: 12px; width: 24px; margin-right:2px;text-align:right;}
                #'.$name.'_main_wrap { clear: both; overflow: hidden; }
                #'.$name.'_bg { width: 220px; height: 220px; border: 3px solid #eee; background: no-repeat; float:left; }
                #'.$name.'_img { width: 200px; height: 200px; margin-top:10px; margin-left:10px; margin-right:10px; margin-bottom:10px;  border: 1px solid #ddd; background: #fff; opacity: 0.5; filter:alpha(opacity=50); }
                #'.$name.'_img img{width: 195px;}
                #'.$name.'_top_wrap, #'.$name.'_left_wrap, #'.$name.'_right_wrap, #'.$name.'_bottom_wrap{ width:50px;}
                #'.$name.'_top_wrap > span, #'.$name.'_left_wrap > span, #'.$name.'_right_wrap > span, #'.$name.'_bottom_wrap > span {display:block;float:right;}
                ';
        $doc->addStyleDeclaration($style);
        return '
			<div id="'.$name.'">
				<div id="'.$name.'_top_wrap"><span>px</span></div>
				<div id="'.$name.'_main_wrap">
					<div id="'.$name.'_left_wrap"><span>px</span></div>
					<div id="'.$name.'_bg">
						<div id="'.$name.'_img"><img src=' . JURI::root() . 'plugins/system/b2j_aquarius_product_zoom_lite/admin/images/b2jlogo.png></img></div>
					</div>
					<div id="'.$name.'_right_wrap"><span>px</span></div>	
				</div>
				<div id="'.$name.'_bottom_wrap"><span>px</span></div>
			</div>';
    }

}

?>