var startDate;
var endDate;
var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;

//var _scheduled_label = '';
var invalid_report_names = '';
var current_filename;
var _show_schedules;

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';
var _schedule_remove = false;

$(document).ready(function() {

	show_state_options($('#assumeinitialstates').attr('checked'));

	$("#saved_report_form").bind('submit', function() {
		return check_and_submit($(this));
	});

	$("#report_id").bind('change', function() {
		if (check_and_submit($("#saved_report_form"))) {
			$("#saved_report_form").trigger('submit');
		}
	});


	// delete single schedule
	$(".delete_schedule").each(function() {
		$(this).click(function() {
			if ($(this).attr('class').indexOf('avail_del') > -1) {
				_schedule_remove = 'avail';
			} else {
				if ($(this).attr('class').indexOf('sla_del') > -1) {
					_schedule_remove = 'sla';
				}
				if ($(this).attr('class').indexOf('summary_del') > -1) {
					_schedule_remove = 'summary';
				}
			}
			if (!_schedule_remove) {
				_schedule_remove = $('input[name=type]').attr('value');
			}
			schedule_delete($(this).attr('id'), _schedule_remove);
		})
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
		return validate_report_form();
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

	// ajax post form options
	var options = {
		target:			'#response',		// target element(s) to be updated with server response
		beforeSubmit:	validate_form,	// pre-submit callback
		success:		show_response	// post-submit callback
	};

	// schedule report when already created
	$('#schedule_report_form').submit(function() {
		$(this).ajaxSubmit(options);
		return false;
	});

	$("#new_schedule_btn").click(function() {$('.schedule_error').hide();})
	$("#show_scheduled").click(function(){toggle_edit()});
	setup_editable();

	$("#switch_report_type").click(function() {
		switch_report_type();
		return false;
	});

	var $tabs = $('#report-tabs').tabs();
	if (_show_schedules) {
		$tabs.tabs('select', 1);
	}

	$("#rep_type").change(function() {
		var rep_type_val = $(this).fieldValue();
		get_saved_reports(rep_type_val[0], true);
	});

	$("#saved_report_id").change(function() {
		create_filename();
	});
	$("#period").change(function() {
		var sel_report = $("#saved_report_id").fieldValue();
		if (sel_report[0] != '')
			create_filename();
	});

	$('.fancybox').click(function() {
		setup_editable('fancy');
		$("#fancybox-content .delete_schedule").each(function() {
			$(this).click(function() {
				schedule_delete($(this).attr('id'));
			});
		});

		$("#fancybox-content .send_report_now").click(function() {
			var type_id = $(this).attr('id');
			type_id = type_id.replace('send_now_', '');
			type_id = type_id.split('_');
			var type = type_id[0];
			var id = type_id[1];
			send_report_now(type, id);
		});
	});

	$('.fancybox').click(function() {
		// set initial states
		if ($('input[name=type]').attr('value') != 'sla') {
			set_initial_state('assumeinitialstates', assumeinitialstates);
			set_initial_state('scheduleddowntimeasuptime', scheduleddowntimeasuptime);
		}
		set_initial_state('cluster_mode', cluster_mode);
	});

	$('#filename').blur(function() {
		// Make sure the filename is explicit by adding it when focus leaves input
		var input = $(this);
		var filename = input.val();
		if(!filename) {
			return;
		}
		if(!filename.match(/.(csv|pdf)$/)) {
			filename += '.pdf';
		}
		input.val(filename);
	});

});

function js_print_date_ranges(the_year, type, item)
{
	show_progress('progress', _wait_str);
	the_year = typeof the_year == 'undefined' ? 0 : the_year;
	type = typeof type == 'undefined' ? '' : type;
	item = typeof item == 'undefined' ? '' : item;

	if (!the_year && type!='' && item!='') {
		return false;
	}
	//get_date_ranges(the_year, type, item);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_date_ranges/";
	var data = {the_year: the_year, type: type, item: item};

	if (type !='') {
		empty_list(type + '_month');
	}

	set_selected_period(type);

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		success: function(data) {
			if (data != '') {
				// OK, continue
				data = eval( "(" + data + ")" );
				if (data['start_year']) {
					for (i in data['start_year']) {
						addSelectOption('start_year', data['start_year'][i], data['start_year'][i]);
					}
				}

				if (data['end_year']) {
					for (i in data['end_year']) {
						addSelectOption('end_year', data['end_year'][i], data['end_year'][i]);
					}
				}

				if (data['type_item']) {
					for (i in data['type_item']) {
						addSelectOption(data['type_item'][i][0], data['type_item'][i][1], data['type_item'][i][1]);
					}
				}

			} else {
				// error
				jgrowl_message('Unable to fetch date ranges...', _reports_error);
			}
		}
	});

	setTimeout('check_custom_months()', 1000);
}

