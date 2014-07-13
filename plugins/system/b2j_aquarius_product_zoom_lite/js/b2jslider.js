
/* ---------------------------------------------------------------------------------------------------------------------
 * Bang2Joom Aquarius Product Zoom Lite for Joomla! 2.5+
 * ---------------------------------------------------------------------------------------------------------------------
 * Copyright (C) 2011-2012 Bang2Joom. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Website: http://www.bang2joom.com
  ----------------------------------------------------------------------------------------------------------------------
 */

function B2JPlgSlider (c) {
	
	var mainSize = c.image_size;
	var marginLeft = c.margin_left;
	var marginRight = c.margin_right;
	var marginBottom = c.margin_bottom;
	var marginTop = c.margin_top;
	var padding = c.padding;
	var border = c.border;
	var tSize;
	var pos;
	var ltLimit;
	var rbLimit;
	var oneSlide;
	function init(){
		var parentSize = c.vertical?jQuery("#"+c.inner_container).parent().height():jQuery("#"+c.inner_container).parent().width();
		mainSize = c.image_size;
		marginLeft = c.margin_left;
		marginRight = c.margin_right;
		marginBottom = c.margin_bottom;
		marginTop = c.margin_top;
		padding = c.padding;
		border = c.border;
		if(parentSize<c.image_size){
			mainSize = parentSize;
			percent = mainSize/c.image_size;
			marginLeft= parseFloat(marginLeft*percent);
			marginRight = parseFloat(marginRight*percent);
			marginBottom = parseFloat(marginBottom*percent);
			marginTop =	parseFloat(marginTop*percent);
			padding = parseFloat(padding*percent);
			border = parseFloat(border*percent);
		}
		tSize = mainSize- (c.vertical?c.num_item*(marginTop+marginBottom+2*border+2*padding):c.num_item*(marginLeft+marginRight+2*border+2*padding));
		if(c.ignore_lt_margin){
			c.vertical?jQuery("#"+c.viewport+" ul").css("top","-"+marginTop+"px"):jQuery("#"+c.viewport+" ul").css("left","-"+marginLeft+"px");
			c.vertical?tSize += marginTop:tSize += marginLeft;
			c.vertical?pos = -marginTop:pos = -marginLeft;
			ltLimit = pos;
		}else{
			pos = 0;
			ltLimit = pos;
			c.vertical?jQuery("#"+c.viewport+" ul").css("top",0):jQuery("#"+c.viewport+" ul").css("left",0);
		}
		if(c.ignore_rb_margin){
			c.vertical?tSize+= marginBottom:tSize+= marginRight;
		}
		tSize=parseInt(tSize/c.num_item);
		if(!c.vertical){
			jQuery("#"+c.inner_container).css("width","100%");
			jQuery("#"+c.inner_container).css("max-width",mainSize+"px");
			jQuery("#"+c.inner_container).css("height",tSize+(marginTop+marginBottom+2*border+2*padding)+"px");
			ulSize = (tSize+marginLeft+marginRight+2*border+2*padding)*jQuery("#"+c.viewport+" ul li").length;
		}else{
			jQuery("#"+c.inner_container).css("height","100%");
			jQuery("#"+c.inner_container).css("max-height",mainSize+"px");
			jQuery("#"+c.inner_container).css("width",tSize+(marginLeft+marginRight+2*border+2*padding)+"px");
			ulSize = (tSize+marginTop+marginBottom+2*border+2*padding)*jQuery("#"+c.viewport+" ul li").length;
		}
		jQuery("#"+c.viewport).css("width","100%");
		jQuery("#"+c.viewport).css("height","100%");
		if(c.ignore_rb_margin){
			rbLimit = c.vertical?-(ulSize-mainSize-marginBottom):-(ulSize-mainSize-marginRight);
		}else{
			rbLimit = -(ulSize-mainSize);
		}
		ulSize+=20;
		jQuery("#"+c.viewport+" ul li").css({"padding":padding+"px",
			"width":tSize+"px",
			"height":tSize+"px",
			"margin-left":marginLeft+"px",
			"margin-right":marginRight+"px",
			"margin-top":marginTop+"px",
			"margin-bottom":marginBottom+"px",
			"border":border+"px solid "+c.border_color});
		if(!c.vertical){
			jQuery("#"+c.viewport+" ul").css("width",ulSize+"px");
			oneSlide = tSize+2*padding+marginRight+marginLeft+2*border;
			jQuery("#"+c.inner_container+" .prev_button").css("height",tSize+(2*border+2*padding)+"px");
			jQuery("#"+c.inner_container+" .prev_button").css("width",(tSize+(2*border+2*padding))/3+"px");
			jQuery("#"+c.inner_container+" .prev_button").css("top",marginTop+"px");
			jQuery("#"+c.inner_container+" .prev_button").css("left",0);
			jQuery("#"+c.inner_container+" .next_button").css("height",tSize+(2*border+2*padding)+"px");
			jQuery("#"+c.inner_container+" .next_button").css("width",(tSize+(2*border+2*padding))/3+"px");
			jQuery("#"+c.inner_container+" .next_button").css("top",marginTop+"px");
			jQuery("#"+c.inner_container+" .next_button").css("right",0);
		}else{
			jQuery("#"+c.viewport+" ul").css("height",ulSize+"px");
			oneSlide = tSize+2*padding+marginTop+marginBottom+2*border;
			jQuery("#"+c.inner_container+" .prev_button").addClass('top_button');
			jQuery("#"+c.inner_container+" .prev_button").removeClass('prev_button');
			jQuery("#"+c.inner_container+" .top_button").css("width",tSize+(2*border+2*padding)+"px");
			jQuery("#"+c.inner_container+" .top_button").css("height",(tSize+(2*border+2*padding))/3+"px");
			jQuery("#"+c.inner_container+" .top_button").css("left",marginLeft+"px");
			jQuery("#"+c.inner_container+" .top_button").css("top",0);
			
			jQuery("#"+c.inner_container+" .next_button").addClass('bottom_button');
			jQuery("#"+c.inner_container+" .next_button").removeClass('next_button');
			jQuery("#"+c.inner_container+" .bottom_button").css("width",tSize+(2*border+2*padding)+"px");
			jQuery("#"+c.inner_container+" .bottom_button").css("height",(tSize+(2*border+2*padding))/3+"px");
			jQuery("#"+c.inner_container+" .bottom_button").css("left",marginLeft+"px");
			jQuery("#"+c.inner_container+" .bottom_button").css("bottom",0);
		}
	}
	if(jQuery("#"+c.viewport+" ul li").length<c.num_item){
		jQuery("#"+c.inner_container+" .next_button").remove();
		jQuery("#"+c.inner_container+" .bottom_button").remove();
		jQuery("#"+c.inner_container+" .prev_button").remove();
		jQuery("#"+c.inner_container+" .top_button").remove();
	}
	init();
	jQuery(window).resize(function(){
		init();
	});
	jQuery("#"+c.inner_container+" .prev_button").click(function(){
		if(jQuery(this).is(':animated')) return;
		if(pos+c.row_column_per_slide*oneSlide<=ltLimit){
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({left:(pos+c.row_column_per_slide*oneSlide)+"px"},c.animation_speed,function(){
				pos += c.row_column_per_slide*oneSlide;
			});
		}else{
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({left:ltLimit+"px"},c.animation_speed,function(){
				pos = ltLimit;
			});
		}
	});
	jQuery("#"+c.inner_container+" .next_button").click(function(){
		if(jQuery(this).is(':animated')) return;
		if(pos-c.row_column_per_slide*oneSlide>=rbLimit){		
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({left:(pos-c.row_column_per_slide*oneSlide)+"px"},c.animation_speed,function(){
				pos -= c.row_column_per_slide*oneSlide;
			});
		}else{
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({left:rbLimit+"px"},c.animation_speed,function(){
				pos = rbLimit;
			});
		}
	});
	jQuery("#"+c.inner_container+" .top_button").click(function(){
		if(jQuery(this).is(':animated')) return;
		if(pos+c.row_column_per_slide*oneSlide<=ltLimit){
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({top:(pos+c.row_column_per_slide*oneSlide)+"px"},c.animation_speed,function(){
				pos += c.row_column_per_slide*oneSlide;
			});
		}else{
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({top:ltLimit+"px"},c.animation_speed,function(){
				pos = ltLimit;
			});
		}
	});
	jQuery("#"+c.inner_container+" .bottom_button").click(function(){
		if(jQuery(this).is(':animated')) return;
		if(pos-c.row_column_per_slide*oneSlide>=rbLimit){		
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({top:(pos-c.row_column_per_slide*oneSlide)+"px"},c.animation_speed,function(){
				pos -= c.row_column_per_slide*oneSlide;
			});
		}else{
			jQuery("#"+c.viewport+" ul").stop(true,false).animate({top:rbLimit+"px"},c.animation_speed,function(){
				pos = rbLimit;
			});
		}
	});
	var startX=0,startY=0;
	var oldStart=0;
	var endX=0,endY=0;
	var timeStart=0,timeEnd=0;
	var legalStart = -1;
	var a=0.01;
	var timeLim = 100;
	var startLim = 20;
	var mouseMoved=0;
	var veryStart = 0;
	jQuery("#"+c.viewport+" ul li img").bind('touchend',function(e){
		e.preventDefault();
		if(legalStart==-1 || legalStart==0) jQuery(this).trigger('click');
	});
	jQuery("#"+c.viewport+" ul").bind('touchstart',function(e){
		if(jQuery("#"+c.inner_container+" .next_button").lenght!=0){
			jQuery("#"+c.inner_container+" .next_button").remove();
			jQuery("#"+c.inner_container+" .bottom_button").remove();
			jQuery("#"+c.inner_container+" .prev_button").remove();
			jQuery("#"+c.inner_container+" .top_button").remove();
		}
		e.preventDefault();
		jQuery(this).stop(true,false);
		legalStart=-1;
		mouseMoved=0;
		var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
        startX=touch.pageX;
        startY=touch.pageY;
		oldStart= c.vertical?touch.pageY:touch.pageX;
		veryStart = c.vertical?touch.pageY:touch.pageX;
		timeStart = (new Date).getTime();
	});
	if(c.vertical){
		jQuery("#"+c.viewport+" ul").bind('touchmove',function(e){
			if(legalStart==0) return;
			var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
			endX=touch.pageX;
			endY=touch.pageY;
			if(legalStart==-1){
				if(Math.abs(endY-startY)>Math.abs(endX-startX) && (Math.abs(endX-startX)>startLim || Math.abs(endY-startY)>startLim)) legalStart=1;
				else if(Math.abs(endX-startX)>startLim || Math.abs(endY-startY)>startLim){
					legalStart=0;
					return;
				}
				if(Math.abs(endX-startX)<startLim && Math.abs(endY-startY)<startLim) return;
			}
			mouseMoved=1;
			e.preventDefault();
			timeEnd = (new Date).getTime();
			if(timeEnd-timeStart>=timeLim){
				timeStart = timeEnd;
				oldStart=startY;
				startY = endY;
			}
			jQuery(this).stop(true,false);
			var tormuzz=1;
			if(pos+(endY-veryStart)<rbLimit){
				tormuzz=(Math.abs(pos+(endY-veryStart)-rbLimit)+mainSize)/mainSize;
			}
			else if(pos+(endY-veryStart)>ltLimit){
				tormuzz=(pos+(endY-veryStart)-ltLimit+mainSize)/mainSize;
			}
			jQuery(this).css({"top":(pos+(endY-veryStart)/tormuzz)+"px"});
		});
		jQuery("#"+c.viewport+" ul").bind('touchend touchcancel',function(e){
			if(legalStart==0) return;
			pos = (pos+(endY-veryStart));
			if(timeEnd-timeStart>timeLim/2 || startX==oldStart){
				S = endY - startY;
				t = timeEnd-timeStart;
				v = S/t;
			}else{
				S=endY-oldStart;
				t = (timeEnd-timeStart+timeLim);
				v = S/t;
			}
			if(mouseMoved){
				dir = Math.abs(v)/v;
				v = Math.abs(v);
				toGoS = 3*(v*v)/a;
				startPos = pos;
				if(pos<rbLimit){ 
					pos = rbLimit;
					jQuery(this).stop(true,false).animate({top:pos},{duration:1000,easing:'easeOutExpo'});
				}
				else if(pos>ltLimit){
					pos = ltLimit;
					jQuery(this).stop(true,false).animate({top:pos},{duration:1000,easing:'easeOutExpo'});
				}else{
					pos+=dir*toGoS;
					if(pos<rbLimit){ 
						pos = rbLimit;
						jQuery(this).stop(true,false).animate({top:pos},{duration:1000,easing:'easeOutExpo'});
					}
					else if(pos>ltLimit){
						pos = ltLimit;
						jQuery(this).stop(true,false).animate({top:pos},{duration:1000,easing:'easeOutExpo'});
					}else{
						jQuery(this).stop(true,false).animate({top:pos},{duration:1000,easing:'easeOutExpo'});
					}
				}
			}
			legalStart=0;
		});
	}
	else{
		jQuery("#"+c.viewport+" ul").bind('touchmove',function(e){
			if(legalStart==0) return;
			var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
			endX=touch.pageX;
			endY=touch.pageY;
			if(legalStart==-1){
				if(Math.abs(endX-startX)>Math.abs(endY-startY) && (Math.abs(endY-startY)>startLim || Math.abs(endX-startX)>startLim)) legalStart=1;
				else if(Math.abs(endY-startY)>startLim || Math.abs(endX-startX)>startLim){
					legalStart=0;
					return;
				}
				if(Math.abs(endY-startY)<startLim && Math.abs(endX-startX)<startLim) return;
			}
			mouseMoved=1;
			e.preventDefault();
			timeEnd = (new Date).getTime();
			if(timeEnd-timeStart>=timeLim){
				timeStart = timeEnd;
				oldStart=startX;
				startX = endX;
			}
			jQuery(this).stop(true,false);
			var tormuzz=1;
			if(pos+(endX-veryStart)<rbLimit){
				tormuzz=(Math.abs(pos+(endX-veryStart)-rbLimit)+mainSize)/mainSize;
			}
			else if(pos+(endX-veryStart)>ltLimit){
				tormuzz=(pos+(endX-veryStart)-ltLimit+mainSize)/mainSize;
			}
			jQuery(this).css({"left":(pos+(endX-veryStart)/tormuzz)+"px"});
		});
		jQuery("#"+c.viewport+" ul").bind('touchend touchcancel',function(e){
			if(legalStart==0) return;
			pos = (pos+(endX-veryStart));
			if(timeEnd-timeStart>timeLim/2 || startX==oldStart){
				S = endX - startX;
				t = timeEnd-timeStart;
				v = S/t;
			}else{
				S=endX-oldStart;
				t = (timeEnd-timeStart+timeLim);
				v = S/t;
			}
			if(mouseMoved){
				dir = Math.abs(v)/v;
				v = Math.abs(v);
				toGoS = 3*(v*v)/a;
				startPos = pos;
				if(pos<rbLimit){ 
					pos = rbLimit;
					jQuery(this).stop(true,false).animate({left:pos},{duration:1000,easing:'easeOutExpo'});
				}
				else if(pos>ltLimit){
					pos = ltLimit;
					jQuery(this).stop(true,false).animate({left:pos},{duration:1000,easing:'easeOutExpo'});
				}else{
					pos+=dir*toGoS;
					if(pos<rbLimit){ 
						pos = rbLimit;
						jQuery(this).stop(true,false).animate({left:pos},{duration:1000,easing:'easeOutExpo'});
					}
					else if(pos>ltLimit){
						pos = ltLimit;
						jQuery(this).stop(true,false).animate({left:pos},{duration:1000,easing:'easeOutExpo'});
					}else{
						jQuery(this).stop(true,false).animate({left:pos},{duration:1000,easing:'easeOutExpo'});
					}
				}
			}
			legalStart=0;
		});
	}
}