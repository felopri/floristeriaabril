/**
* @file
* @brief    sigplus Image Gallery Plus initialization for Slimbox2
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

if (typeof(__jQuery__) == 'undefined') {
	var __jQuery__ = jQuery;
}
(function($) {
	$.fn.bindSlimbox = function (options) {
		// Link mapper for Slimbox.
		var linkmapper = function (el) {
			var elem = $(el);
			var image = $('img', el);
			var url = elem.attr('href');

			var summaryNode = $('#'+image.attr('id')+'_summary');
			if (summaryNode.size()) {
				return [url, summaryNode.html()];  // unescape HTML entities
			} else if (image.attr('title')) {
				return [url, image.attr('title')];
			} else {
				return [url, elem.attr('title')];
			}
		}

		// Link filter for Slimbox.
		var linkfilter = function (el) {
			return (this == el) || ((this.rel.length > 'slimbox2'.length) && (this.rel == el.rel));
		}
		
		$('a[rel|="slimbox2"]', this).slimbox(options, linkmapper, linkfilter);
	};
})(__jQuery__);