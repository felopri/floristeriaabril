/**@license boxplus carousel engine
 * @author  Levente Hunyadi
 * @version 1.4.2
 * @remarks Copyright (C) 2009-2010 Levente Hunyadi
 * @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
 * @see     http://hunyadi.info.hu/projects/boxplus
 **/

/*
* boxplus: a lightweight pop-up window engine shipped with sigplus
* Copyright 2009-2010 Levente Hunyadi
*
* boxplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* boxplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with boxplus.  If not, see <http://www.gnu.org/licenses/>.
*/

if (typeof(__jQuery__) == 'undefined') {
	var __jQuery__ = jQuery;
}
(function ($) {
	var CLASS_DISABLED = 'boxplus-disabled';

	/**
	* Sum or string concatenation as a function.
	*/
	function _sum(a,b) {
		return a + b;
	}
	
	/**
	* Reduce values of a dimension metric for set of elements to a single value.
	* @param dim Dimension metric.
	* @param fn The fold function.
	*/
	function _foldDimension(items, dim, fn) {
		var t = 0;
		items.each( function(index, el) {
			t = fn(t, $(el)[dim](true));
		});
		return t;
	}
	
	/**
	* Maximum computed width of matched elements including margin, border and padding.
	*/
	$.fn.maxOuterWidth = function() {
		return _foldDimension(this, 'outerWidth', Math.max);
	}

	/**
	* Maximum computed height of matched elements including margin, border and padding.
	*/
	$.fn.maxOuterHeight = function() {
		return _foldDimension(this, 'outerHeight', Math.max);
	}

	/**
	* Total computed width of matched elements including margin, border and padding.
	*/
	$.fn.totalOuterWidth = function() {
		return _foldDimension(this, 'outerWidth', _sum);
	}

	/**
	* Total computed height of matched elements including margin, border and padding.
	*/
	$.fn.totalOuterHeight = function() {
		return _foldDimension(this, 'outerHeight', _sum);
	}
	
	/**
	* Background color.
	* @return An array of red, green and blue components as integers, and opacity as a float.
	*/
	$.fn.backColor = function () {
		function int2rgb(values, radix) {
			return $.map(values, function (value) {
				return parseInt(value, radix);
			});
		}

		var backcolor = this.css('background-color');
		var rgb = [0,0,0], alpha = 1.0;
		
		if (backcolor == 'transparent') {
			alpha = 0;
		}
		var mo = backcolor.match(/^#[\da-f]{6}$/i);  // e.g. #aabbcc
		if (mo) {
			var m = mo[0];
			rgb = int2rgb([m.substr(1,2), m.substr(3,2), m.substr(5,2)], 16);  // omit leading #
		}
		mo = backcolor.match(/^#([\da-f]{3})$/i);  // e.g. #abc
		if (mo) {
			var m = mo[0];
			var r = m.substr(1,1), g = m.substr(2,1), b = m.substr(3,1);  // omit leading #, IE7 does not accept [] on strings
			rgb = int2rgb([r+r,g+g,b+b], 16);
		}
		mo = backcolor.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);  // e.g. rgb(1,2,3)
		if (mo) {
			rgb = int2rgb(mo.slice(1), 10);
		}
		mo = backcolor.match(/^rgba\((\d+),\s*(\d+),\s*(\d+)\,\s*(\d+(?:\.\d+)?)\)$/);  // e.g. rgba(1,2,3,0.5)
		if (mo) {
			rgb = int2rgb(mo.slice(1, -1), 10);
			alpha = parseFloat(mo[mo.length-1]);
		}
		rgb.push(alpha);
		return rgb;	}
	
	/**
	* Effective background color.
	* Skips transparent backgrounds until it encounters an element with background color set.
	*/
	$.fn.effectiveBackColor = function () {
		var elem = this;
		do {
			var rgb = elem.backColor();
			elem = elem.parent();
		} while (rgb[3] == 0);  // loop while background color is transparent
		return rgb;
	}

	/**
	* Converts an [r,g,b,a] color array to an "#AARRGGBB" hex string.
	*/
	function color2ahex(rgba) {
		function hex(x) {
			return ("0" + x.toString(16)).slice(-2);
		}
		return "#" + hex(Math.floor(255*rgba[3])) + hex(rgba[0]) + hex(rgba[1]) + hex(rgba[2]);
	}
	
	/**
	* Converts an [r,g,b,a] color array to an "rgba(r,g,b,a)" CSS color definition.
	*/
	function color2rgba(rgba) {
		return 'rgba(' + rgba.join(',') + ')';
	}

	$.fn.boxplusCarousel = function (settings) {
		// default configuration properties
		var defaults = {
			rtl: false,
			orientation: 'horizontal',  // orientation of sliding image ribbon ['horizontal'|'vertical']
			positioning: 'side',        // position of current image in viewport ['side'|'center']
			edges: 'sharp',             // the way images blend into the background at the edges ['sharp'|'blurred']
			navigation: 'top',          // position where navigation controls are displayed ['top'|'bottom'|'both']
			showButtons: true,          // whether to show navigation buttons [true|false]
			showLinks: true,            // whether to show navigation links [true|false]
			showOverlayButtons: true,   // whether to show navigation buttons that overlay image thumbnails [true|false]
			contextmenu: true,          // whether the context menu appears when right-clicking an image [true|false]
			duration: 800,              // duration for scroll animation in milliseconds, or one of ['slow'|'fast']
			delay: 0,                   // time between successive automatic slide steps in milliseconds, or 0 to use no automatic sliding
			opacity: 1                  // item opacity when mouse moves away from item, 1.0 (default) for no opacity visual effect
		};
		settings = $.extend(defaults, settings);
		var isVerticallyOriented = settings.orientation == 'vertical';
		// size of carousel viewport window
		var windowSize = settings.windowSize ? settings.windowSize : (isVerticallyOriented ? 400 : '100%');

		var lists = this.filter('ul, ol');  // filter elements that are not lists

		// iterate over elements if invoked on an element collection
		lists.each(function () {
			var duration = settings.duration;
			var isCentered = settings.positioning == 'center';
			var rtldir = rtl ? 'right' : 'left';

			// DOM elements
			var ribbon = $(this).wrap('<div />');
			var gallery = ribbon.parent().addClass('boxplus-carousel').addClass(CLASS_DISABLED);
			gallery.wrapInner('<div class="boxplus-viewport" />');

			var listitems = $('li:visible', ribbon);
			var items = listitems.find('img:first').attr('src', function (index, srclow) {  // load higher-resolution image version where one is available
				var srchigh = $(this).attr('longdesc');
				return /\.(gif|jpe?g|png)$/i.test(srchigh) ? srchigh : srclow;
			});
			var focusedindex = listitems.size() / 2;  // list item index that is aligned centered with center positioning

			// set width and height of image ribbon that accomodates all items
			var maxWidth = listitems.maxOuterWidth();
			var maxHeight = listitems.maxOuterHeight();
			var rtl = settings.rtl;
			ribbon.css({
				width: isVerticallyOriented ? maxWidth : listitems.totalOuterWidth(),
				height: isVerticallyOriented ? listitems.totalOuterHeight() : maxHeight,
				left: rtl ? 'auto' : 0,
				right: rtl ? 0 : 'auto'
			});
			var galleryWidth = isVerticallyOriented ? maxWidth : windowSize;
			var galleryHeight = isVerticallyOriented ? windowSize : maxHeight;
			var galleryViewport = $('.boxplus-viewport', gallery).css({
				width: galleryWidth,
				height: galleryHeight
			});
			gallery.css({
				width: galleryWidth
			});

			var galleryEffectiveWidth = galleryViewport.width();
			var galleryEffectiveHeight = galleryViewport.height();
			
			// allow carousel functionality only if there is a sufficient number of images
			if (listitems.size() > 1 && gallery.width() >= galleryEffectiveWidth && gallery.height() >= galleryEffectiveHeight) {
				// cannot use windowSize, which is a value with dimension (px or %)
				// inequality can be sharp; e.g. galleryHeight == windowSize for horizontally oriented carousels

				// setup outside navigation controls
				function _addLink(cls) {
					return '<a class="boxplus-' + cls + '" href="javascript:void(0)" />';
				}
				function _addButton(cls) {
					return '<div class="boxplus-' + cls + '" />';
				}
				var
					showButtons = settings.showButtons,
					showLinks = settings.showLinks;
				var navigationBar = '<div class="boxplus-paging">' +
					(showButtons ? _addButton('prev') : '') +
					(showLinks ? _addLink('prev') + ' ' : '') +
					(showLinks ? ' ' + _addLink('next') : '') +
					(showButtons ? _addButton('next') : '') +
					'</div>';
				switch (settings.navigation) {
					case 'both':
						gallery.prepend(navigationBar).append(navigationBar);
						break;
					case 'top':
						gallery.prepend(navigationBar);
						break;
					default:  // case 'bottom':
						gallery.append(navigationBar);
				}

				// setup overlay navigation controls
				if (settings.showOverlayButtons) {
					galleryViewport.append(
						$(_addButton('prev') + _addButton('next')).addClass(
							'boxplus-large ' + (isVerticallyOriented ? 'boxplus-vertical' : 'boxplus-horizontal')
						)
					);
				}
				
				if (settings.edges == 'blurred') {
					// make viewport blurred near edges
					function _addEdge(cls) {
						return '<div class="boxplus-edge boxplus-' + cls + '" />';
					}
					galleryViewport.append(
						$((isCentered ? _addEdge('start') : '') + _addEdge('end')).addClass(  // blur at start edge only for centered positioning
							(isVerticallyOriented ? 'boxplus-vertical' : 'boxplus-horizontal')
						)
					);

					// set gradient color
					var rgbaBack = gallery.effectiveBackColor();
					var rgbaTransparent = [rgbaBack[0],rgbaBack[1],rgbaBack[2],0.0];  // set fully transparent near edges
					$('.boxplus-edge', gallery).each(function () {
						var item = $(this);
						
						item.css('filter', function (index, value) {  // IE
							if (value) {
								// in CSS file, #ff000000 denotes 'from' color (opaque), #00000000 denotes 'to' color (transparent)
								return value.replace('#ff000000', color2ahex(rgbaBack)).replace('#00000000', color2ahex(rgbaTransparent));
							}
						});

						item.css('background-image', function (index, value) {  // standard browsers
							if (value) {
								// in CSS file, rgba(0,0,0,0) stands for 'from' color, rgba(0,0,0,1) stands for 'to' color
								return value.replace(/#000|#000000|rgb\(0,\s*0,\s*0\)|black/,color2rgba(rgbaBack)).replace(/rgba\(0,\s*0,\s*0,\s*1\)|transparent/,color2rgba(rgbaTransparent));
							}
						});
					});
				}

				ribbon.css(getPosition());
			}

			if (!settings.contextmenu) {
				$(document).bind('contextmenu', function (event) {  // subscribe to right-click event
					return !items.filter(event.target).size();  // prevent right-click on image
				});
			}

			// bind events for navigation controls
			var btnPrev = $('.boxplus-prev', gallery).click(scrollPrevious);
			var btnNext = $('.boxplus-next', gallery).click(scrollNext);

			gallery.removeClass(CLASS_DISABLED);
			ribbon.css({
				visibility: 'visible'  // show image ribbon if it has been hidden to avoid erratic browser layout
			});

			// image mouse hover animation
			if (settings.opacity < 1.0) {
				items.css('opacity', settings.opacity);
				items.hover(
					function () {
						$(this).stop().animate({
							opacity: 1.0
						}, 'slow');
					},
					function () {
						$(this).stop().animate({
							opacity: settings.opacity
						}, 'slow');
					}
				);
			}

			// slider animation
			var delay = settings.delay;
			if (delay > 0) {
				delay = Math.max(delay, duration);
				var intervalID = window.setInterval(scrollNext, delay);
				gallery.mouseover(function () {
					window.clearInterval(intervalID);
				}).mouseout(function () {
					intervalID = window.setInterval(scrollNext, delay);
				});
			}

			//
			// Callback functions
			//

			function scrollPrevious() {
				if (ribbon.queue().length > 0) {  // do not execute if an animation is in progress
					return;
				}

				var listitem = listitems.last();
				if (isVerticallyOriented) {
					listitem.css('top', -listitems.totalOuterHeight());
				} else {
					listitem.css(rtldir, -listitems.totalOuterWidth());  // move item to other end
				}
				scroll(-1);
			}

			function scrollNext() {
				if (ribbon.queue().length > 0) {  // do not execute if an animation is in progress
					return;
				}

				scroll(1);
			}
			
			function getPosition() {
				var top = 0, side = 0;
				if (isCentered) {
					var listitemsbefore = listitems.slice(0, focusedindex);
					var listitemcurrent = listitems.eq(focusedindex);
					if (isVerticallyOriented) {
						top = galleryEffectiveHeight / 2 - listitemsbefore.totalOuterHeight() - listitemcurrent.totalOuterHeight() / 2;
					} else {
						side = galleryEffectiveWidth / 2 - listitemsbefore.totalOuterWidth() - listitemcurrent.totalOuterWidth() / 2;
					}
				}
				var position = {
					top: top
				}
				position[rtldir] = side;
				return position;
			}

			function scroll(dir) {
				var movingitem = listitems[dir > 0 ? 'first' : 'last']();  // item that is relocated in the HTML DOM
				
				var off = 0;
				if (isCentered) {
					off = isVerticallyOriented
						? listitems.eq(focusedindex).totalOuterHeight() / 2 + listitems.eq(focusedindex + dir).totalOuterHeight() / 2
						: listitems.eq(focusedindex).totalOuterWidth() / 2 + listitems.eq(focusedindex + dir).totalOuterWidth() / 2;
				} else {
					off = isVerticallyOriented ? movingitem.totalOuterHeight() : movingitem.totalOuterWidth();
				}

				var target = getPosition();
				if (isVerticallyOriented) {
					target['top'] -= dir * off;  // slide by a single item height
				} else {
					target[rtldir] -= dir * off;  // slide by a single item width
				}
				ribbon.animate(target, duration, 'swing', function () {
					movingitem.detach()[(dir > 0 ? 'append' : 'prepend') + 'To'](ribbon);
					listitems = $('li:visible', ribbon);  // re-index list items (detaching and re-attaching an item changes order)
					listitems.css('top', 0).css(rtldir, 0);
					ribbon.css(getPosition());
				});
			}
		});

		return this;  // support chaining
	};
})(__jQuery__);