function validate_report_form(f)
{
	var is_ok = check_form_values();
	if (!is_ok) {
		return false;
	}
	var errors = 0;
	var err_str = '';
	var jgrowl_err_str = '';

	var fancy_str = '';
	if ($('#fancybox-content').is(':visible')) {
		fancy_str = '#fancybox-content ';
	}

	// only run this part if report should be saved
	if ($(fancy_str + "#save_report_settings").attr('checked') == true || $('input[name=sla_save]').attr('value') == '1') {
		var report_name = $.trim($('input[name=report_name]').attr('value'));
		if (report_name == '') {
			// fancybox is stupid and copies the form so we have to force
			// this script to check the form in the fancybox_content div
			report_name = $(fancy_str + '#report_name').attr('value');
		}

		// these 2 fields should be the same no matter where on the
		// page they are found
		var saved_report_id = $('input[name=saved_report_id]').attr('value');
		var old_report_name = $.trim($('input[name=old_report_name]').attr('value'));

		if (report_name == '') {
			errors++;
			jgrowl_err_str += _reports_name_empty + "\n";
			err_str += "<li>" + _reports_name_empty + ".</li>";
		}

		// display err_str if any

		if (errors) {
			// clear all style info from progress
			$('#response').attr("style", "");
			$('#response').html("<ul class=\"error\">" + err_str + "</ul>");
			window.scrollTo(0,0); // make sure user sees the error message

			jgrowl_message(jgrowl_err_str, _error_header);
			return false;
		}
	}
	$('#response').html('').hide();
	return true;
}

function trigger_ajax_save(f)
{
	// first we need to make sure we get the correct field information
	// for report_name since fancybox is pretty stupid
	$('input[name=report_name]').attr('value', $('#fancybox-content #report_name').attr('value'));

	// ajax post form options for SLA save generated report
	var sla_options = {
		target:			'#response',		// target element(s) to be updated with server response
		beforeSubmit:	validate_report_form,	// pre-submit callback
		success:		show_sla_saveresponse,	// post-submit callback
		dataType: 'json'
	};
	$('#fancybox-content #report_form_sla').ajaxSubmit(sla_options);
	return false;
}

function show_sla_saveresponse(responseText, statusText)
{
	if (responseText['status'] == 'ok' && statusText == 'success') {
		jgrowl_message(responseText['status_msg'], _success_header);

		// propagate new values to form
		$('input[name=saved_report_id]').attr('value', responseText['report_id']);
		$('input[name=report_id]').attr('value', responseText['report_id']);
		$('input[name=report_name]').attr('value', $('#fancybox-content #report_name').attr('value'));
		$('#scheduled_report_name').text($('#fancybox-content #report_name').attr('value'));
	}
	$('#view_add_schedule').show();
	$('#save_to_schedule').hide();
	$(".fancybox").fancybox.close();
}

