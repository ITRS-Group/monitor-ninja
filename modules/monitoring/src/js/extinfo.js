/**
 * When executing custom commands we do it over ajax and present the result in
 * a dialog (lightbox)
 */
$(document).on('click','a[data-custom-command]', function (e) {

	e.preventDefault();

	var command = $(this).text();
	var href = $(this).attr('href');

	var present = function (content)  {

		var lightbox = LightboxManager.create();
		var header = document.createElement('h1');
		header.textContent = 'Command result - ' + command;

		lightbox.show();
		lightbox.header(header);
		lightbox.content(content);
		lightbox.button("OK", function() {
			LightboxManager.remove_topmost();
		});

	};

	$.get(href)
		.done(function (data) {

			present($('<div />').append(
				$('<p class="output" />').html(data.output)
			).get(0));

		})
		.fail(function (data) {

			try {
				data = JSON.parse(data.responseText);
			} catch (e) {
				/* Possibly in the case of an unhandled exception */
				data = {output: "An unexpected error occured when attempting to execute the custom command"}
			}

			present($('<div />').append(
				$('<p class="alert error" />').append(
					$('<h2>').text('Command failed')
				),
				$('<p class="output" />').html(data.output)
			).get(0));

		});
});

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
	}).always(function () {
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
