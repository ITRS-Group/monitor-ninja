// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';
var _schedule_remove = false;

$(document).ready(function() {

	$("#saved_report_form").bind('submit', function() {
		return check_and_submit($(this));
	});

	// reset options and reload page
	$('#new_report').click(function() {
		var current_report = $('input[name=type]').val();
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		var uri_xtra = current_report == 'avail' ? '' : '?type=sla';
		self.location.href = base_uri + uri_xtra;
	});

	disable_sla_fields($('#report_period').attr('value'));

	$("#report_form").bind('submit', function() {
		loopElements();
		return check_form_values();
	});

	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});
	show_calendar($("#report_period").attr('value'));

	$('.autofill').click(function() {
		var the_val = $("input[name='" + $(this).attr('id') + "']").attr('value');
		if (the_val!='') {
			if (!confirm(_reports_propagate.replace('this value', the_val+'%'))) {
				return false;
			}
			set_report_form_values(the_val);
		} else {
			if (!confirm(_reports_propagate_remove)) {
				return false;
			}
			set_report_form_values('');
		}
	});

	$("#new_schedule_btn").click(function() {$('.schedule_error').hide();})
	setup_editable();

	$('.fancybox').click(function() {
		setup_editable('fancy');
		// set initial states
	});

	$('#filename').blur(function() {
		// Make sure the filename is explicit by adding it when focus leaves input
		var input = $(this);
		var filename = input.val();
		if(!filename) {
			return;
		}
		if(!filename.match(/\.(csv|pdf)$/)) {
			filename += '.pdf';
		}
		input.val(filename);
	});

	// delete the report (and all the schedules if any)
	$("#delete_report").click(function() {
		confirm_delete_report(this, $("#report_id").attr('value'));
	});
});

function trigger_ajax_save(f)
{
	// first we need to make sure we get the correct field information
	// for report_name since fancybox is pretty stupid
	$('input[name=report_name]').attr('value', $('#fancybox-content #report_name').attr('value'));

	// ajax post form options for SLA save generated report
	var sla_options = {
		target:			'#response',		// target element(s) to be updated with server response
		beforeSubmit:	check_form_values,	// pre-submit callback
		success:		show_sla_saveresponse,	// post-submit callback
		dataType: 'json'
	};
	$('#fancybox-content #report_form_sla').ajaxSubmit(sla_options);
	return false;
}

function show_sla_saveresponse(responseText, statusText)
{
	if (responseText.status == 'ok' && statusText == 'success') {
		jgrowl_message(responseText.status_msg, _success_header);

		// propagate new values to form
		$('input[name=saved_report_id]').attr('value', responseText.report_id);
		$('input[name=report_id]').attr('value', responseText.report_id);
		$('input[name=report_name]').attr('value', $('#fancybox-content #report_name').attr('value'));
		$('#scheduled_report_name').text($('#fancybox-content #report_name').attr('value'));
	}
	$('#view_add_schedule').show();
	$('#save_to_schedule').hide();
	$.fancybox.close();
}

function populate_saved_sla_data(json_data) {
	json_data = eval(json_data);
	for (var i = 1; i <= 12; i++) {
		$("#sla_month_"+i).attr('value','');
	}
	for (var i = 0; i < json_data.length; i++) {
		var j = i+1;
		var name = json_data[i].name;
		var value = json_data[i].value;
		if (document.getElementById("sla_"+name).style.backgroundColor != 'rgb(205, 205, 205)')
			$("#sla_"+name).attr('value',value);
	}
	setTimeout(delayed_hide_progress, 1000);
}

