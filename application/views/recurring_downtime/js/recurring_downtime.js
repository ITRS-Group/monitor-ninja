$(document).ready(function() {
//	$('.time-entry').timePicker();
	$("#setup_form").bind('submit', function() {
		loopElements();
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

	$('#downtime_type').on('change', function() {
		var value = this.value;
		$('.object-list-type').text(value);
		get_members(value, function(all_names) {
			populate_options('objects_tmp', 'objects', all_names);
		});
	}).each(function() {
		var val = $(this).val();
		$('.object-list-type').text(val);
		if (window['_report_data']) {
			expand_and_populate(_report_data);
		} else if (val) {
			get_members(val, function(all_names) {
				populate_options('objects_tmp', 'objects', all_names);
			});
		}
	});
	$('#sel_downtime_type').on('click', function() {
		var value = this.form.downtime_type.value;
		$('.object-list-type').text(value);
		get_members(value, function(all_names) {
			populate_options('objects_tmp', 'objects', all_names);
		});
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
	var fixed = $('#checkbox_fixed').attr('checked');
	var days = $('.recurring_day');
	var months = $('.recurring_month');

	if (comment == '' || start_time == '' || end_time == '' || (!fixed && duration == '')) {
		// required fields are empty
		// _form_err_empty_fields
		err_str += '<li>' + _form_err_empty_fields + '</li>';
	} else {
		// check for special input

		// start_time field
		if (start_time.indexOf(':') != -1) {
			// we have hh:mm
			timeparts = start_time.split(':');
			if (timeparts.length != 2 || isNaN(timeparts[0]) || isNaN(timeparts[1])) {
				// bogus time format
				err_str += '<li>' + sprintf(_form_err_bad_timeformat, _form_field_time) + '</li>';
			}
		}

		// duration field
		if (duration.indexOf(':') != -1) {
			// we have hh:mm
			durationparts = duration.split(':');
			if (durationparts.length != 2 || isNaN(durationparts[0]) || isNaN(durationparts[1])) {
				// bogus time format
				err_str += '<li>' + sprintf(_form_err_bad_timeformat, _form_field_duration) + '</li>';
			}
		}

		if (!fixed) {
			if (!$('#triggered_by').val() || $('#triggered_by').val() == 0) {
				// user selected triggered scheduled downtime but nothing to trigger by
				err_str += '<li>' + _form_err_no_trigger_id + '</li>';
			}
		} else {
			// force triggered by value to 0 when using fixed
			$('#triggered_by').val(0);
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
	return true;
}

/**
*	Receive params as JSON object
*	Parse fields and populate corresponding fields in form
*	with values.
*/
function expand_and_populate(reportObj)
{
	var field_str = reportObj.downtime_type;
	get_members(field_str, function(all_names) {
		var mo = new missing_objects();
		var from = $('#objects_tmp');
		var to = $('#objects');
		populate_options('objects_tmp', 'objects', all_names);
		// select report objects
		for (prop in reportObj.objects) {
			if (!from.containsOption(reportObj.objects[prop])) {
				mo.add(reportObj.objects[prop])
			} else {
				from.selectOptions(reportObj.objects[prop]);
			}
		}
		mo.display_if_any();
		// move selected options from left -> right
		moveAndSort(from, to);
	});
}
