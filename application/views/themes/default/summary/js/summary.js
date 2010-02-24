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

});

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
			if (!is_populated)
				set_selection($('#report_type').val());
			$("#custom_report").show();
			$('#td_cust').css('font-weight', 'bold');
			$("#td_std").css('font-weight', 'normal');
			break;
	}
}
