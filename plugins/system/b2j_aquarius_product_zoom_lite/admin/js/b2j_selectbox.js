
/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

jQuery(document).ready(function(){
    var parent_container_class = '.pane-sliders select';
	var selLength = jQuery(parent_container_class).length;
	
	for(var z=0;z<selLength;z++){
	
		var html = jQuery(parent_container_class).eq(z).html();		
		var count = jQuery(parent_container_class).eq(z).find("option").length;
		var markup = "<div class='customSelect'>";
		var sel = jQuery(parent_container_class).eq(z).find("option:selected").html();
		markup +="<div class='first'>"+sel+"</div><div class='options'>";
		for(var i=1; i<=count; i++){
			var t = jQuery(parent_container_class).eq(z).find("option:nth-child("+i+")").html();
			markup += "<div rel="+i+">"+t+"</div>";
		} 
		markup += "</div></div>";
		jQuery(parent_container_class).eq(z).hide();
		jQuery(parent_container_class).eq(z).after(markup);
	}	
	jQuery(".customSelect > .options > div").click(function(event){
		var f=jQuery(this).html();
		jQuery(this).parent().siblings(".first").html(f);
		jQuery(this).parent().parent().removeClass("open");
		var index = jQuery(this).attr("rel");
		jQuery(this).parent().parent().prev(parent_container_class).find("option:nth-child("+index+")").attr("selected","selected");
		jQuery(this).parent().parent().prev(parent_container_class).trigger("change");
	})
	jQuery(".customSelect > div.first").click(function(event){
		event.stopPropagation();	
		if(jQuery(this).parent().hasClass("open")){
			jQuery(".customSelect.open").removeClass("open");
		}
		else{
			jQuery(".customSelect.open").removeClass("open");
			jQuery(this).parent().addClass("open");
		}
	})	
	jQuery("html").click(function(){
		jQuery(".customSelect.open").removeClass("open");
	})
	
})