function ajax_submit(f)
{
	show_progress('progress', _wait_str);
	// fetch values from form
	var report_id = 0;

	var rep_type = $('#rep_type').fieldValue();
	rep_type = rep_type[0];
	var rep_type_str = $('#rep_type option:selected').val();

	var saved_report_id = $('#saved_report_id').fieldValue()[0];

	var period = $('#period').fieldValue()[0];
	var period_str = $('#period option:selected').text();

	var recipients = $.trim($('#recipients').fieldValue()[0]);

	if (!check_email(recipients)) {
		alert(_reports_invalid_email);
		return false;
	}

	var filename = $('#filename').fieldValue()[0];

	var description = $('#description').fieldValue()[0];

	var report_types = $.parseJSON(_report_types_json);
	for (var i in report_types) {
		if (report_types[i] == rep_type) {
			report_type_id = i;
		}
	}

	if(!validate_form()) {
		setTimeout('delayed_hide_progress()', 1000);
		return false;
	}
	var local_persistent_filepath = $.trim($('#local_persistent_filepath').val());
	$.ajax({
		url:_site_domain + _index_page + '/reports/schedule',
		type: 'POST',
		data: {report_id: report_id, rep_type: rep_type, saved_report_id: saved_report_id, period: period, recipients: recipients, filename: filename, description: description, local_persistent_filepath: local_persistent_filepath},
		success: function(data) {
			if (data.error) {
				jgrowl_message(data.error, _reports_error);
			} else {
				new_schedule_rows(data.result.id, period_str, recipients, filename, description, rep_type_str, report_type_id, local_persistent_filepath);
				jgrowl_message(_reports_schedule_create_ok, _reports_success);
			}
		},
		dataType: 'json'
	});
	setTimeout('delayed_hide_progress()', 1000);
	return false;
}

/**
*	Switch report type without page reload
*/
function switch_report_type()
{
	// new values in report_period (AJAX call)
	// update saved + scheduled reports
	var current_report = $('input[name=type]').val();
	var other_report = current_report == 'avail' ? 'sla' : 'avail';
	if (current_report == 'avail') { // switching to SLA
		other_report = 'sla';
		$('#switch_report_type_txt').text(_label_switch_to + ' ' + _label_avail + ' ' + _label_report);
		$('#enter_sla').show();
		$('#switcher_image').attr('src', _site_domain + _theme_path + 'icons/16x16/availability.png');
		$('#switcher_image').attr('alt', _label_avail);
		$('#switcher_image').attr('title', _label_avail);
		$("#old_avail_link").hide();
		$(".sla_display").show();
		$(".avail_display").hide();

		$('#csv_cell').hide();
		$("#report_type_label").text(_label_sla + ' ' + _label_report);
	} else {
		other_report = 'avail';
		$('#switch_report_type_txt').text(_label_switch_to + ' ' + _label_sla + ' ' + _label_report);
		$('#enter_sla').hide();
		$('#switcher_image').attr('src', _site_domain + _theme_path + 'icons/16x16/sla.png');
		$('#switcher_image').attr('alt', _label_sla);
		$('#switcher_image').attr('title', _label_sla);
		$("#old_avail_link").show();
		$(".sla_display").hide();
		$(".avail_display").show();

		$('#csv_cell').show();
		$("#report_type_label").text(_label_avail + ' ' + _label_report);
	}
	$('input[name=type]').val(other_report);
	$("#single_schedules").remove();
	$("#display").hide();
	get_report_periods(other_report);
	get_saved_reports(other_report);

	// reset saved_report_id
	$('input[name=saved_report_id]').val(0);
	$('input[name=report_name]').val('');
}

function get_saved_reports(type, schedules)
{
	show_progress('progress', _wait_str);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_saved_reports/";
	var data = {type: type};
	var field = false;

	field = schedules == true ? 'saved_report_id' : 'report_id';
	empty_list(field);

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		success: function(data) {
			if (data != '') {
				// OK, populate
				populate_saved_reports(data, field);
				$('#saved_reports_display').show();
				$('.sla_values').show();
			} else {
				// error
				// suppressed since this is not always an error - they maybe doesn't exist yet
				//jgrowl_message('Unable to fetch saved reports...', _reports_error);
				$('#saved_reports_display').hide();
				$('.sla_values').hide();
			}
		}
	});

}

function create_filename()
{
	if (!$('#saved_report_id option:selected').val()) {
		$('input[name=filename]').val('');
		return false;
	}
	var new_filename = $('#saved_report_id option:selected').text();
	new_filename = remove_scheduled_str(new_filename);
	new_filename += '_' + $('#period option:selected').text() + '.pdf';
	new_filename = new_filename.replace(/ /g, '_');
	if ($('input[name=filename]').val() != '' && $('input[name=filename]').val() != current_filename) {
		if (!confirm(_schedule_change_filename)) {
			return false;
		}
	}
	$('input[name=filename]').val(new_filename);
	current_filename = new_filename;
	return true;
}

