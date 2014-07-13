/**@license boxplus image transition engine
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
	var max = Math.max;
	var floor = Math.floor;
	var ceil = Math.ceil;

	/**
	* Maximum computed width of matched elements including margin, border and padding.
	*/
	$.fn.maxWidth = function () {
		var width = 0;
		this.each( function(index, el) {
			width = max(width, $(el).safeWidth());
		});
		return width;
	}

	/**
	* Maximum computed height of matched elements including margin, border and padding.
	*/
	$.fn.maxHeight = function () {
		var height = 0;
		this.each( function(index, el) {
			height = max(height, $(el).safeHeight());
		});
		return height;
	}

	/**
	* "Safe" dimension of an element.
	* Some browsers give invalid values with .width() but others give the meaningless,
	* value "auto" with .css('width'), this function bridges the differences.
	*/
	function _safeDimension(obj, dim) {
		var attrvalue = parseInt(obj.attr(dim));  // attribute numeric value (if present)
		var cssstring = obj.css(dim);
		var cssvalue = /[0-9]+px/.test(cssstring) ? parseInt(cssstring) : 0;
		return Math.max(obj[dim](), attrvalue ? attrvalue : 0, cssvalue ? cssvalue : 0);  // convert NaN to 0
	}

	$.fn.safeWidth = function () {
		return _safeDimension(this, 'width');
	}

	$.fn.safeHeight = function () {
		return _safeDimension(this, 'height');
	}

	/**
	* Creates a new image slider from a collection of images.
	* The method should be called on a ul or ol element that wraps a set of li elements.
	*/
	$.fn.boxplusTransition = function (settings) {
		// default configuration properties
		var defaults = {
			navigation: 'horizontal',   // orientation of navigation buttons, or do not show navigation buttons at all ['horizontal'|'vertical'|false]
			loop: true,                 // whether the image sequence loops such that the first image follows the last [true|false]
			contextmenu: true,          // whether the context menu appears when right-clicking an image [true|false]
			orientation: 'vertical',    // alignment of bars used in transition ['vertical'|'horizontal']
			slices: 15,                 // number of bars to use in transition animation
			effect: 'fade',             // image transition effect ['fade'|'bars'|'bars+fade'|'shutter'|'shutter+fade']
			easing: 'swing',
			duration: 500,              // duration for transition animation [ms]
			delay: 4000                 // delay between successive animation steps [ms]
		};
		settings = $.extend(defaults, settings);

		var lists = this.filter('ul, ol');  // filter elements that are not lists

		// iterate over elements if invoked on an element collection
		lists.each(function () {
			// short-hand access to settings
			var isNavigationVertical = settings.navigation == 'vertical';
			var isOrientationHorizontal = settings.orientation == 'horizontal';
			var sliceCount = settings.slices;
			var duration = settings.duration;
			var delay = settings.delay;

			// status information
			var sliderIndexPosition = 0;  // index of item currently shown
			var animation = false;        // true if an animation is in progress

			// DOM elements
			var list = $(this).wrap('<div />').before('<div />').addClass('boxplus-hidden');
			var wrapper = list.parent().addClass('boxplus-wrapper');
			var items = $('li', list).css({
				position: 'absolute',
				left: 0,
				top: 0
			}).find('img:first');

			// forces following an anchor (in a cancellable way) even when click event is triggered with jQuery
			items.parent('a').click(function (event) {
				if (!event.isDefaultPrevented()) {
					location.href = this.href;
				}
			});

			var container = list.prev().addClass('boxplus-transition').addClass(CLASS_DISABLED).click(function () {
				items.eq(sliderIndexPosition).parent('a').click();  // when an image is clicked, the anchor wrapping the original image (if any) should be followed
			});

			// get maximum width and height of image slider items
			var itemCount = items.length;
			var itemWidth = items.maxWidth();
			var itemHeight = items.maxHeight();

			// set width and height of image container
			wrapper.add(container).css({
				width: itemWidth,
				height: itemHeight
			});

			switch (settings.navigation) {
				case 'horizontal': case 'vertical':
					var cls = 'boxplus-' + settings.navigation;
					container.addClass(cls);

					// setup overlay navigation controls
					function _addButton(cls) {
						return '<div class="boxplus-' + cls + '" />';
					}
					container.prepend(
						$(_addButton('prev') + _addButton('next')).addClass(cls).addClass(
							(isNavigationVertical ? itemWidth : itemHeight) < 120 ? 'boxplus-small' : 'boxplus-large'
						)
					);

					// bind events for navigation controls
					$('.boxplus-prev', container).click(scrollPrevious);
					$('.boxplus-next', container).click(scrollNext);
			}

			if (!settings.contextmenu) {
				$(document).bind('contextmenu', function (event) {  // subscribe to right-click event
					return !container.children().add(container).filter(event.target).size();  // prevent right-click on image
				});
			}

			// add bars to container for animation
			var sliceDim = (isOrientationHorizontal ? itemHeight : itemWidth) / sliceCount;
			for (var sliceIndex = 0; sliceIndex < sliceCount; sliceIndex++) {
				var sliceOffset = floor(sliceIndex*sliceDim);
				$('<div class="boxplus-transition-bars" />').css({
					left: isOrientationHorizontal ? 0 : sliceOffset,
					top: isOrientationHorizontal ? sliceOffset : 0,
					height: isOrientationHorizontal ? sliceDim : itemHeight,
					width: isOrientationHorizontal ? itemWidth : sliceDim,
					visibility: 'hidden'
				}).appendTo(container);
			}

			// update visibility of navigation controls
			_updatePaging();
			container.removeClass(CLASS_DISABLED);
			scrollFirst();

			// slider animation
			if (delay > 0) {
				delay = max(delay, duration + 500);
				var intervalID = window.setInterval(scrollNext, delay);

				// stop animation when mouse moves over an image
				container.mouseover(function () {
					window.clearInterval(intervalID);
				}).mouseout(function () {
					intervalID = window.setInterval(scrollNext, delay);
				});
			}

			//
			// Callback functions
			//

			function scrollFirst() {
				return scroll('first');
			}

			function scrollPrevious() {
				return scroll('prev');
			}

			function scrollNext() {
				return scroll('next');
			}

			function scrollLast() {
				return scroll('last');
			}

			/**
			* Sets the image shown as the background image of elements.
			* @param elem The element whose background-image property to set.
			*/
			function _setImage(e, x, y) {
				var item = items.eq(sliderIndexPosition);  // item to be shown
				e.css({
					backgroundImage: 'url("' + item.attr('src') + '")',
					backgroundPosition: ((itemWidth - item.safeWidth()) / 2 - x) + 'px ' + ((itemHeight - item.safeHeight()) / 2 - y) + 'px'
				});
			}

			/**
			* Preloads an image for later display.
			* @param item The element to use to acquire the URL of the image.
			*/
			function _preloadImage(item) {
				var longdesc = item.attr('longdesc');
				if (longdesc) {  // higher-resolution image is available
					item.attr('src', longdesc).attr('longdesc', '');
				}
			}

			function _preloadImages() {
				_preloadImage(items.eq(sliderIndexPosition));
				_preloadImage(items.eq((sliderIndexPosition - 1) % itemCount));
				_preloadImage(items.eq((sliderIndexPosition + 1) % itemCount));
			}

			/**
			* Execute image transition.
			*/
			function scroll(dir) {
				var bars = $('.boxplus-transition-bars', container);

				if (animation) {  // clear ongoing transitions
					_setImage(container, 0, 0);
					bars.clearQueue().stop().css('visibility', 'hidden');
				}
				animation = true;  // indicate an ongoing transition

				switch (dir) {
					case 'first':
						sliderIndexPosition = 0; break;
					case 'prev':
						sliderIndexPosition = (sliderIndexPosition - 1) % itemCount; break;
					case 'next':
						sliderIndexPosition = (sliderIndexPosition + 1) % itemCount; break;
					case 'last':
						sliderIndexPosition = itemCount - 1; break;
					default:
						return;
				};
				_updatePaging();
				_preloadImages();

				bars.css({  // reset bars background image, height, width, opacity, etc.
					opacity: 1
				}).each(function (index) {  // set the image shown as the background image of bars with computing offset position
					var bar = $(this);
					var dim = ceil(index*sliceDim+sliceDim) - floor(index*sliceDim);
					bar.css({
						height: isOrientationHorizontal ? dim : itemHeight,
						width: isOrientationHorizontal ? itemWidth : dim
					});
					var position = bar.position();
					_setImage(bar, position.left, position.top);
				});

				function _transitionFade() {
					bars.css('opacity', 0).show();
					return {opacity: 1};
				}

				function _transitionBars() {
					bars.css(isOrientationHorizontal ? 'width' : 'height', 0);
					if (isOrientationHorizontal) {
						return {width: itemWidth};
					} else {
						return {height: itemHeight};
					}
				}

				function _transitionShutter() {
					bars.css(isOrientationHorizontal ? 'height' : 'width', 0);
					if (isOrientationHorizontal) {
						return {height: ceil(sliceDim)};
					} else {
						return {width: ceil(sliceDim)};
					}
				}

				var target;
				switch (settings.effect) {
					case 'fade':
						target = _transitionFade(); break;
					case 'bars':
						target = _transitionBars(); break;
					case 'bars+fade':
						target = $.extend(_transitionBars(), _transitionFade()); break;
					case 'shutter':
						target = _transitionShutter(); break;
					case 'shutter+fade':
						target = $.extend(_transitionShutter(), _transitionFade()); break;
				}
				bars.css('visibility', 'visible');

				// function to arrange bars in a specific order
				var ordfun = function (index) { return index; };
				switch (dir) {
					case 'first': case 'prev':
						ordfun = function (index) { return sliceCount-1-index; }; break;
				}

				// register animation events for bars
				bars.each(function (index) {
					var k = ordfun(index);
					var options = {
						duration: 500,
						easing: settings.easing
					};
					if (k == sliceCount-1) {
						$.extend(options, {
							complete: function () {
								animation = false;
								_setImage(container, 0, 0);
								bars.css('visibility', 'hidden');
							}
						});
					}

					// fire animation after an initial delay
					$(this).delay(k * duration / sliceCount).animate(target, options);
				});

				return false;  // prevent event propagation
			}

			/**
			* Update which navigation links are enabled.
			*/
			function _updatePaging() {
				if (!settings.loop) {
					$('.boxplus-prev', container).toggleClass(CLASS_DISABLED, sliderIndexPosition <= 0);
					$('.boxplus-next', container).toggleClass(CLASS_DISABLED, sliderIndexPosition >= itemCount-1);
				}
			}
		});

		return this;  // support chaining
	}
})(__jQuery__);