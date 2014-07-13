
/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

jQuery(window).load(function(){
    this.percent =  jQuery('.zoom_container img').attr('percent')/100;
    this.zoomContainerW = parseFloat(jQuery('.zoom_container').css('width'));
    this.zoomContainerH = parseFloat(jQuery('.zoom_container').css('height'));
    this.mainImageW = 0;
    this.mainImageH = 0;
    this.percentMain = 0;
    this.mainImageOffsetLeft = 0;
    this.mainImageOffsetTop = 0;
    original_image_height = 0;
    original_image_width = 0;
    this.borderMain = parseFloat(jQuery('.main-image').css("border-left-width"));
    this.no_zoom = jQuery('.main-image').hasClass('nozoom');
    this.zoom_mode = jQuery('.main-image').attr('rel');
    
    // fullscreen mode toggler based on image resized
    var resized = parseInt(jQuery('.main-image').css('max-width')) / parseInt(jQuery('.main-image').css('width'));
    if (resized != 1 || zoom_mode == 2){
        jQuery('.main-image').addClass('nozoom');
        jQuery('.fullscreen_button').css('display', 'block');
        this.no_zoom = true;
    } else {
        jQuery('.main-image').removeClass('nozoom');
        jQuery('.fullscreen_button').css('display', 'none');
        this.no_zoom = false;
    }
    
    jQuery('.fullscreen_layer_container').appendTo('body');
    
    var startX;
    var startY;
    
    var image_aspect = parseInt(jQuery('.main-image').css('max-width')) / parseInt(jQuery('.main-image').css('height'));
    jQuery('.main-image').height((1/image_aspect)*parseInt(jQuery('.main-image').width()));
    
    // Main Image
    var main_image = new Image();
    main_image.onload = function () {
        jQuery('.image_container > img').attr('src', this.src);
        jQuery('.image_container > img').stop(true, false).animate({opacity:1},500);
        
        jQuery('.zoom_container img').attr('src',this.src);
        
        original_image_width = percent*this.width;
        original_image_height = percent*this.height;
        mainImageW = parseFloat(jQuery('.image_container > img').css('width'));
        mainImageH = parseFloat(jQuery('.image_container > img').css('height'));
        
        zoom_container_width = jQuery('.zoom_container').width();
        zoom_container_height = jQuery('.zoom_container').height();
        
        jQuery('.zoom_overlay').css('margin-left', -mainImageW/2);
        jQuery('.zoom_overlay').css('margin-top', -mainImageH/2);
        jQuery('.zoom_overlay').css('height', mainImageH);
        jQuery('.zoom_overlay').css('width', mainImageW);
        
        jQuery('.image_container > img').css('margin-left', -mainImageW/2);
        jQuery('.image_container > img').css('margin-top', -mainImageH/2);
        jQuery('.image_container > img').css('top', '50%');
        jQuery('.image_container > img').css('left', '50%');
        
        percentMain = jQuery('.image_container > img').width()/original_image_width;
        jQuery('.zoom_container img').css('width',original_image_width);
        jQuery('.zoom_container img').css('height',original_image_height);
        jQuery(".zoom_box").css("width", (zoom_container_width*percentMain));
        jQuery(".zoom_box").css("height",(zoom_container_height*percentMain));
        
        mainImageOffsetLeft = jQuery('.image_container > img').offset().left;
        mainImageOffsetTop = jQuery('.image_container > img').offset().top;
        
        jQuery('.main-image').removeClass('loading');
    };
    main_image.src = jQuery('.image_container > img').attr('src')+ '?r=' + Math.random();
    
    // Mouse Events
    this.mousemoved = 1;
    if(jQuery('#is_mobile').val()!='1'){
        jQuery('.main-image').bind('mousemove', function(e){
            if ((original_image_height < zoomContainerH && original_image_width < zoomContainerW) || no_zoom){
                return;
            }
            if(!mousemoved) return;
            if (jQuery('.zoom_container').css('opacity') < 1){
                jQuery('.zoom_container').css('display', 'block');
                jQuery('.zoom_container').stop(true,false).animate({'opacity': 1}, 200);
            }
            jQuery('.zoom_container').mouseenter(function() {
                mousemoved=0;
                mouseLeave();
            });
            jQuery('.zoom_container').css('z-index', 6);
            moveTo(e.pageX, e.pageY, false);
        });

        jQuery('.main-image').bind('mouseleave', function(){
               mouseLeave();
        });
    } else{
        jQuery('.main-image').addClass('nozoom');
        jQuery('.fullscreen_button').css('display', 'block');
        this.no_zoom = true;
    }
    
    //Fullscreen button click
    jQuery('.fullscreen_button').click(function(){
        jQuery('.fullscreen_layer_container').css('display', 'block');
        jQuery('.fullscreen_layer_container').css('z-index', 999);
        jQuery('body').css("overflow","hidden");
        jQuery('.fullscreen_layer_container').stop(true,false).animate({'opacity': 1});
        jQuery('.fullscreen_image_container img').css('left', '50%');
        jQuery('.fullscreen_image_container img').css('top', '50%');
        jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
        jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
        
        var fullscreen_thumbnail_position = jQuery('#b2j_plg_galleryzoom_main_container2').attr('rel');
        var galleryzoom_main_container2_width = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').width());
        var galleryzoom_main_container2_height = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').height());
        
        if (parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').height()) > parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').height())){
            var galleryzoom_main_container2_height = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').height());
        }
        if (parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').width()) > parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').width())){
            var galleryzoom_main_container2_width = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').width());
        }
         
        switch(fullscreen_thumbnail_position){
            case "0"://LEFT
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-top', -galleryzoom_main_container2_height/2);
                break;
            case "1": //RIGHT
                jQuery('#b2j_plg_galleryzoom_main_container2').css('right', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-top', -galleryzoom_main_container2_height/2);
                break;
            case "2": //TOP
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-left', -galleryzoom_main_container2_width/2);
                break;
            case "3": //BOTTOM
                jQuery('#b2j_plg_galleryzoom_main_container2').css('bottom', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-left', -galleryzoom_main_container2_width/2);
                break;
        };
        
        mouseLeave();
        
        jQuery(window).trigger('resize');
    });
    
    //Fullscreen mode by mouse click on image
    if (this.no_zoom){
        jQuery('.main-image').click(function(){
            jQuery('.fullscreen_layer_container').css('display', 'block');
            jQuery('.fullscreen_layer_container').css('z-index', 999);
            jQuery('body').css("overflow","hidden");
            jQuery('.fullscreen_layer_container').stop(true,false).animate({'opacity': 1});
            jQuery('.fullscreen_image_container img').css('left', '50%');
            jQuery('.fullscreen_image_container img').css('top', '50%');
            jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
            jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
        });
    }
    
    //Fullscreen close button click
    jQuery('.fullscreen_close_button').bind('click touchstart',function(){
        jQuery('.fullscreen_layer_container').stop(true,false).animate({'opacity': 0}, function(){
            jQuery('.fullscreen_layer_container').css('display', 'none');
            jQuery('.fullscreen_layer_container').css('z-index', 0);
            jQuery('body').css("overflow","auto");
            jQuery('.fullscreen_image_container img').css('left', '50%');
            jQuery('.fullscreen_image_container img').css('top', '50%');
            jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
            jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
        });
    });
    
    //Window Resize
    jQuery(window).resize(function(){
        jQuery('.main-image').css('height', (1/image_aspect)*parseInt(jQuery('.main-image').width()));
        jQuery('.fullscreen_layer_container').appendTo('body');
        
        mainImageW = parseFloat(jQuery('.image_container > img').css('width'));
        mainImageH = parseFloat(jQuery('.image_container > img').css('height'));
        jQuery('.image_container > img').css('margin-left', -mainImageW/2);
        jQuery('.image_container > img').css('margin-top', -mainImageH/2);
        jQuery('.image_container > img').css('top', '50%');
        jQuery('.image_container > img').css('left', '50%');
    
        jQuery('.fullscreen_image_container img').css('left', '50%');
        jQuery('.fullscreen_image_container img').css('top', '50%');
        jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
        jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
        
        percentMain = jQuery('.image_container > img').width()/original_image_width;
                
        jQuery('.zoom_container img').css('width',original_image_width);
        jQuery('.zoom_container img').css('height',original_image_height);
        jQuery(".zoom_box").css("width", zoom_container_width*percentMain);
        jQuery(".zoom_box").css("height",zoom_container_height*percentMain);
        
        var resized = parseInt(jQuery('.main-image').css('max-width')) / parseInt(jQuery('.main-image').css('width'));
        if (resized != 1 || jQuery('#is_mobile').val()=='1' || zoom_mode == 2){
            jQuery('.main-image').addClass('nozoom');
            jQuery('.fullscreen_button').fadeIn();
            this.no_zoom = true;
        } else {
            jQuery('.main-image').removeClass('nozoom');
            jQuery('.fullscreen_button').fadeOut();
            this.no_zoom = false;
        }
        
        var fullscreen_thumbnail_position = jQuery('#b2j_plg_galleryzoom_main_container2').attr('rel');
        var galleryzoom_main_container2_width = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').width());
        var galleryzoom_main_container2_height = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').height());
        
        if (parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').height()) > parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').height())){
            var galleryzoom_main_container2_height = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').height());
        }
        if (parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2').width()) > parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').width())){
            var galleryzoom_main_container2_width = parseFloat(jQuery('#b2j_plg_galleryzoom_main_container2 ul').width());
        }
         
        switch(fullscreen_thumbnail_position){
            case "0"://LEFT
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-top', -galleryzoom_main_container2_height/2);
                break;
            case "1": //RIGHT
                jQuery('#b2j_plg_galleryzoom_main_container2').css('right', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-top', -galleryzoom_main_container2_height/2);
                break;
            case "2": //TOP
                jQuery('#b2j_plg_galleryzoom_main_container2').css('top', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-left', -galleryzoom_main_container2_width/2);
                break;
            case "3": //BOTTOM
                jQuery('#b2j_plg_galleryzoom_main_container2').css('bottom', 0);
                jQuery('#b2j_plg_galleryzoom_main_container2').css('left', '50%');
                jQuery('#b2j_plg_galleryzoom_main_container2').css('margin-left', -galleryzoom_main_container2_width/2);
                break;
        };
        
        //Fullscreen mode by mouse click on image
        if (this.no_zoom){
            jQuery('.main-image').click(function(){
                jQuery('.fullscreen_layer_container').css('display', 'block');
                jQuery('.fullscreen_layer_container').css('z-index', 999);
                jQuery('body').css("overflow","hidden");
                jQuery('.fullscreen_layer_container').stop(true,false).animate({'opacity': 1});
                jQuery('.fullscreen_image_container img').css('left', '50%');
                jQuery('.fullscreen_image_container img').css('top', '50%');
                jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
                jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
            });
        } else {
            jQuery('.main-image').unbind('click');
        }
        
    });
    
    //Keydown event for closing fullscreen mode
    jQuery(document).keydown(function (e){
        switch (e.keyCode) {
            case 27: //ESC
            case 13: //Enter
            case 32: //Space
                e.preventDefault();
                jQuery('.fullscreen_layer_container').stop(true,false).animate({'opacity': 0}, function(){
                    jQuery('.fullscreen_layer_container').css('display', 'none');
                    jQuery('.fullscreen_layer_container').css('z-index', 0);
                    jQuery('body').css("overflow","auto");
                    jQuery('.fullscreen_image_container img').css('left', '50%');
                    jQuery('.fullscreen_image_container img').css('top', '50%');
                    jQuery('.fullscreen_image_container img').css('margin-top', -jQuery('.fullscreen_image_container img').height()/2);
                    jQuery('.fullscreen_image_container img').css('margin-left', -jQuery('.fullscreen_image_container img').width()/2);
                });
                break;
        }
    });
    
    //Fullscreen image movement
    this.touchX = 0;
	this.touchY = 0;
	jQuery('.fullscreen_layer_container').bind('mousemove', function(move_e){
		moveToFullscreen(move_e);
	})
	jQuery('.fullscreen_layer_container').bind('touchstart', function(e){
		e.preventDefault();
	});
    //Fullscreen image movement touch
    jQuery('.fullscreen_image_container img').bind('touchstart', function(e){
        e.preventDefault();
        var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
        touchX=touch.pageX;
        touchY=touch.pageY; 
        jQuery('.fullscreen_image_container img').bind('touchmove', function(move_e){
            var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
            moveToFullscreenTouch(touch);
        })
    })
    jQuery('.fullscreen_image_container img').bind('touchend', function(){
        jQuery('.fullscreen_image_container img').unbind('touchmove');
    })
});

