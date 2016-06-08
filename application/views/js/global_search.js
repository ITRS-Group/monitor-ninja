$(document).ready(function() {
	var query = $('#query');
	if(!query.length) {
		// fix to avoid autocomplete if element is not present.
		// the autocomplete source is minified and we've got a demo to run..
		// new-host wizard fails without this
		return;
	}
	query.autocomplete({
		serviceUrl:_site_domain + _index_page + '/search/ajax_auto_complete/',
		minChars:2,
		//delimiter: /(,|;)\s*/, // regex or character
		maxHeight:500,
		width:'auto',
		deferRequestBy: 300, //miliseconds
		cacheLength: 0,
		// callback function:
		onSelect: function(value, data){ do_redirect(value, data); }
	});


	var search_old_refresh = 0;
	query.focus(function() {
		search_old_refresh = current_interval;
		ninja_refresh(0);
		$("#ninja_refresh_control").attr('checked', true);
		$('#ninja_refresh_lable').css('font-weight', 'bold');
	});

	query.blur(function() {
		if (current_interval === 0 && search_old_refresh !== 0) {
			current_interval = search_old_refresh;
			ninja_refresh(current_interval);
			$("#ninja_refresh_control").attr('checked', false);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});
});

function do_redirect(value, data)
{
	// if user clicked on information message about
	// nr of rows returned, we shouldn't do anything
	if (data[0] === '') {
		$('#query').val('');
		return false;
	}

	var match = value.split(';');
	if (match.length) value = match[1];

	var template = data[0];
	var path = template.replace('{identifier}', encodeURIComponent(data[1]));

	if (value) {
		path = path.replace('{subidentifier}', encodeURIComponent(value));
	}

	self.location.href = _site_domain + _index_page + path;

}
