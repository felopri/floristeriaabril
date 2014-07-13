window.addEvent('domready', function() {
	$('paramspresets').addEvent('change', function() {
		var answer = confirm("This will replace all values, are you sure?");
		if (!answer) return;

		var parampresets = new Array();
		// build list of presets
		for (i=0; i<presets.preset.length; i++) {
			parampresets[presets.preset[i].attributes.name] = i;
		}

		var preset_id = parampresets[this.getValue()];
		var preset = presets.preset[preset_id];

		// Reset list of presets with preset defaults
		for (z=0; z<preset.param.length; z++) {
			$('params'+preset.param[z].attributes.name).setProperty('value', preset.param[z].attributes.value);
			jscolor.color('params'+preset.param[z].attributes.name, preset.param[z].attributes.value);
		}

	})
});