/**
* @file
* @brief    sigplus Image Gallery Plus initialization for Slimbox
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2010 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

function bindSlimbox(gallery, options) {
	$$(gallery.getElements('a').filter(function(el) {
		return /^slimbox($|-)/i.test(el.get('rel'));
	})).slimbox(
		// options
		options,
		// link mapper
		function(el) {
			var image = el.getElement('img');
			var url = el.get('href');
			var summary = document.id(image.get('id') + '_summary');
			if (summary) {
				return [url, summary.get('html')];
			} else if (image) {
				return [url, image.get('title')];
			} else {
				return [url, el.get('title')]
			}
		},
		// link filter
		function(el) {
			return this == el || (this.get('rel').length > 'slimbox'.length && this.get('rel') == el.get('rel'));
		}
	);
}