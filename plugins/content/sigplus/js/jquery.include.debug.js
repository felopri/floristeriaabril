/**@license sigplus Image Gallery Plus jQuery on-demand inclusion
 * @author  Levente Hunyadi
 * @version 1.4.2
 * @remarks Copyright (C) 2009-2010 Levente Hunyadi.
 * @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
 * @see     http://hunyadi.info.hu/projects/sigplus
 **/

function __jQuery_version_compare__() {
	function _compare(current, required) {
		var cur = current.split('.');
		var req = required.split('.');
		for (var k = 0; k < req.length; k++) {
			var c = parseInt(cur[k]);
			var r = parseInt(req[k]);
			if (c == r) {  // check next component (equality fails on NaN)
				continue;
			}
			return c > r;  // returns false if required version is less than current version or one of the components is NaN
		}
		return true;
	}
	return _compare(jQuery.fn.jquery, '1.4');
}

if (typeof(__jQuery__) == 'undefined') {
	// check if another version of jQuery has already been included
	if (typeof(jQuery) != 'undefined' && !__jQuery_version_compare__()) {
		var __jQueryOther__ = jQuery;  // backup other version
	}
	
	// load required version
	if (typeof(jQuery) == 'undefined' || !__jQuery_version_compare__()) {
		google.load('jquery', '1', {uncompressed:true});  // .js file will be loaded after this script terminates
	} else {
		var __jQuery__ = jQuery;
	}
}