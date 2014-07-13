/**@license boxplus: helper function for Facebook integration
 * @author  Levente Hunyadi
 * @version 1.4.2
 * @remarks Copyright (C) 2009-2010 Levente Hunyadi
 * @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
 * @see     http://hunyadi.info.hu/projects/boxplus
 **/

__jQuery__(function ($) {
	// var container = $('<div id="boxplus-facebook" />').appendTo('body');

	window.boxplusFacebookCaption = function (anchor, sizing) {
		var summarynode = $("#" + $("img", anchor).attr("id") + "_summary");  // get summary node
		var summarytext = summarynode.size() ? summarynode.html() : anchor.attr("title");
		if (sizing) {
			return summarytext;
		} else {
			//container.html('<fb:like xmlns:fb="http://www.facebook.com/2008/fbml" href="' + anchor[0].href + '" layout="button_count" show_faces="false" width="150" colorscheme="light"></fb:like>' + summarytext);
			//FB.XFBML.parse(container[0]);
			//return container.children();
			return '<iframe src="http://www.facebook.com/plugins/like.php?href=' + escape(anchor[0].href) + '&amp;layout=button_count&amp;show_faces=false&amp;width=150&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe> ' + summarytext;
		}
	}
});
