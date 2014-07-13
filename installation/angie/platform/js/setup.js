/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

var setupSuperUsers = {};
var setupDefaultTmpDir = '';
var setupDefaultLogsDir = '';

/**
 * Initialisation of the page
 */
$(document).ready(function(){
	// Enable tooltips
	$('.help-tooltip').tooltip();
	
	$('div.navbar div.btn-group a:last').click(function(e){
		document.forms.setupForm.submit();
		return false;
	});
	
	$('#usesitedirs').click(function(e){
		setupOverrideDirectories();
	});
});


function setupSuperUserChange(e)
{
	var saID = $('#superuserid').val();
	var params = {};

	$.each(setupSuperUsers, function(idx, sa){
		if(sa.id == saID)
		{
			params = sa;
		}
	});
	
	$('#superuseremail').val('');
	$('#superuserpassword').val('');
	$('#superuserpasswordrepeat').val('');
	$('#superuseremail').val(params.email);
}

function openFTPBrowser()
{
	var hostname = $('#ftphost').val();
	var port = $('#ftpport').val();
	var username = $('#ftpuser').val();
	var password = $('#ftppass').val();
	var directory = $('#fptdir').val();

	if ((port <= 0) || (port >= 65536))
	{
		port = 21;
	}

	var url = 'index.php?view=ftpbrowser&tmpl=component'
		+ '&hostname=' + encodeURIComponent(hostname)
		+ '&port=' + encodeURIComponent(port)
		+ '&username=' + encodeURIComponent(username)
		+ '&password=' + encodeURIComponent(password)
		+ '&directory=' + encodeURIComponent(directory);

		document.getElementById('browseFrame').src = url;

	$('#browseModal').modal({
		keyboard: false
	});
}

function useFTPDirectory(path)
{
	$('#ftpdir').val(path);
	$('#browseModal').modal('hide');
}

function setupOverrideDirectories()
{
	$('#tmppath').val(setupDefaultTmpDir);
	$('#logspath').val(setupDefaultLogsDir);
}