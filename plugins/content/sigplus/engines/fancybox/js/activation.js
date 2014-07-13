/**
* @file
* @brief    sigplus Image Gallery Plus activation hooks for Fancybox
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

(__jQuery__ ? __jQuery__ : jQuery)(function($) {
	$('a[href$=".jpg"], a[href$=".jpeg"], a[href$=".png"], a[href$=".gif"], a[rel^=lightbox], a[rel^=fancybox]').fancybox();
});