function remove_scheduled_str(in_str)
{
	in_str = in_str.replace(/\*/g, '');
	in_str = in_str.replace(" ( " + _scheduled_label + " )", '');
	return in_str;
}

function set_selection(val, no_erase) {
	// start by hiding ALL rows
	hide_these = Array('hostgroup_row', 'servicegroup_row', 'host_row_2', 'service_row_2', 'settings_table', 'submit_button', 'enter_sla','display_service_status','display_host_status');
	hide_rows(hide_these);
	switch (val) {
		case 'hostgroups':
			get_members('', 'hostgroup', no_erase);
			show_row('hostgroup_row');
			show_row('display_host_status');
			break;
		case 'servicegroups':
			get_members('', 'servicegroup', no_erase);
			show_row('servicegroup_row');
			show_row('display_service_status');
			break;
		case 'hosts':
			get_members('', 'host', no_erase);
			show_row('host_row_2');
			show_row('display_host_status');
			break;
		case 'services':
			get_members('', 'service', no_erase);
			show_row('service_row_2');
			show_row('display_service_status');
			break;
	}
	show_row('settings_table');
	if ($('input[name=type]').val() == 'sla')
		show_row('enter_sla');
	show_row('submit_button');
}

/**
*	Uncheck form element by name
*	Used to set correct initial values
*	since some browser seem to cache checkbox state
*/
function uncheck(the_name, form_name)
{
	//document.forms[form_name].elements[the_name].checked=false;
	$("input[name='" + the_name + "']").attr('checked', false);
}

function hide_rows(input) {
	for (i=0;i<input.length;i++) {
		if (document.getElementById(input[i]))
			document.getElementById(input[i]).style.display='none';
	}
}

/**
*	cache the progress indicator image to show faster...
*/
var Image1 = new Image(16,16);
Image1.src = _site_domain + '/application/media/images/loading.gif';

/**
*	Show a progress indicator to inform user that something
*	is happening...
*/
function show_progress(the_id, info_str)
{
	$("#" + the_id)
		.html('<img id="progress_image_id" src="' + Image1.src + '"> <em>' + info_str +'</em>')
		.show();
}

function get_members(val, type, no_erase) {
	if (type=='') return;
	is_populated = false;
	show_progress('progress', _wait_str);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "group_member/";
	var data = {input: val, type: type}
	var field_name = false;
	var empty_field = false;

	switch(type) {
		case 'hostgroup': case 'servicegroup':
			field_name = type + "_tmp";
			empty_field = type;
			break;
			case 'host':
				field_name = "host_tmp";
				empty_field = 'host_name';
				break;
			case 'service':
				field_name = "service_tmp";
				empty_field = 'service_description';
				break;
	}

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		success: function(data) {
			if (data.error) {
				jgrowl_message('Unable to fetch objects: ' + data.error, _reports_error);
				setup_hide_content('progress');
				return;
			}
			populate_options(field_name, empty_field, data.result);
			if(no_erase == '') {
				empty_list(field_name);
				empty_list(empty_field);
			}
		},
		dataType: 'json'
	});


	sel_str = type;
	$('#settings_table').show();
	$('#submit_button').show();
}

/**
*	Fetch the report periods for selected report type.
*
*	Result will be returned to populate_report_periods() below.
*/
function get_report_periods(type)
{
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_report_periods/";
	var data = {type: type};
	empty_list('report_period');
	set_selected_period(type);

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		success: function(data) {
			if (data != '') {
				// OK, populate
				populate_report_periods(data);
			} else {
				// error
				jgrowl_message('Unable to fetch report periods...', _reports_error);
			}
		}
	});
}

function show_row(the_id) {
	$("#"+the_id).show();
}

function empty_list(field) {
	// escape nasty [ and ]
	field = field.replace('[', '\\[');
	field = field.replace(']', '\\]');

	// truncate select list
	$("#"+field).removeOption(/./);
}

/**
*	Populate HTML select list with supplied JSON data
*/
function populate_options(tmp_field, field, json_data)
{
	json_data = eval(json_data);
	show_progress('progress', _wait_str);
	for (var i = 0; i < json_data.length; i++) {
		var val = json_data[i].optionValue;
		addSelectOption(tmp_field, val);
	}
	is_populated = true;
	setTimeout('delayed_hide_progress()', 1000);
}

