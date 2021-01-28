$(function() {
	var set_report_mode = function(type) {
		var summary_report_type;
		if (type) {
			summary_report_type = type;
		}
		else {
			//This fix for Cucumber 'Edit Settings' test case
			summary_report_type = getUrlParam('standardreport')?'standard':'custom';
		}
		switch (summary_report_type) {
			case 'standard':
				$('.standard').show();
				$('.custom').hide();
				break;
			case 'custom':
				$('.standard').hide();
				$('.custom').show();
				break;
		}
	};

	$('#report_mode_form input').on('change', function() {
		set_report_mode(this.value);
	});
	set_report_mode($('#report_mode_form input:checked').val());

	$("#report_period").on('change', function() {
		show_calendar($(this).val());
	});
	show_calendar($('#report_period').val());

	// reset options and reload page
	$('#new_report').on('click', function() {
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		self.location.href = base_uri;
	});

	$('.comments').editable(_site_domain + _index_page + '/alert_history/add_comment', {
		data: function(value) {
			var that = $(this);
			that.addClass('editing-comments').on('blur', 'input', function() {
				that.removeClass('editing-comments');
			});
			return $(value).filter('.content').text()
		},
		submitdata: function(value, settings) {
			$(this).removeClass('editing-comments');
			var eventrow = $(this).parents('.eventrow');
			return {
				timestamp: eventrow.data('timestamp'),
				event_type: eventrow.data('statecode'),
				host_name: eventrow.data('hostname'),
				service_description: eventrow.data('servicename'),
				csrf_token: _csrf_token,
				comment: $('input', this).val() // (today) you cannot use value, because it's the original HTML
			}
		},
		width: 'none'
	});

	$('.toggle-long-output').on('click', function() {
		var toggler = $(this);
		var span = toggler.parent().siblings('.alert-history-long-output');
		if (toggler.text() === '+') {
			toggler.text('-');
			span.show();
		} else if (toggler.text() === '-') {
			toggler.text('+');
			span.hide();
		}
	});
});
