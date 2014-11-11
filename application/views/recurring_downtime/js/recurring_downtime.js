$(document).ready(function() {
//	$('.time-entry').timePicker();
	$("#setup_form").bind('submit', function() {
		return check_setup();
	});

	$('.content').on('click', '.recurring_delete', function(ev) {
		ev.preventDefault();
		var this_id = $(this).data('recurring-id');

		if (confirm(_('Are you sure that you would like to delete this schedule.\nPlease note that already scheuled downtime won\'t be affected by this and will have to be deleted manually.\nThis action can\'t be undone.'))) {
			$.ajax({
				url:_site_domain + _index_page + '/recurring_downtime/delete',
				type: 'POST',
				data: {schedule_id: this_id},
				success: function(data) {
					if (data) {
						$.notify(data);
						window.setTimeout(function() {
							lsfilter_main.refresh();
						}, 1500);
					}
					else {
						$.notify('An unexpected error occured', {'sticky':true});
					}
				},
				error: function(){
					$.notify("An unexpected error occured", {'sticky':true});
				}
			});
		}
		return false;
	});

	$('#fixed').bind('change', function() {
		if ($(this).is(':checked'))
			$('#triggered_row').hide();
		else
			$('#triggered_row').show();
	}).each(function() {
		if ($(this).is(':checked'))
			$('#triggered_row').hide();
		else
			$('#triggered_row').show();
	});

	$('#progress').css('position', 'absolute').css('top', '90px').css('left', '470px');

	$('#select-all-days').on('click', function() {
		$('.recurring_day').prop('checked', true);
	});
	$('#deselect-all-days').on('click', function() {
		$('.recurring_day').prop('checked', false);
	});
	$('#select-all-months').on('click', function() {
		$('.recurring_month').prop('checked', true);
	});
	$('#deselect-all-months').on('click', function() {
		$('.recurring_month').prop('checked', false);
	});
});

function check_timestring(timestring) {
	if (timestring.indexOf(':') === -1) {
		return false;
	}
	// We have hh:mm or hh:mm:ss
	var timeparts = timestring.split(':');
	if ((timeparts.length !== 2 && timeparts.length !== 3) ||
		isNaN(timeparts[0]) ||
		isNaN(timeparts[1]) ||
		(timeparts.length === 3 && isNaN(timeparts[2]))
	) {
		return false;
	}
	return true;
}

function check_setup()
{
	if (!check_form_values()) {
		return false;
	}

	var err_str = '';

	var comment = $.trim($('textarea[name=comment]').val());
	var start_time = $.trim($('input[name=start_time]').val());
	var end_time = $.trim($('input[name=end_time]').val());
	var duration = $.trim($('input[name=duration]').val());
	var fixed = $('#fixed').attr('checked');
	var days = $('.recurring_day');
	var months = $('.recurring_month');

	if (comment == '' || start_time == '' || end_time == '' || (!fixed && duration == '')) {
		// required fields are empty
		// _form_err_empty_fields
		err_str += '<li>' + _form_err_empty_fields + '</li>';
	} else {
		// check for special input

		// start_time field
		if (!check_timestring(start_time)) {
			err_str += '<li>' + sprintf(_form_err_bad_timeformat, _form_field_start_time) + '</li>';
		}

		// end_time field
		if (!check_timestring(end_time)) {
			err_str += '<li>' + sprintf(_form_err_bad_timeformat, _form_field_end_time) + '</li>';
		}

		// duration field
		if (!fixed && !check_timestring(duration)) {
			err_str += '<li>' + sprintf(_form_err_bad_timeformat, _form_field_duration) + '</li>';
		}
	}
	days = days.filter(function() {
		return $(this).prop('checked');
	});
	if (days.length === 0) {
		err_str += '<li>You must check at least one day of the week</li>';
	}
	months = months.filter(function() {
		return $(this).prop('checked');
	});
	if (months.length === 0) {
		err_str += '<li>You must check at least one month</li>';
	}

	if (err_str != '') {
		$('#response').attr("style", "");
		$('#response').html("<ul class=\"error\">" + err_str + "</ul>");
		window.scrollTo(0,0); // make sure user sees the error message
		return false;
	}

	/**
	 * Everything validated ok.
	 * Check if schedule matches today and if so ask the user if a downtime
	 * should be inserted today.
	 */
	var day_values = Array();
	var month_values = Array();
	days.each(function() {
		day_values.push($(this).val());
	});
	months.each(function() {
		month_values.push($(this).val());
	});
	if (fixed) {
		fixed = 1;
	} else {
		fixed = 0;
	}
	var d = new Date();
	if ($.inArray(d.getDay().toString(), day_values) !== -1 && $.inArray((d.getMonth() +1).toString(), month_values) !== -1) {
		if (confirm("The schedule you are creating matches today, would you like to schedule a downtime for today?\nClick 'Cancel' to save your recurring schedule without scheduling a downtime for today or 'Ok' to save recurring schedule and schedule downtimes today.")) {
			// Downtime type string
			var object_type = $('#downtime_type option:selected').val();
			// Array of selected objects
			var objects = [];
			$('#objects option').each(function() {
				objects.push($(this).val());
			});
			$.ajax({
				url: _site_domain + _index_page + '/recurring_downtime/insert_downtimes',
				type: 'post',
				async: false,
				data: {
					objects: objects,
					object_type: object_type,
					start_time: start_time,
					end_time: end_time,
					fixed: fixed,
					duration: duration,
					comment: comment
				},
				success: function(result) {
					$.notify(result);
				},
				error: function(result) {
					$.notify(result.responseText, {'sticky':true, 'type':'critical'});
				}
			});
		}
	}
	return true;
}
