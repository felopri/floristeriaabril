jQuery(document).ready(function () {
  
//____________________________________________________________________________________________ tooltip 

    if (jQuery("[rel=tooltip]").length) {
	jQuery("[rel=tooltip]").tooltip();
  }

// ____________________________________________________________________________________________ resize display

//        var myWidth = 0, myHeight = 0;
//        myWidth = window.innerWidth;
//        myHeight = window.innerHeight;
//        jQuery('body').prepend('<div id="size" style="position:fixed;z-index:999">Width = '+myWidth+', Height = '+myHeight+'</div>');
//        jQuery(window).resize(function(){
//            var myWidth = 0, myHeight = 0;
//            myWidth = window.innerWidth;
//            myHeight = window.innerHeight;
//            jQuery('#size').remove();
//            jQuery('body').prepend('<div id="size" style="position:fixed;z-index:999">Width = '+myWidth+', Height = '+myHeight+'</div>');
//   
//        });

// ____________________________________________________________________________________________ responsive menu

	var mainMenu = jQuery('.globalMenu ul.menu');	

	mainMenu.find('li.parent > a').next('ul').hide();
	mainMenu.find('li.parent > a').append('<span class="arrow"></span>');

    jQuery(function() {
	  mainMenu.find('li.parent').hover(function() {
	    if (!jQuery(this).children('ul').is(':visible')) {
		  jQuery(this).children('ul').stop().slideDown('normal');
	    }
	  },
	    function() {
		if (jQuery(this).children('ul').is(':visible')) {
		  jQuery(this).children('ul').slideUp(4);
		}
	    });
    });
    
// ____________________________________________________________________________________________

    
 });






