//Mouse leave function which is called after mouse leave or touchend event occurs.
function mouseLeave(){
    jQuery('.zoom_container').css('z-index', 1);
    jQuery('.zoom_container').stop(true,false).animate({'opacity': 0}, 'fast', function() {
        jQuery(".zoom_box").css("display","none");
        jQuery(".zoom_container").css("display","none");
        mousemoved=1;
    });
}

//Function for image movement in Fullscreen mode (touch)
function moveToFullscreenTouch(move_e){
    if(touchX==0 && touchY==0){
        touchX = move_e.pageX;
        touchY = move_e.pageY
    }
    var vertical_difference= move_e.pageY-touchY;
    var horizontal_difference= move_e.pageX-touchX;
    touchX = move_e.pageX;
    touchY = move_e.pageY;
    //limits
    
    var css_top = parseFloat(jQuery('.fullscreen_image_container img').position().top);
    var css_left = parseFloat(jQuery('.fullscreen_image_container img').position().left);
    var css_margin_top = parseFloat(jQuery('.fullscreen_image_container img').css("margin-top"));
    var css_margin_left = parseFloat(jQuery('.fullscreen_image_container img').css("margin-left"));
    
    var fullscreen_image_width = parseFloat(jQuery('.fullscreen_image_container img').width());
    var fullscreen_image_height = parseFloat(jQuery('.fullscreen_image_container img').height());
    
    var window_width = parseFloat(jQuery(window).width());
    var window_height = parseFloat(jQuery(window).height());
    if(fullscreen_image_width<window_width){
        horizontal_difference=0;
    } else{
        //left
        if(css_left+horizontal_difference > -css_margin_left){
            horizontal_difference = 0;
            jQuery('.fullscreen_image_container img').css("left",-css_margin_left);
        }
        //right
        if(css_left+horizontal_difference + css_margin_left < window_width - fullscreen_image_width){
            horizontal_difference = 0;
            jQuery('.fullscreen_image_container img').css("left", window_width - fullscreen_image_width - css_margin_left);
        }
    }
    if(fullscreen_image_height<window_height) {
        vertical_difference=0;
    } else{
        //top
        if(css_top+vertical_difference > -css_margin_top){
            vertical_difference = 0;
            jQuery('.fullscreen_image_container img').css("top",-css_margin_top);
        }
        //bottom
        if(css_top+vertical_difference + css_margin_top < window_height - fullscreen_image_height){
            vertical_difference = 0;
            jQuery('.fullscreen_image_container img').css("top", window_height - fullscreen_image_height - css_margin_top);
        }
    }
    jQuery('.fullscreen_image_container img').css({"left": (jQuery('.fullscreen_image_container img').position().left + horizontal_difference)+"px",
                                                    "top": (jQuery('.fullscreen_image_container img').position().top + vertical_difference)+"px"});
}

