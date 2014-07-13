/**
* @file
* @brief    sigplus Image Gallery Plus activation hooks for boxplus
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

(__jQuery__ ? __jQuery__ : jQuery)(function ($) {
	$('a[href]').filter(function () {
		return /\.(gif|jpe?g|png|swf)$/i.test(this.pathname) && !/_(blank|parent|self|top)/.test($(this).attr('target'));
	}).add('a[rel^=lightbox],a[rel^=boxplus]').boxplus();
});