/**
*	Re-populate report_period select field
*/
function populate_report_periods(json_data)
{
	json_data = eval(json_data);
	var field_name = 'report_period';
	for (var i = 0; i < json_data.length; i++) {
		var val = json_data[i].optionValue;
		var txt = json_data[i].optionText;
		$("#" + field_name).addOption(val, txt, false);
	}
	disable_sla_fields($('#report_period option:selected').val());
	setTimeout('delayed_hide_progress()', 1000);
}

/**
*	Re-populate report_id (saved reports) select field
*/
function populate_saved_reports(json_data, field_name)
{
	json_data = eval(json_data);
	invalid_report_names = new Array();
	for (var i = 0; i < json_data.length; i++) {
		var val = json_data[i].optionValue;
		var txt = json_data[i].optionText;
		$("#" + field_name).addOption(val, txt, false);
		$('.sla_values').show();
		$('#sla_report_id').addOption(val, txt, false);
		invalid_report_names[i] = txt;
	}
	setTimeout('delayed_hide_progress()', 1000);
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
	setTimeout('delayed_hide_progress()', 1000);
}

/**
*	Set selected report period to default
*	(and disable sla fields out of scope if sla)
*/
function set_selected_period(val)
{
	$("#report_period").selectOptions(val);
	disable_sla_fields(val);
}

// delay hiding of progress indicator
function delayed_hide_progress()
{
	setup_hide_content('progress');
}

function addSelectOption(theSel, theVal)
{
	theSel = theSel.replace('[', '\\[');
	theSel = theSel.replace(']', '\\]');
	$("#"+theSel).addOption(theVal, theVal, false);
}

function setup_hide_content(d) {
	if(d.length < 1) {
		return;
	}
	$('#' + d).hide();
}

function hide_response() {setup_hide_content('response');}

function show_state_options(val)
{
	if (val) {
		show_row('assumed_host_state');
		show_row('assumed_service_state');
	} else {
		hide_these = new Array('assumed_host_state', 'assumed_service_state');
		hide_rows(hide_these);
	}
}
function edit_state_options(val)
{
	var options = $('#state_options');
	if(options == undefined)
		return;

	if (val) {
		$('#fancybox-content #state_options').show();
	} else {
		$('#fancybox-content #state_options').hide();
	}
}

function toggle_field_visibility(val, theId) {
	var fancy_str = '';

	if ($('#fancybox-content').is(':visible')) {
		fancy_str = '#fancybox-content ';
	}

	if (val) {
		$(fancy_str + '#' + theId).show();
	} else {
		$(fancy_str + '#' + theId).hide();
	}
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
	var field_str = reportObj['report_type'];
	if (reportObj['objects']) {
		for (prop in reportObj['objects']) {
			add_if_not_exist(field_obj.map[field_str], reportObj['objects'][prop]);
		}
	}
	set_selection(reportObj['report_type'], 'true');
	set_initial_state('assumeinitialstates', reportObj['assumeinitialstates']);
	set_initial_state('host', reportObj['initialassumedhoststate']);
	set_initial_state('service', reportObj['initialassumedservicestate']);
	set_initial_state('scheduleddowntimeasuptime', reportObj['scheduleddowntimeasuptime']);
	set_initial_state('report_type', reportObj['report_type']);
	set_initial_state('report_period', reportObj['report_period']);
	show_calendar(reportObj['report_period']);
	set_initial_state('rpttimeperiod', reportObj['rpttimeperiod']);
	if (reportObj['report_name'] != undefined) {
		set_initial_state('report_name', reportObj['report_name']);
	} else {
		set_initial_state('report_name', reportObj['sla_name']);
		show_row('enter_sla');
	}
	set_initial_state('includesoftstates', reportObj['includesoftstates']);
	if (reportObj['report_period'] == 'custom') {
		if ($('input[name=type]').attr('value') == 'sla') {
			js_print_date_ranges(reportObj['start_time'], 'start', 'month');
			js_print_date_ranges(reportObj['end_time'], 'end', 'month');

			setTimeout('set_initial_state("report_period-start", ' + reportObj['start_year'] + ')', 2000);
			setTimeout('set_initial_state("report_period-startmonth", ' + reportObj['start_month'] + ')', 2000);
			setTimeout('set_initial_state("report_period-end", ' + reportObj['end_year'] + ')', 2000);
			setTimeout('set_initial_state("report_period-endmonth", ' + reportObj['end_month'] + ')', 2000);
		} else {
			startDate = epoch_to_human(reportObj['start_time']);
			//$('#cal_start').text(format_date_str(startDate));
			document.forms['report_form'].start_time.value = format_date_str(startDate);
			endDate = epoch_to_human(reportObj['end_time']);
			//$('#cal_end').text(format_date_str(endDate));
			document.forms['report_form'].end_time.value = format_date_str(endDate);
		}
	}
	current_obj_type = field_str;

	// wait for lists to populate
	setTimeout("remove_duplicates();", 500);
}

