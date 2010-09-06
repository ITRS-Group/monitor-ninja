var startDate;
var endDate;
var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;
var current_obj_type = false; // keep track of what we are viewing
var is_populated = false; // flag list population when done

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

$(document).ready(function() {
	set_report_mode('standard');

	// handle standard/custom report switching
	$("#td_std").click(function() {
		$("#report_mode_standard").attr('checked', true);
		set_report_mode('standard');
	});
	$("#td_cust").click(function() {
		$("#report_mode_custom").attr('checked', true);
		set_report_mode('custom');
	});
	$("#report_mode_standard").click(function() {
		set_report_mode('standard');
	});
	$("#report_mode_custom").click(function() {
		set_report_mode('custom');
	});

	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});

	$("#summary_form").bind('submit', function() {
		loopElements();
		return check_form_values();
	});

	$("#saved_report_form").bind('submit', function() {
		return check_and_submit($(this));
	});

	$("#report_id").bind('change', function() {
		if (check_and_submit($("#saved_report_form"))) {
			$("#saved_report_form").trigger('submit');
		}
	});
	// reset options and reload page
	$('#new_report').click(function() {
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		self.location.href = base_uri;
	});

	// delete the report (and all the schedules if any)
	$("#delete_report").click(function() {
		confirm_delete_report($("#report_id").attr('value'));
	});

	$("#show_scheduled").click(function() {
		self.location.href = _site_domain + _index_page + '/reports?show_schedules#summary_schedules';
	});
/*
	$("#fancy_content .send_report_now").live('click', function() {
		var type_id = $(this).attr('id');
		type_id = type_id.replace('send_now_', '');
		type_id = type_id.split('_');
		var type = type_id[0];
		var id = type_id[1];
		send_report_now(type, id);
	});
	*/
	$('.fancybox').click(function() {
		setup_editable('fancy');
	});
});


function confirm_delete_report(the_val)
{
	var the_path = self.location.href;
	the_path = the_path.replace('#', '');

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	if (the_path!="") {
		if (is_scheduled) {
			msg += _reports_confirm_delete_warning;
		}
		if (confirm(msg)) {
			self.location.href=the_path + '?del_report=true&del_id=' + the_val;
			return true;
		}
	}
	return false;
}
/**
*	Receive params as JSON object
*	Parse fields and populate corresponding fields in form
*	with values.
*/
function expand_and_populate(data)
{
	set_initial_state('report_type', data['obj_type']);
	if (!is_populated) {
		setTimeout(function() {expand_and_populate(data);}, 1000);
		return;
	}
	var reportObj = data;
	var field_obj = new field_maps();
	var tmp_fields = new field_maps3();
	var field_str = reportObj['obj_type'];
	if (reportObj['objects']) {
		var to_id = field_obj.map[field_str];
		var from_id = tmp_fields.map[field_str];
		// select report objects
		for (prop in reportObj['objects']) {
			$('#' + from_id).selectOptions(reportObj['objects'][prop]);
		}
		// move selected options from left -> right
		moveAndSort(from_id, to_id);
	}

	set_initial_state('report_type', reportObj['report_type']);
	show_calendar(reportObj['report_period']);

	if (reportObj['report_name'] != undefined) {
		set_initial_state('report_name', reportObj['report_name']);
	}

	if (reportObj['report_period'] == 'custom') {
		startDate = epoch_to_human(reportObj['start_time']);
		document.forms['summary_form'].cal_start.value = reportObj['cal_start'];
		document.forms['summary_form'].time_start.value = reportObj['time_start'];
		document.forms['summary_form'].start_time.value = format_date_str(startDate);

		endDate = epoch_to_human(reportObj['end_time']);
		document.forms['summary_form'].cal_end.value = reportObj['cal_end'];
		document.forms['summary_form'].time_end.value = reportObj['time_end'];
		document.forms['summary_form'].end_time.value = format_date_str(endDate);
	}

	// wait for lists to populate
	setTimeout("remove_duplicates();", 500);
}

function set_report_mode(type)
{
	switch (type) {
		case 'standard':
			$("#std_report_table").show();
			$("#custom_report").hide();
			$(this).parent().css('font-weight', 'bold');
			$("#td_std").css('font-weight', 'bold');
			$("#td_cust").css('font-weight', 'normal');
			break;
		case 'custom':
			$("#std_report_table").hide();
			if (!is_populated && !report_id)
				set_selection($('#report_type').val());
			$("#custom_report").show();
			$('#td_cust').css('font-weight', 'bold');
			$("#td_std").css('font-weight', 'normal');
			break;
	}
}

function set_initial_state(what, val)
{
	var item = '';
	var elem = false;
	switch (what) {
		case 'obj_type':
			item = 'report_type';
			break;
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
