$(document).on('click','div[data-setting-toggle-command]', function () {

	var toggler = $(this);
	var command = toggler.attr('data-setting-toggle-command');
	var query = toggler.attr('data-setting-toggle-query');
	var on = command.match(/^(disable_|stop_)/);

	/* Do nothing if the current toggle is waiting for response */
	if (toggler.hasClass('toggle-waiting'))
		return;

	toggler.addClass('toggle-waiting');

	query = decodeURIComponent(query);
	var url = _site_domain + _index_page;
	$.post(url + '/cmd/ajax_command', {
		'csrf_token': _csrf_token,
		'command': command,
		'query': query
	}).done(function () {
		if (on) {
			command = command.replace(/disable_/, 'enable_');
			command = command.replace(/stop_/, 'start_');
			toggler.attr('data-setting-toggle-state', 'off');
			toggler.attr('data-setting-toggle-command', command)
		} else {
			command = command.replace(/enable_/, 'disable_');
			command = command.replace(/start_/, 'stop_');
			toggler.attr('data-setting-toggle-state', 'on');
			toggler.attr('data-setting-toggle-command', command)
		}
	}).fail(function () {
		Notify.message("Failed to toggle setting.", {type: "error", sticky: true});
	}).complete(function () {
		toggler.removeClass('toggle-waiting');
	});


});

$(document).on('click', '.information-performance-raw-show', function () {
	var table = $(this).siblings('table');
	if (table.is(':hidden')) {
		$(this).find('.information-performance-raw-show-label').text('Hide raw data');
		$(this).siblings('table').show();
	} else {
		$(this).find('.information-performance-raw-show-label').text('Show raw data');
		$(this).siblings('table').hide();
	}
});
