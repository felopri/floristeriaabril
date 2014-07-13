window.addEvent('domready', function() {
	var gf_body = $('paramsbody_googlefontlist').getParent().getParent();
	var gf_header = $('paramsheader_googlefontlist').getParent().getParent();
	if ($('paramsbody_font').getValue() !== 'googlefonts') {
		gf_body.setStyle('display', 'none');
	}
	if ($('paramsheader_font').getValue() !== 'googlefonts') {
		gf_header.setStyle('display', 'none');
	}
	$('paramsbody_font').addEvent('change', function() {
		if (this.getValue() == 'googlefonts') {
			gf_body.setStyle('display', 'table-row');
		}
		else {
			gf_body.setStyle('display', 'none');
		}
	});
	$('paramsheader_font').addEvent('change', function() {
		if (this.getValue() == 'googlefonts') {
			gf_header.setStyle('display', 'table-row');
		}
		else {
			gf_header.setStyle('display', 'none');
		}
	});
	$('paramsbody_googlefontlist').addEvent('change', function() {
		setGoogle('body');
	});
	$$('input.body_googlefont').each(function(el) {
		el.addEvent('change', function() {
			setGoogle('body');
		});
	});
	$('paramsheader_googlefontlist').addEvent('change', function() {
		setGoogle('header');
	});
	$$('input.header_googlefont').each(function(el) {
		el.addEvent('change', function() {
			setGoogle('header');
		});
	});
});

function getTypes(type)
{
	var data = '';
	$$('input.'+type+'_googlefont').each(function(el) {
		if (el.getProperty('checked') == true) data += el.getValue()+',';
	});
	return data;
}

function setGoogle(type)
{
	var val = $('params'+type+'_googlefontlist').getValue();
	val += ','+getTypes(type);
	val = val.substring(0, val.length - 1);
	$('params_'+type+'_googlefont').setProperty('value', val);
}