// Propagate sla values
function set_report_form_values(the_val)
{
	for (i=1;i<=12;i++) {
		var field_name = 'month_' + i;
		if ($("input[name='" + field_name + "']").attr('disabled')) {
			$("input[name='" + field_name + "']").attr('value', '');
		} else {
			$("input[name='" + field_name + "']").attr('value', the_val);
		}
	}
}

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
	var field_str = reportObj.report_type;
	set_selection(reportObj.report_type, function() {
		var mo = new missing_objects();
		if (reportObj.objects) {
			for (var prop in reportObj.objects) {
				if (!$('#'+tmp_fields.map[field_str]).containsOption(reportObj.objects[prop])) {
					mo.add(reportObj.objects[prop]);
				} else {
					$('#'+tmp_fields.map[field_str]).selectOptions(reportObj.objects[prop]);
				}
			}
			mo.display_if_any();
			moveAndSort(tmp_fields.map[field_str], field_obj.map[field_str]);
		}
	});
	set_initial_state('scheduleddowntimeasuptime', reportObj.scheduleddowntimeasuptime);
	set_initial_state('report_type', reportObj.report_type);
	set_initial_state('report_period', reportObj.report_period);
	show_calendar(reportObj.report_period);
	set_initial_state('rpttimeperiod', reportObj.rpttimeperiod);
	if (reportObj.report_name != undefined) {
		set_initial_state('report_name', reportObj.report_name);
	}
	set_initial_state('includesoftstates', reportObj.includesoftstates);
	if (reportObj.report_period == 'custom') {
		if ($('input[name=type]').attr('value') == 'sla') {
			js_print_date_ranges(reportObj.start_time, 'start', 'month');
			js_print_date_ranges(reportObj.end_time, 'end', 'month');
		} else {
			startDate = epoch_to_human(reportObj.start_time);
			//$('#cal_start').text(format_date_str(startDate));
			document.forms.report_form.start_time.value = format_date_str(startDate);
			endDate = epoch_to_human(reportObj.end_time);
			//$('#cal_end').text(format_date_str(endDate));
			document.forms.report_form.end_time.value = format_date_str(endDate);
		}
	}
	current_obj_type = field_str;
}

function set_initial_state(what, val)
{
	var rep_type = $('input[name=type]').attr('value');
	f = $('#report_form').get(0);
	var item = '';
	var elem = false;
	switch (what) {
		case 'includesoftstates':
			if (val!='0') {
				toggle_label_weight(1, 'include_softstates');
				f.elements.includesoftstates.checked = true;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				toggle_label_weight(0, 'include_softstates');
				f.elements.includesoftstates.checked = false;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
			}
			break;
		case 'cluster_mode':
			if (val!='0') {
				toggle_label_weight(1, 'cluster_mode');
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				toggle_label_weight(0, 'cluster_mode');
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
			}
			break;
		case 'report_name':
			f.elements.report_name.value = val;
			break;
		case 'rpttimeperiod':
			item = 'rpttimeperiod';
			break;
		default:
			item = what;
	}
	if (item) {
		elem = f[item];
		if (elem) {
			for (i=0;i<elem.length;i++) {
				if (elem.options[i].value==val) {
					elem.options[i].selected = true;
				}
			}
		}
	}
}

/**
*	create ajax call to reports/fetch_field_value
*	to fetch a specific field value and asssign it to html element.
*/
function fetch_field_value(type, id, elem_id)
{
	$.ajax({
		url: _site_domain + _index_page + '/reports/fetch_field_value?id=' + id + '&type=' + type,
		success: function(data) {
			$('#' + elem_id).text(data);
			$('#fancybox-content #' + elem_id).text(data);
		}
	});
}

function get_sla_values() {
	var sla_id = $('#sla_report_id').attr('value');

	if (!sla_id) {
		// don't try to fetch sla values when we have no id
		return;
	}
	show_progress('progress', _wait_str);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_sla_from_saved_reports/";
	var data = {sla_id: sla_id}

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		success: function(data) {
			if (data.error) {
				jgrowl_message('Unable to fetch saved sla values...', _reports_error);
			} else {
				populate_saved_sla_data(data.result);
				$('.sla_values').show();
			}
		},
		dataType: 'json'
	});
}

function toggle_state(the_id)
{
	var fancy_str = '';

	if ($('#fancybox-content').is(':visible')) {
		fancy_str = '#fancybox-content ';
	}

	if ($(fancy_str + '#' + the_id).attr('checked') ) {
		$(fancy_str + '#' + the_id).attr('checked', false);
	} else {
		$(fancy_str + '#' + the_id).attr('checked', true);
	}
}

function confirm_delete_report(btn, id)
{
	var btn = $(btn);

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	var type = $('input[name=type]').attr('value');
	if (!id)
		return;
	if (is_scheduled) {
		msg += _reports_confirm_delete_warning;
	}
	msg = msg.replace("this saved report", "the saved report '"+$('#report_id option[selected=selected]').text()+"'");
	if (confirm(msg)) {
		btn.after(loadimg);
		$.ajax({
			url: _site_domain + _index_page + '/' + _controller_name + '/delete/',
			type: 'POST',
			data: {'id': id},
			success: function(data) {
				if (!data.error) {
					jgrowl_message(data.result, _reports_success);
					var input = $('#report_id');
					$(':selected', input).remove();
					$('[value=\'\']', input).selected();
				} else {
					jgrowl_message(data.error, _reports_error);
				}
				btn.parent().find('img:last').remove();
			},
			error: function(data) {
				jgrowl_message(_reports_error, _reports_error);
				btn.parent().find('img:last').remove();
			},
			dataType: 'json'
		});
		return true;
	}
	return false;
}
