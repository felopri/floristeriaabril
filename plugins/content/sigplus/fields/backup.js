/**
* @file
* @brief    sigplus Image Gallery Plus save and restore settings control
* @author   Levente Hunyadi
* @version  1.4.2
* @remarks  Copyright (C) 2009-2011 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

window.addEvent('domready', function () {
	/**
	* Converts back-end settings into a list of key-value pairs.
	*/
	function settings_backup() {
		var params = $$('*[name^="jform[params]"]').filter(function (item) {
			return item.get('type') != 'radio' || item.get('checked');  // is not a radio button or is a radio button but checked
		});
		var data = params.map(function (item) {  // iterate name-value pairs
			var match = /^jform\[params\]\[(.*)\]$/.exec(item.get('name'));
			if (match) {
				var param_name = match[1];
				if (param_name != 'settings') {  // standard setting
					return param_name + '=' + escape(item.get('value'));
				} else {  // custom settings text box
					return item.get('value').trim();
				}
			}
			return null;
		}).join('\n');
		$('extension-settings-list').set('value', data);
	}

	/**
	* Converts a list of key-value pairs into their back-end equivalent.
	*/
	function settings_restore() {
		var params_mapping = {  // maps inline setting to back-end setting
			maxcount:'thumb_count',
			width:'thumb_width',
			height:'thumb_height',
			crop:'thumb_crop',
			orientation:'slider_orientation',
			navigation:'slider_navigation',
			buttons:'slider_buttons',
			links:'slider_links',
			counter:'slider_counter',
			overlay:'slider_overlay',
			duration:'slider_duration',
			animation:'slider_animation',
			borderstyle:'border_style',
			borderwidth:'border_width',
			bordercolor:'border_color',
			sortcriterion:'sort_criterion',
			sortorder:'sort_order'
		};
		var listitems = [];
		$('extension-settings-list').get('value').split('\n').each(function (item) {
			var i = item.indexOf('=');
			if (i < 0) {
				return;
			}
			var param_name = item.substr(0, i);
			var param_value = item.substr(i+1);
			var params = $$('*[name^="jform[params][' + param_name + ']"], *[name^="jform[params][' + params_mapping[param_name] + ']"]');
			if (params.length > 0) {  // parameter exists, set in settings form
				// set appropriate radio button to checked
				var radiobutton = $pick(params.filter('*[type=radio][value=' + param_value + ']'));
				if (radiobutton) {
					radiobutton.set('checked', true);
				}

				var elem = $pick(params.filter('*:not(input[type=radio])'));
				if (elem) {
					elem.set('value', unescape(param_value));
				}
			} else {  // parameter does not exist, add as custom setting
				listitems.push(item);
			}
		});

		// set custom parameters
		var customparam = $pick($$('*[name="jform[params][settings]"]'));
		if (customparam) {
			customparam.set('value', listitems.join('\n'));
		}
	}

	$('extension-settings-backup').addEvent('click', settings_backup);
	$('extension-settings-restore').addEvent('click', settings_restore);
});