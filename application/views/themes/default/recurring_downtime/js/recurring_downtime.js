
$(document).ready(function() {
	$("#setup_form").bind('submit', function() {
		loopElements();
		return check_setup();
	});

	var $tabs = $('#schedule-tabs-container').tabs();

	$('.recurring_delete').click(function() {
		var this_id = $(this).attr('id');
		this_id = this_id.replace('recurring_delete_', '');
		var type = $('#schedule_' + this_id).attr('class');
		type = type.replace('scheduled_', '')

		// find nr of rows
		var nr_of_schedules = $('#table_' + type + ' tr').length - 2;

		if (confirm(_confirm_delete_schedule)) {
			alert('should delete id:' + this_id);
			$.ajax({
				url:_site_domain + _index_page + '/recurring_downtime/delete',
				type: 'POST',
				data: {schedule_id: this_id},
				success: function(data) {
					if (data == 'ERROR') {
						jgrowl_message(_schedule_error, data);
					} else {
						jgrowl_message(_schedule_delete_success, _schedule_delete_ok);
						$('#schedule_' + this_id).remove();
						if (nr_of_schedules == 1) {
							$('#table_' + type).remove();
						}
					}
				}
			});
		} else {
			alert('will not do anything with id: ' + this_id);
		}
		return false;
	});

	$('.show_all_subobjects').css('cursor', 'pointer');

	$('.show_all_subobjects').click(function() {
		var the_id = $(this).attr('id');
		the_id = the_id.replace('show_all_objects_', '');
		$('#objects_small_' + the_id).toggle();
		$('#all_objects_' + the_id).toggle();
	});
});

function check_setup()
{
	if (!check_form_values()) {
		return false;
	}

	var err_str = '';

	/*
		Fields to check (required):
			comment 	-
			time		- hh:mm
			duration	- hh:mm
	*/
	var comment = $.trim($('textarea[name=comment]').val());
	var time = $.trim($('input[name=time]').val());
	var duration = $.trim($('input[name=duration]').val());

	if (comment == '' || time == '' || duration == '') {
		// required fields are empty
		// _form_err_empty_fields
		err_str += '<li>' + _form_err_empty_fields + '</li>';
	} else {
		// check for special input

		// time field
		if (time.indexOf(':') != -1) {
			// we have hh:mm
			timeparts = time.split(':');
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
function expand_and_populate(data)
{
	if (!is_populated) {
		setTimeout(function() {expand_and_populate(data);}, 1000);
		return;
	}
	var reportObj = data;
	var field_obj = new field_maps();
	var tmp_fields = new field_maps3();
	var field_str = reportObj['report_type'];
	if (reportObj[field_obj.map[field_str]]) {
		var to_id = field_obj.map[field_str];
		var from_id = tmp_fields.map[field_str];
		// select report objects
		for (prop in reportObj[field_obj.map[field_str]]) {
			$('#' + from_id).selectOptions(reportObj[field_obj.map[field_str]][prop]);
			console.log(reportObj[field_obj.map[field_str]][prop]);
		}
		// move selected options from left -> right
		moveAndSort(from_id, to_id);
	}

	// wait for lists to populate
	setTimeout("remove_duplicates();", 500);
}

function set_initial_state(what, val)
{
	var item = '';
	var elem = false;
	switch (what) {
		case '':
			item = '';
			break;
		default:
			item = what;
	}
	if (item) {
		// don't use name field - use ID!
		if ($('#' + item).is(':visible')) {
			$('#' + item + ' option').each(function() {
				if ($(this).val() == val) {
					$(this).attr('selected', true);
				}
			});
		}
	}
}
