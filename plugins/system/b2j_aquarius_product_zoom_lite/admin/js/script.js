
/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

parent = Array();
jQuery(document).ready(function(){
    
    jQuery('.pro').parent().children('label').css('text-decoration', 'line-through');
    
    jQuery('.pxinput').after('<span class="pxtext">px</span>');
    jQuery('.sinput').after('<span class="mstext">sec</span>');
    jQuery('.msinput').after('<span class="mstext">ms</span>');

    if(jQuery('#jform_params_zoom_mode').val()==0){
        jQuery('#jform_params_magnifier_background_color-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border_color-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border_radius-lbl').parent().hide();
        jQuery('#jform_params_zoom_percent-lbl').parent().hide();
        jQuery('#jform_params_zoom_width-lbl').parent().hide();
        jQuery('#jform_params_zoom_height-lbl').parent().hide();
        jQuery('#jform_params_zoom_position-lbl').parent().hide();
        jQuery('#jform_params_zoom_border-lbl').parent().hide();
        jQuery('#jform_params_zoom_border_color-lbl').parent().hide();
        jQuery('#jform_params_zoom_border_radius-lbl').parent().hide();
        jQuery('#jform_params_zoom_movement-lbl').parent().hide();
        jQuery('#jform_params_fullscreen_background_color-lbl').parent().hide();
        jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().hide();
        jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().hide();
    } else if(jQuery('#jform_params_zoom_mode').val()==1){
        jQuery('#jform_params_magnifier_background_color-lbl').parent().show();
        jQuery('#jform_params_magnifier_border-lbl').parent().show();
        jQuery('#jform_params_magnifier_border_color-lbl').parent().show();
        jQuery('#jform_params_magnifier_border_radius-lbl').parent().show();
        jQuery('#jform_params_zoom_percent-lbl').parent().show();
        jQuery('#jform_params_zoom_width-lbl').parent().show();
        jQuery('#jform_params_zoom_height-lbl').parent().show();
        jQuery('#jform_params_zoom_position-lbl').parent().show();
        jQuery('#jform_params_zoom_border-lbl').parent().show();
        jQuery('#jform_params_zoom_border_color-lbl').parent().show();
        jQuery('#jform_params_zoom_border_radius-lbl').parent().show();
        jQuery('#jform_params_zoom_movement-lbl').parent().show();
        jQuery('#jform_params_fullscreen_background_color-lbl').parent().show();
        jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().show();
        jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().show();
    } else {
        jQuery('#jform_params_magnifier_background_color-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border_color-lbl').parent().hide();
        jQuery('#jform_params_magnifier_border_radius-lbl').parent().hide();
        jQuery('#jform_params_zoom_percent-lbl').parent().hide();
        jQuery('#jform_params_zoom_width-lbl').parent().hide();
        jQuery('#jform_params_zoom_height-lbl').parent().hide();
        jQuery('#jform_params_zoom_position-lbl').parent().hide();
        jQuery('#jform_params_zoom_border-lbl').parent().hide();
        jQuery('#jform_params_zoom_border_color-lbl').parent().hide();
        jQuery('#jform_params_zoom_border_radius-lbl').parent().hide();
        jQuery('#jform_params_zoom_movement-lbl').parent().hide();
        jQuery('#jform_params_fullscreen_background_color-lbl').parent().show();
        jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().show();
        jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().show();
    }
    jQuery('#jform_params_zoom_mode').change(function(){
        if(jQuery('#jform_params_zoom_mode').val()==0){
            jQuery('#jform_params_magnifier_background_color-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border_color-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border_radius-lbl').parent().hide();
            jQuery('#jform_params_zoom_percent-lbl').parent().hide();
            jQuery('#jform_params_zoom_width-lbl').parent().hide();
            jQuery('#jform_params_zoom_height-lbl').parent().hide();
            jQuery('#jform_params_zoom_position-lbl').parent().hide();
            jQuery('#jform_params_zoom_border-lbl').parent().hide();
            jQuery('#jform_params_zoom_border_color-lbl').parent().hide();
            jQuery('#jform_params_zoom_border_radius-lbl').parent().hide();
            jQuery('#jform_params_zoom_movement-lbl').parent().hide();
            jQuery('#jform_params_fullscreen_background_color-lbl').parent().hide();
            jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().hide();
            jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().hide();
        } else if(jQuery('#jform_params_zoom_mode').val()==1){
            jQuery('#jform_params_magnifier_background_color-lbl').parent().show();
            jQuery('#jform_params_magnifier_border-lbl').parent().show();
            jQuery('#jform_params_magnifier_border_color-lbl').parent().show();
            jQuery('#jform_params_magnifier_border_radius-lbl').parent().show();
            jQuery('#jform_params_zoom_percent-lbl').parent().show();
            jQuery('#jform_params_zoom_width-lbl').parent().show();
            jQuery('#jform_params_zoom_height-lbl').parent().show();
            jQuery('#jform_params_zoom_position-lbl').parent().show();
            jQuery('#jform_params_zoom_border-lbl').parent().show();
            jQuery('#jform_params_zoom_border_color-lbl').parent().show();
            jQuery('#jform_params_zoom_border_radius-lbl').parent().show();
            jQuery('#jform_params_zoom_movement-lbl').parent().show();
            jQuery('#jform_params_fullscreen_background_color-lbl').parent().show();
            jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().show();
            jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().show();
        } else {
            jQuery('#jform_params_magnifier_background_color-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border_color-lbl').parent().hide();
            jQuery('#jform_params_magnifier_border_radius-lbl').parent().hide();
            jQuery('#jform_params_zoom_percent-lbl').parent().hide();
            jQuery('#jform_params_zoom_width-lbl').parent().hide();
            jQuery('#jform_params_zoom_height-lbl').parent().hide();
            jQuery('#jform_params_zoom_position-lbl').parent().hide();
            jQuery('#jform_params_zoom_border-lbl').parent().hide();
            jQuery('#jform_params_zoom_border_color-lbl').parent().hide();
            jQuery('#jform_params_zoom_border_radius-lbl').parent().hide();
            jQuery('#jform_params_zoom_movement-lbl').parent().hide();
            jQuery('#jform_params_fullscreen_background_color-lbl').parent().show();
            jQuery('#jform_params_fullscreen_background_opacity-lbl').parent().show();
            jQuery('#jform_params_fullscreen_thumbnail_position-lbl').parent().show();
        }
    });
});