function set_initial_state(what, val)
{
	var rep_type = $('input[name=type]').attr('value');
	if (document.forms['report_form_sla'] != undefined) {
		f = document.forms['report_form_sla'];
	} else {
		f = document.forms['report_form'];
	}
	var item = '';
	var elem = false;
	switch (what) {
		case 'host':
			item = 'initialassumedhoststate';
			break;
		case 'service':
			item = 'initialassumedservicestate';
			break;
		case 'includesoftstates':
			if (val!='0') {
				toggle_label_weight(1, 'include_softstates');
				f.elements['includesoftstates'].checked = true;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				toggle_label_weight(0, 'include_softstates');
				f.elements['includesoftstates'].checked = false;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
			}
			break;
		case 'assumeinitialstates':
			if (val!='0') {
				edit_state_options(1);
				toggle_label_weight(1, 'assume_initial');
				//f.elements['assumeinitialstates'].checked = true;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				//f.elements['assumeinitialstates'].checked = false;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
				edit_state_options(0);
				toggle_label_weight(0, 'assume_initial');
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
			f.elements['report_name'].value = val;
			break;
		case 'rpttimeperiod':
			item = 'rpttimeperiod';
			break;
		case 'report_period-start':
			item = 'start_year';
			if ($('select[name=' + item + '] option').length < 2) {
				setTimeout('set_initial_state("' + what + '", ' + val + ')', 1000);
			}
			break;
		case 'report_period-startmonth':
			item = 'start_month';
			if ($('select[name=' + item + '] option').length < 2) {
				if (val < 10) val = '0' + val;
				setTimeout('set_initial_state("' + what + '", ' + val + ')', 1000);
			}
			break;
		case 'report_period-end':
			item = 'end_year';
			if ($('select[name=' + item + '] option').length < 2) {
				setTimeout('set_initial_state("' + what + '", ' + val + ')', 1000);
			}
			break;
		case 'report_period-endmonth':
			item = 'end_month';
			if ($('select[name=' + item + '] option').length < 2) {
				if (val < 10) val = '0' + val;
				setTimeout('set_initial_state("' + what + '", ' + val + ')', 1000);
			}
			break;
		default:
			item = what;
	}
	if (item) {
		elem = f.elements[item];
		for (i=0;i<elem.length;i++) {
			if (elem.options[i].value==val) {
				elem.options[i].selected = true;
			}
		}
	}
}


function confirm_delete_report(the_val)
{
	var the_path = self.location.href;
	the_path = the_path.replace('#', '');

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	var type = $('input[name=type]').attr('value');
	if (the_val!="" && the_path!="") {
		if (is_scheduled) {
			msg += _reports_confirm_delete_warning;
		}
		if (confirm(msg)) {
			self.location.href=the_path + '?del_report=true&del_id=' + the_val + '&type=' + type;
			return true;
		}
	}
	return false;
}



function show_response(responseText, statusText)
{
	var message_div_start = '<div id="statusmsg">';
	var ok_img = '<img src="' + _site_domain + _theme_path + 'icons/16x16/shield-ok.png" alt="' + _ok_str + '" title="' + _ok_str + '"> &nbsp;'
	var message_div_end = '</div>';
	var time = 3000;
	$('#response').remove();
	$('body').prepend('<div id="response"></div>');
	if (isNaN(responseText)) { // an error occurred
		$('#response').css("position", "absolute").css('top', '11px').css('left', '200px').html(message_div_start + _reports_schedule_error + message_div_end);
		time = 6000;
	} else {
		var report_id = $('#report_id').fieldValue();
		if (report_id[0]) { // updated
			jgrowl_message(_reports_schedule_update_ok, _reports_success);
		} else { //new save
			jgrowl_message(_reports_schedule_create_ok, _reports_success);
			$('#schedule_report_table').append(create_new_schedule_rows(responseText));
			$('#schedule_report_table').show();
			schedule_is_visible = true;
			$('#schedule_report_form').clearForm();
			setup_editable();
			//tb_remove();
			update_visible_schedules(true);
			if (nr_of_scheduled_instances > 0) {
				$('#show_schedule').show();
			}
		}
	}
	setTimeout('hide_response()', time);
}


// used from setup
function new_schedule_rows(id, period_str, recipients, filename, description, rep_type_str, report_type_id, local_persistent_filepath)
{
	var return_str = '';
	var reportname = $("#saved_report_id option:selected").text();
	reportname = remove_scheduled_str(reportname);
	return_str += '<tr id="report-' + id + '" class="odd">';
	return_str += '<td class="period_select" title="' + _reports_edit_information + '" id="period_id-' + id + '">' + period_str + '</td>';
	return_str += '<td class="report_name" id="' + report_type_id + '.report_id-' + id + '">' + reportname + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="recipients-' + id + '">' + recipients + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="filename-' + id + '">' + filename + '</td>';
	return_str += '<td class="iseditable_txtarea" title="' + _reports_edit_information + '" id="description-' + id + '">' + description + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="local_persistent_filepath-' + id + '">' + local_persistent_filepath + '</td>';
	return_str += '<td><form><input type="button" class="send_report_now" id="send_now_' + rep_type_str + '_' + id + '" title="' + _reports_send_now + '" value="&nbsp;" onclick="send_report_now(\'' + rep_type_str + '\', ' + id + ')"></form>';
	return_str += '<div class="delete_schedule ' + rep_type_str + '_del" onclick="schedule_delete(' + id + ', \'' + rep_type_str + '\');" id="delid_' + id + '"><img src="' + _site_domain + _theme_path + 'icons/16x16/delete-schedule.png" class="deleteimg" title="Delete scheduled report" /></td></tr>';
	$('#' + rep_type_str + '_scheduled_reports_table').append(return_str);
	setup_editable();
	$('#new_schedule_report_form').clearForm();
	setTimeout('delayed_hide_progress()', 1000);
	update_visible_schedules(false);
	//nr_of_scheduled_instances++;

	// make sure we hide message about no schedules and show table headers
	$('#' + rep_type_str + '_no_result').hide();
	$('#' + rep_type_str + '_headers').show();
	return true;
}

var is_visible = false;
function toggle_edit() {
	var $tabs = $('#report-tabs').tabs();
	$tabs.tabs('select', 1);
}

function fetch_report_data(id)
{
	parts = id.split('-');
	type_id = get_type_id(parts[0]);
	var sType = '';

	var report_types = $.parseJSON(_report_types_json);
	sType = report_types[type_id];
	switch (sType) {
		case 'avail':
		//var data = eval('(' + $('#saved_reports').text() + ')');
			return eval(_saved_avail_reports);
			break;
		case 'sla':
			return eval(_saved_sla_reports);
			break;
		case 'summary':
			return eval(_saved_summary_reports);
			break;
		default:
			return false;
	}
}

function get_type_id(str)
{
	parts = str.split('.');
	return parts[0];
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

function check_email(mail_str)
{
	var emailRegex= new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i );
	var mail_list = mail_str.split(',');
	var result = false;
	if (mail_list.length > 1) {
		for (var i=0;i<mail_list.length;i++) {
			if ($.trim(mail_list[i]) != '') {
				var m = emailRegex.exec($.trim(mail_list[i]));
				if (!m) {
					return false;
				} else {
					result = true;
				}
			}
		}
	} else {
		mail_str = $.trim(mail_str);
		var m = emailRegex.exec(mail_str);
		if (!m) {
			result = false;
		} else {
			result = true;
		}
	}
	return result;
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
			if (data != '') {
				// OK, populate
				populate_saved_sla_data(data);
				$('.sla_values').show();
			} else {
				// error
				jgrowl_message('Unable to fetch saved sla values...', _reports_error);
			}
		}
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
