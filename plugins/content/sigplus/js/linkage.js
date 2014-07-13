/**@license sigplus Image Gallery Plus gallery external linkage
 * @author  Levente Hunyadi
 * @version 1.4.2
 * @remarks Copyright (C) 2009-2010 Levente Hunyadi.
 * @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
 * @see     http://hunyadi.info.hu/projects/sigplus
 **/

if (typeof(__jQuery__) == 'undefined') {
	var __jQuery__ = jQuery;
}
(function ($) {
	$.fn.sigplusLinkage = function (items, rel, count, progressive, deftitle, defdescription) {
		var gallery = this;
		var galleryid = gallery.attr('id');
		var list = $('<ul />').appendTo(gallery);

		$.each(items, function (index, item) {
			var url = item[0];
			var primaryURL = item[1];  // usually a preview image URL
			var width = item[2];
			var height = item[3];
			var secondaryURL = item[4];  // usually a thumbnail image URL in place of primary image while it is loading
			var title = item[5] ? item[5] : deftitle;
			var description = item[6] ? item[6] : defdescription;
			var downloadURL = item[7];
			var iptc = item[8];

			// preview image (possibly wrapped in anchor)
			var imageid = galleryid + '_img' + ('000' + index).substr(-4);
			var image = $('<img />').attr({
				id: imageid,
				width: width,
				height: height,
				alt: $('<div />').html(title).text()
			});
			image.css({  // IE fix
				width: width,
				height: height
			});
			var imagesrc = primaryURL;
			if (count > 0 && index >= count) {  // primary image never shown
				imagesrc = null;
				image.attr({
					longdesc: secondaryURL ? secondaryURL : primaryURL  // overwrites blank image
				});
			} else if (secondaryURL && progressive) {
				imagesrc = secondaryURL;  // overwritten with primary URL
				image.attr({
					longdesc: primaryURL
				});
			}
			if (imagesrc) {
				image.attr({
					src: imagesrc
				});
			}

			if (url) {
				var anchor = $('<a />').attr({
					href: url,
					rel: rel,
					title: $('<div />').html(description).text()  // strip HTML tags
				}).append(image);
			} else {
				var anchor = image;  // no anchor
			}

			// image metadata
			var metadata = $('<div style="display:none !important;" />').attr('id', imageid + '_metadata');
			if (description) {
				$('<div>' + description + '</div>').attr('id', imageid + '_summary').appendTo(metadata);
			}
			if (downloadURL) {
				$('<a rel="download" />').attr('href', downloadURL).appendTo(metadata);
			}
			if (iptc) {
				var metatable = $('<table />');
				for (var key in iptc) {
					var value = iptc[key];

					var row = $('<tr />').appendTo(metatable);
					$('<th />').appendTo(row).text(key);

					var str = $.isArray(value) ? value.join(', ') : value;
					$('<td />').appendTo(row).text(str);
				}
				$('<div />').attr('id', imageid + '_iptc').append(metatable).appendTo(metadata);
			}

			var listitem = count > 0 && index >= count ? '<li style="display:none !important" />' : '<li />';
			$(listitem).append(anchor).append(metadata.children().size() ? metadata : $()).appendTo(list);
		});
	}
})(__jQuery__);