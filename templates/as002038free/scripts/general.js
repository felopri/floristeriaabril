
/***************************************************************************************/
/*
/*		Designed by 'AS Designing'
/*		Web: http://www.asdesigning.com
/*		Email: info@asdesigning.com
/*		License: ASDE Commercial
/*
/**************************************************************************************/

var asjQuery = jQuery.noConflict();

asjQuery(window).load(function() 
{
	asjQuery(".menu li").fadeIn(1);	
	asjQuery("#topmenu ul.menu > li").addClass("toplevel")
	asjQuery("#topmenu ul.menu > li:last-child").removeClass("toplevel")
	
	asjQuery("#phocagallery-module-ri").css("margin", "0px auto");		

});