//Function for image movement in Fullscreen mode (no touch)
function moveToFullscreen(move_e){
 	var percentX = (move_e.clientX/jQuery(window).width());
	var percentY = (move_e.clientY/jQuery(window).height());
	jQuery('.fullscreen_layer_container .fullscreen_image_container img').css("margin-left","0px");
	jQuery('.fullscreen_layer_container .fullscreen_image_container img').css("margin-top","0px");
	moveX = -(jQuery('.fullscreen_layer_container .fullscreen_image_container img').width()-jQuery(window).width())*percentX;
	moveY = -(jQuery('.fullscreen_layer_container .fullscreen_image_container img').height()-jQuery(window).height())*percentY;
	jQuery('.fullscreen_image_container img').css({"left": moveX+"px","top": moveY+"px"});
}

// Move to function for changing zoom container coordinates
function moveTo(x, y, is_touch){    
    startX = x;
    startY = y;
    jQuery('.zoom_box').css("z-index",1);
    jQuery(".zoom_box").css("display","inline");
    mainImageOffsetLeft = jQuery('.image_container > img').offset().left;
    mainImageOffsetTop = jQuery('.image_container > img').offset().top;
    var x0 = parseFloat(x-mainImageOffsetLeft);
    var y0 = parseFloat(y-mainImageOffsetTop);
    
    var zoomBoxLeft = x0+parseFloat((jQuery('.main-image').width()-mainImageW)/2)-parseFloat((parseFloat(jQuery(".zoom_box").width()) + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width')))/2);
    var zoomBoxTop = y0+parseFloat((jQuery('.main-image').height()-mainImageH)/2)-parseFloat((parseFloat(jQuery(".zoom_box").height()) + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width')))/2);
    
    var zoomBoxImgleft = -x0+parseFloat(jQuery(".zoom_box").width()/2);
    var zoomBoxImgtop = -y0+parseFloat(jQuery(".zoom_box").height()/2);
    
    // Top limit
    if(zoomBoxTop<parseFloat((jQuery('.main-image').height()-mainImageH)/2)){
        zoomBoxTop=parseFloat((jQuery('.main-image').height()-mainImageH)/2);
        zoomBoxImgtop=0;
    }
    //Left limit
    if(zoomBoxLeft<parseFloat((jQuery('.main-image').width()-mainImageW)/2)){
        zoomBoxLeft=parseFloat((jQuery('.main-image').width()-mainImageW)/2);
        zoomBoxImgleft=0;
    }
    //Bottom limit
    if(zoomBoxTop>(mainImageH+parseFloat((jQuery('.main-image').height()-mainImageH)/2)) - (parseFloat(jQuery(".zoom_box").height() + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width'))))){
        zoomBoxTop=(mainImageH+parseFloat((jQuery('.main-image').height()-mainImageH)/2)) - (parseFloat(jQuery(".zoom_box").height() + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width'))));
        zoomBoxImgtop=-mainImageH+jQuery(".zoom_box").height();
    }
    //Right limit
    if(zoomBoxLeft>(mainImageW+parseFloat((jQuery('.main-image').width()-mainImageW)/2)) - (parseFloat(jQuery(".zoom_box").width() + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width'))))){
        zoomBoxLeft=(mainImageW+parseFloat((jQuery('.main-image').width()-mainImageW)/2)) - (parseFloat(jQuery(".zoom_box").width() + 2 * parseFloat(jQuery(".zoom_box").css('border-left-width'))));
        zoomBoxImgleft=-mainImageW+jQuery(".zoom_box").width();
    }
    
    jQuery(".zoom_box").css("left",zoomBoxLeft);
    jQuery(".zoom_box").css("top",zoomBoxTop);

    var left = (1/percentMain)*(-x0)+zoomContainerW/2;
    var top = (1/percentMain)*(-y0)+zoomContainerH/2;

    if(top>0) top=0;
    if(top<-original_image_height + zoomContainerH) top = -original_image_height + zoomContainerH;
    if(left>0) left = 0;
    if(left<-original_image_width + zoomContainerW) left = -original_image_width + zoomContainerW;
    
    if (jQuery('.zoom_container').hasClass('movement0')){
        jQuery('.zoom_container img').stop(true,false).animate({top:top,left:left}, {duration: 1, easing: 'linear'});
    } else if (jQuery('.zoom_container').hasClass('movement1')){
        jQuery('.zoom_container img').stop(true,false).animate({top:top,left:left}, {duration: 500, easing: 'easeOutCubic'});
    }

}
