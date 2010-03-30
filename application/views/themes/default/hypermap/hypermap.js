$(document).ready(function() {
	setTimeout('fixLinks()', 1000);
});

function fixLinks()
{
	$('#iframe').contents().find('a').each(function() {
		switch($(this).attr('href')) {
			case 'status.cgi?host=all':
				$(this).attr('href', _site_domain + _index_page + '/status/host');
				$(this).attr('target', '_parent');
				break;
			case 'status.cgi?hostgroup=all':
				$(this).attr('href', _site_domain + _index_page + '/status/hostgroup');
				$(this).attr('target', '_parent');
				break;
		}
	});
	setTimeout('fixLinks()', 5000);
}