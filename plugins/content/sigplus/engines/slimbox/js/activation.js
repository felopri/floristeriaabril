/**
* @file
* @brief    sigplus Image Gallery Plus activation hooks for Slimbox
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2010 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
	With the following code, Slimbox will activate itself automatically on all links pointing to images,
	or more specifically all links having URLs ending with: ".jpg" or ".png" or ".gif".
	As a result, you will not need to set the rel="lightbox" attribute on any link to activate Slimbox.
	Furthermore, all image links contained in the same block or paragraph (having the same parent element)
	will automatically be grouped together in a gallery, so you will not need to specify groups either.
	Images that are alone in their block or paragraph will be displayed individually.
*/

Slimbox.scanPage = function() {
	$$($$('a').filter(function(el) {
		return /\.(jpg|png|gif)$/i.test(el.get('href'));
	})).slimbox({}, null, function(el) {
		var parent = this.getParent();
		return this == el || (parent && parent == el.getParent());
	});
};
if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
	window.addEvent("domready", Slimbox.scanPage);
}