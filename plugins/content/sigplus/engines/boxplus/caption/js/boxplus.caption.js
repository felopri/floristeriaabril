/**@license boxplus mouse-over image caption engine
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
	/**
	* Returns the URL of of a high-resolution image version.
	* @return A well-formed URL, or false.
	*/
	function _getImageDownloadUrl(image) {
		var anchor = image.parent('a');
		return anchor.size() ? anchor.attr('href') : false;
	}

	function _getCaption(image) {
		return image.attr('alt');  // image caption text (if any)
	}

	/**
	* Adds a mouse-over image caption to a single image.
	*/
	$.fn.boxplusCaption = function (settings) {
		// default configuration properties
		var defaults = {
			position: 'overlay',             // caption position: ['overlay'|'figure']
			download: _getImageDownloadUrl,  // function that returns URL to high-resolution image version, or false
			caption: _getCaption,            // function that returns the text that belongs an image
			metadata: false,                 // function that returns the image metadata DOM node, or false
			dialog: false                    // function that binds metadata icon to metadata dialog
		};
		settings = $.extend(defaults, settings);
		settings = $.extend({
			alwaysOnTop: settings.position == 'figure'  // whether to keep captions on screen even after mouse pointer leaves image bounds
		}, settings);

		var image = this;
		var
			imagetext = settings.caption(image),
			download = settings.download,
			metadata = settings.metadata,
			dialog = settings.dialog;
		var
			url = download && download(image),   // url is assigned a URL, or false
			meta = metadata && metadata(image);  // meta is assigned a DOM node wrapped in a jQuery object, or false
		var showButtons = url || meta;

		if (!imagetext && !showButtons) {
			return this;  // nothing to show in caption
		}

		var isFigureCaption = settings.position == 'figure';
		var sibling = image.parent('a').size() ? image.parent() : image;

		// prevent tooltip text from being displayed when mouse cursor is moved over image
		image.attr('title', '');  // Internet Explorer
		var title = sibling.attr('title');  // save "title" attribute value
		sibling.hover(  // other browsers
			function () {
				sibling.removeAttr('title');
			},
			function () {
				sibling.attr('title', title);
			}
		);

		var container = $('<div class="boxplus-container" />').insertAfter(sibling);
		var caption = $('<div class="boxplus-imagecaption boxplus-hidden"></div>').addClass(isFigureCaption ? 'boxplus-figurecaption' : 'boxplus-overlaycaption').appendTo(container).html(imagetext);
		if (showButtons) {
			var buttons = $('<div class="boxplus-buttons" />');
			if (url) {
				$('<div class="boxplus-download" />').appendTo(buttons).click(function () {
					window.location.href = url;
					return false;  // prevent event propagation
				});
			}
			if (meta && dialog) {
				dialog($('<div class="boxplus-metadata" />').appendTo(buttons), image);
			}
			caption.append(buttons);
		}

		var parent = container.parent().mouseenter(function () {
			var off = image.offset();
			container.css({
				width: image.outerWidth(),
				height: image.outerHeight()
			}).offset(off).offset(off);  // offset takes border and padding into account but not margin, must be repeated for IE
			caption.removeClass('boxplus-hidden');
		});
		if (!settings.alwaysOnTop) {
			parent.mouseleave(function () {
				caption.addClass('boxplus-hidden');
			});
		}
		parent.mouseenter().mouseleave();

		return this;  // support chaining
	}

	/**
	* Adds a mouse-over image caption to all images in a gallery.
	* A gallery is assumed to be an ordered or unordered list (ol or ul), where the first image
	* (img) in each list item (li) is the image to tag with a caption.
	*/
	$.fn.boxplusCaptionGallery = function (settings) {
		$('li img:first-child', this).each(function () {
			$(this).boxplusCaption(settings);
		});
		return this;  // support chaining
	}
})(__jQuery__);