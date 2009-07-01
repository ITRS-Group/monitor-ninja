$(document).ready(function() {
	/**
	*	Show the checkbox to show/hide "page header" if
	*	we find the content-header div in the current page
	*/
	if ($('#content-header').text()!='') {
		$('#noheader_ctrl').show();

		// Let checkbox state reflect visibility of the #content-header div
		if ($('#content-header').is(':visible')) {
			// force unchecked checkbox
			$('#noheader_chbx').attr('checked', false);
		} else {
			// mark current state by checking the checkbox
			$('#noheader_chbx').attr('checked', true);
		}
	}

	/**
	*	Bind some functionality to the checkbox state change event
	*	This involves setting the correct value for the noheader GET parameter
	*	and passing the new value to the refresh script so that the value
	*	will persist between refreshes.
	*/
	$('#noheader_chbx').bind('change', function() {
		var noheader = $.query.get('noheader');
		if ($(this).attr('checked')) {
			$('#content-header').hide();
			var new_url = $.query.set('noheader', 1);
		} else {
			$('#content-header').show();
			var new_url = $.query.set('noheader', 0);
		}
		sURL = new_url.toString();
	});
});
