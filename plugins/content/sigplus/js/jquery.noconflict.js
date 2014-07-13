/**@license sigplus Image Gallery Plus jQuery on-demand inclusion
 * @author  Levente Hunyadi
 * @version 1.4.2
 * @remarks Copyright (C) 2009-2010 Levente Hunyadi.
 * @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
 * @see     http://hunyadi.info.hu/projects/sigplus
 **/

if (typeof(__jQuery__) == 'undefined') {
	var __jQuery__ = jQuery.noConflict();

	if (typeof(__jQueryOther__) != 'undefined') {  // restore other version
		jQuery = __jQueryOther__;
	}
}