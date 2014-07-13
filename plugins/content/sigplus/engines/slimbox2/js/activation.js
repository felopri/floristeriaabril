/**
* @file
* @brief    sigplus Image Gallery Plus activation hooks for Slimbox2
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

if (typeof(__jQuery__) == 'undefined') {
	var __jQuery__ = jQuery;
}
__jQuery__(function($) {  // shorthand for $(document).ready(function() {...})
	$("a[href$=\'jpg\']").attr("rel","lightbox");
	$("a[href$=\'gif\']").attr("rel","lightbox");
	$("a[href$=\'png\']").attr("rel","lightbox");
	$("a[rel^='lightbox']").slimbox({});
});