var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;
var current_obj_type = false; // keep track of what we are viewing

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

$(document).ready(function() {
	$('#report_mode_form input').on('change', function() {
		set_report_mode(this.value);
	});
	set_report_mode($('#report_mode_form input:checked').val());

	$("#report_period").bind('change', function() {
		show_calendar($(this).val());
	});
	show_calendar($('#report_period').val());

	$(".to_check").bind('submit', function() {
		loopElements();
		return check_form_values(this.form);
	});

	$("#saved_report_form").bind('submit', function() {
		return check_and_submit($(this));
	});

	// reset options and reload page
	$('#new_report').click(function() {
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		self.location.href = base_uri;
	});
});

/**
*	Receive params as JSON object
*	Parse fields and populate corresponding fields in form
*	with values.
*/
function expand_and_populate(data)
{
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
}

function set_report_mode(type)
{
	switch (type) {
		case 'standard':
			$('.standard').show();
			$('.custom').hide();
			$.fancybox.resize();
			break;
		case 'custom':
			$('.standard').hide();
			$('.custom').show();
			if (!report_id)
				set_selection($('#report_type').val());
			$('#standardreport').val(''); // FIXME: this is broken
			$.fancybox.center();
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
