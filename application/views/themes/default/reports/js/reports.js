var startDate;
var endDate;
var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;
var current_obj_type = false; // keep track of what we are viewing
var is_populated = false; // flag list population when done

//var _scheduled_label = '';
var invalid_report_names = '';
var current_filename;
var _show_schedules;

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

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

	$(".send_report_now").click(function() {
		var type_id = $(this).attr('id');
		type_id = type_id.replace('send_now_', '');
		type_id = type_id.split('_');
		var type = type_id[0];
		var id = type_id[1];
		send_report_now(type, id);
	});

	// delete the report (and all the schedules if any)
	$("#delete_report").click(function() {
		confirm_delete_report($("#report_id").attr('value'));
	});
	$(".deleteimg").css('cursor', 'pointer');

	// delete single schedule
	$(".delete_schedule").each(function() {
		$(this).click(function() {
			schedule_delete($(this).attr('id'));
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

	$('.autofill').click(function() {
		var the_val = $("input[name='" + $(this).attr('id') + "']").attr('value');
		if (the_val!='') {
			if (!confirm(_reports_propagate)) {
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
		xajax_get_saved_reports(rep_type_val[0], true);
	});

	$("#saved_report_id").change(function() {
		create_filename();
	});
	$("#period").change(function() {
		var sel_report = $("#saved_report_id").fieldValue();
		if (sel_report[0] != '')
			create_filename();
	});
});

function validate_report_form(f)
{
	var is_ok = check_form_values();
	if (!is_ok) {
		return false;
	}
	var errors = 0;
	var err_str = '';
	var jgrowl_err_str = '';
	var is_sla = false;
	// only run this part if report should be saved
	if ($("#save_report_settings").attr('checked') == true || $('input[name=sla_save]').attr('value') == '1') {

		var f = f == null ? document.forms['report_form'] : f;
		if ($('input[name=sla_save]').attr('value') == '1') {
			f = document.forms['report_form_sla'];
			is_sla = true;
		}

		var report_name = $.trim(f.report_name.value);
		if (report_name == '') {
			// fancybox is stupid and copies the form so we have to force
			// this script to check the form in the fancybox_content div
			report_name = $('#fancy_content #report_name').attr('value');
		}

		// these 2 fields should be the same no matter where on the
		// page they are found
		var saved_report_id = f.saved_report_id.value;
		var old_report_name = $.trim(f.old_report_name.value);

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
	$('#response').html('');
	return true;
}

function trigger_ajax_save(f)
{
	// first we need to make sure we get the correct field information
	// for report_name since fancybox is pretty stupid
	$('input[name=report_name]').attr('value', $('#fancy_content #report_name').attr('value'));

	// ajax post form options for SLA save generated report
	var sla_options = {
		target:			'#response',		// target element(s) to be updated with server response
		beforeSubmit:	validate_report_form,	// pre-submit callback
		success:		show_sla_saveresponse,	// post-submit callback
		dataType: 'json'
	};
	$('#report_form_sla').ajaxSubmit(sla_options);
	return false;
}

function show_sla_saveresponse(responseText, statusText)
{
	if (responseText['status'] == 'ok' && statusText == 'success') {
		jgrowl_message(responseText['status_msg'], _success_header);

		// propagate new values to form
		$('input[name=saved_report_id]').attr('value', responseText['report_id']);
		$('input[name=report_id]').attr('value', responseText['report_id']);
		$('input[name=report_name]').attr('value', $('#fancy_content #report_name').attr('value'));
		$('#scheduled_report_name').text($('#fancy_content #report_name').attr('value'));
	}
	$('#view_add_schedule').show();
	$('#save_to_schedule').hide();
	$(".fancybox").fancybox.close();
}

function trigger_schedule_save(f)
{
	// ajax post form options
	show_progress('progress', _wait_str);
	// fetch values from form
	var report_id = 0; // new schedule has no ID
	var rep_type = $('input[name=type]').attr('value');
	var saved_report_id = $('#fancy_content #saved_report_id').attr('value');
	var period = $('#fancy_content #period').attr('value');
	var period_str = $('#fancy_content #period option:selected').text();
	var recipients = $('#fancy_content #recipients').attr('value');
	var filename = $('#fancy_content #filename').attr('value');
	var description = $('#fancy_content #description').attr('value');

	$.ajax({
		url:_site_domain + _index_page + '/reports/schedule',
		type: 'POST',
		data: {report_id: report_id, rep_type: rep_type, saved_report_id: saved_report_id, period: period, recipients: recipients, filename: filename, description: description},
		success: function(data) {
			if (isNaN(data)) { // error!
				jgrowl_message(data, _reports_error);
			} else {
				$('#schedule_report_table').append(create_new_schedule_rows(data));
				jgrowl_message(_reports_schedule_create_ok, _reports_success);
				$(".fancybox").fancybox.close();
				$('#show_schedule').show(); // show the link to view available schedules
			}
		}
	});

	setTimeout('delayed_hide_progress()', 1000);
	return false;
}

function ajax_submit(f)
{
	show_progress('progress', _wait_str);
	// fetch values from form
	var report_id = $('#report_id').fieldValue();
	report_id = report_id[0];

	var rep_type = $('#rep_type').fieldValue();
	rep_type = rep_type[0];
	var rep_type_str = $('#rep_type option:selected').val();

	var saved_report_id = $('#saved_report_id').fieldValue();
	saved_report_id = saved_report_id[0];

	var period = $('#period').fieldValue();
	period = period[0];
	var period_str = $('#period option:selected').text();

	var recipients = $('#recipients').fieldValue();
	recipients = recipients[0];

	var filename = $('#filename').fieldValue();
	filename = filename[0];

	var description = $('#description').fieldValue();
	description = description[0];

	var report_types = $.parseJSON(_report_types_json);
	for (var i in report_types) {
		if (report_types[i] == rep_type) {
			report_type_id = i;
		}
	}

//	validate_form();
	$.ajax({
		url:_site_domain + _index_page + '/reports/schedule',
		type: 'POST',
		data: {report_id: report_id, rep_type: rep_type, saved_report_id: saved_report_id, period: period, recipients: recipients, filename: filename, description: description},
		success: function(data) {
			new_schedule_rows(saved_report_id, period_str, recipients, filename, description, rep_type_str, report_type_id);
			jgrowl_message(_reports_schedule_create_ok, _reports_success);
		}
	});
	return false;
}

function send_report_now(type, id)
{
	if (type=='' || id =='') {
		// missing info
		return false;
	}
	$.ajax({
		url:_site_domain + _index_page + '/reports/generate',
		type: 'POST',
		data: {type: type, schedule_id: id},
		success: function(data) {
			if (data == '') {
				jgrowl_message(_reports_schedule_send_ok, _reports_success);
			} else {
				jgrowl_message(_reports_schedule_send_error, _reports_error);
			}
		}
	});

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
		$('#switch_report_type').text(_label_switch_to + ' ' + _label_avail + ' ' + _label_report);
		$('#enter_sla').show();
		$('#csv_cell').hide();
		$("#report_type_label").text(_label_sla + ' ' + _label_report);
	} else {
		other_report = 'avail';
		$('#switch_report_type').text(_label_switch_to + ' ' + _label_sla + ' ' + _label_report);
		$('#enter_sla').hide();
		$('#csv_cell').show();
		$("#report_type_label").text(_label_avail + ' ' + _label_report);
	}
	$('input[name=type]').val(other_report);
	$("#single_schedules").remove();
	$("#display").hide();
	get_report_periods(other_report);
	xajax_get_saved_reports(other_report);

	// reset saved_report_id
	$('input[name=saved_report_id]').val(0);
	$('input[name=report_name]').val('');
}

function create_filename()
{
	var new_filename = $('#saved_report_id option:selected').text();
	new_filename = remove_scheduled_str(new_filename);
	new_filename += '_' + $('#period option:selected').text() + '.pdf';
	new_filename = new_filename.replace(' ', '_');
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
	hide_these = Array('hostgroup_row', 'servicegroup_row', 'host_row_2', 'service_row_2', 'settings_table', 'submit_button', 'enter_sla');
	hide_rows(hide_these);
	switch (val) {
		case 'hostgroups':
			get_members('', 'hostgroup', no_erase);
			show_row('hostgroup_row');
			break;
		case 'servicegroups':
			get_members('', 'servicegroup', no_erase);
			show_row('servicegroup_row');
			break;
		case 'hosts':
			get_members('', 'host', no_erase);
			show_row('host_row_2');
			break;
		case 'services':
			get_members('', 'service', no_erase);
			show_row('service_row_2');
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
	xajax_get_group_member(val, type, no_erase);
	sel_str = type;
	show_row('settings_table');
	show_row('submit_button');
}

/**
*	Let xajax fetch the report periods for
*	selected report type.
*
*	Result will be returned to populate_report_periods() below.
*/
function get_report_periods(type)
{
	xajax_get_report_periods(type);
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
		invalid_report_names[i] = txt;
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

function toggle_label_weight(val, the_id)
{
	if (document.getElementById(the_id)) {
		elem = document.getElementById(the_id);
		//elem.className = val ? 'bold' : '';
		//elem.style.fontStyle = val ? 'italic' : '';
		//elem.style.fontWeight = val ? 'bold' : '';
	}
}

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
		$('#state_options').show();
	} else {
		$('#state_options').hide();
	}
}

function toggle_field_visibility(val, theId) {
	if (val) {
		$('#' + theId).show();
	} else {
		$('#' + theId).hide();
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
		startDate = epoch_to_human(reportObj['start_time']);
		$('#start_time_tmp').text(format_date_str(startDate));
		document.forms['report_form'].start_time.value = format_date_str(startDate);
		endDate = epoch_to_human(reportObj['end_time']);
		$('#end_time_tmp').text(format_date_str(endDate));
		document.forms['report_form'].end_time.value = format_date_str(endDate);
	}
	current_obj_type = field_str;

	// wait for lists to populate
	setTimeout("remove_duplicates();", 500);
}

function set_initial_state(what, val)
{
	f = document.forms['report_form'];
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
			} else {
				toggle_label_weight(0, 'include_softstates');
				f.elements['includesoftstates'].checked = false;
			}
			break;
		case 'assumeinitialstates':
			if (val!='0') {
				edit_state_options(1);
				toggle_label_weight(1, 'assume_initial');
				f.elements['assumeinitialstates'].checked = true;
			} else {
				f.elements['assumeinitialstates'].checked = false;
				edit_state_options(0);
				toggle_label_weight(0, 'assume_initial');
			}
			break;
		case 'scheduleddowntimeasuptime':
			if (val!='0') {
				toggle_label_weight(1, 'sched_downt');
				f.elements['scheduleddowntimeasuptime'].checked = true;
			} else {
				f.elements['scheduleddowntimeasuptime'].checked = false;
				toggle_label_weight(0, 'sched_downt');
			}
			break;
		case 'report_name':
			f.elements['report_name'].value = val;
			break;
		case 'rpttimeperiod':
			item = 'rpttimeperiod';
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


/**
*	Remove duplicate entries
*	We need to check that we don't have items in the
*	left (available) list that is added to the to the right (selected)
'	Also, we need to check that the right list doesn't have items that are
*	removed from the configuration.
*/
function remove_duplicates()
{
	if (!current_obj_type) {
		return false;
	}
	if (!is_populated) {
		// check if lists has been populated before trying
		// to remove duplicates and removed objects.
		// Call self if not ready yet
		setTimeout("remove_duplicates();", 500);
		return false;
	}
	setup_hide_content('progress');
	var field_obj = new field_maps();
	var tmp_fields = new field_maps3();
	var field_str = current_obj_type;
	var field = field_obj.map[field_str]+'[]'
	var tmp_field = tmp_fields.map[field_str]+ '[]';
	var removed_items = new Array();
	var i = 0;

	$("select[name='" + field + "'] option").each(function() {
		var this_item = $(this).val();
		if ($("select[name='" + tmp_field + "']").containsOption(this_item)) {
			$("select[name='" + tmp_field + "']").removeOption($(this).val());
		} else {
			$("select[name='" + field + "']").removeOption(this_item);
			removed_items[i++] = this_item;
		}
	});
	if (removed_items.length) {
		var info_str = _reports_missing_objects + ": ";
		info_str += "<ul><li><img src=\"" + _site_domain + _theme_path + "icons/arrow-right.gif" + "\" /> " + removed_items.join('</li><li><img src="' + _site_domain + _theme_path + 'icons/arrow-right.gif' + '" /> ') + '</li></ul>';
		info_str += _reports_missing_objects_pleaseremove;
		info_str += '<a href="#" id="hide_response" onclick="hideMe(\'response\')" style="position:absolute;top:8px;left:700px;">Close <img src="' + _site_domain + _theme_path + '' + 'icons/12x12/cross.gif" /></a>';
		$('#response')
			.css('background','#f4f4ed url(' + _site_domain + _theme_path + 'icons/32x32/shield-info.png) 7px 7px no-repeat')
			.css("position", "relative")
			.css('top', '0px')
			.css('width','748px')
			.css('left', '0px')
			.css('padding','15px 2px 5px 50px')
			.css('margin-left','5px')
			.html(info_str);
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
			nr_of_scheduled_instances++;
			if (nr_of_scheduled_instances > 0) {
				$('#show_schedule').show();
			}
		}
	}
	setTimeout('hide_response()', time);
}

function create_new_schedule_rows(id)
{
	var return_str = '';
	var f = document.forms['schedule_report_form'];
	return_str += '<tr id="report-' + id + '">';
	return_str += '<td class="period_select" title="' + _reports_edit_information + '" id="period_id-' + id + '">' + $('#period option:selected').text(); + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="recipients-' + id + '">' + f.recipients.value + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="filename-' + id + '">' + f.filename.value + '</td>';
	return_str += '<td class="iseditable_txtarea" title="' + _reports_edit_information + '" id="description-' + id + '">' + f.description.value + '</td>';
	return_str += '<td class="delete_schedule" onclick="schedule_delete(' + id + ');" id="delid_' + id + '"><img src="' + _site_domain + _theme_path + 'icons/12x12/cross.gif"></td></tr>';
	return return_str;
}

function new_schedule_rows(id, period_str, recipients, filename, description, rep_type_str, report_type_id)
{
	var return_str = '';
	var reportname = $("#saved_report_id option:selected").text();
	reportname = remove_scheduled_str(reportname);
	return_str += '<tr id="report-' + id + '">';
	return_str += '<td class="period_select" title="' + _reports_edit_information + '" id="period_id-' + id + '">' + period_str + '</td>';
	return_str += '<td class="report_name" id="' + report_type_id + '.report_id-' + id + '">' + reportname + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="recipients-' + id + '">' + recipients + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="filename-' + id + '">' + filename + '</td>';
	return_str += '<td class="iseditable_txtarea" title="' + _reports_edit_information + '" id="description-' + id + '">' + description + '</td>';
	return_str += '<td class="delete_schedule" onclick="schedule_delete(' + id + ');" id="delid_' + id + '"><img src="' + _site_domain + _theme_path + 'icons/12x12/cross.gif"></td></tr>';
	$('#' + rep_type_str + '_scheduled_reports_table').append(return_str);
	setup_editable();
	$('#new_schedule_report_form').clearForm();
	setTimeout('delayed_hide_progress()', 1000);

	return true;
}

function schedule_delete(id)
{
	if (!confirm(_reports_confirm_delete_schedule)) {
		return false;
	}

	// clean input id from prefix (from setup template)
	if (isNaN(id)) {
		id = id.replace('delid_', '');  // from single report listing
		id = id.replace('alldel_', ''); // from all schedules list
	}

	var time = 6000;

	$.ajax({
		url:_site_domain + _index_page + '/reports/delete_schedule?id=' + id,
		success: function(data) {
			if (data == 'OK') {
				// item deleted
				remove_schedule(id);
			} else {
				jgrowl_message(data, _reports_error);
				setTimeout('hide_response()', time);
			}
		}
	});
}

function remove_schedule(id)
{
	var time = 3000;

	if (nr_of_scheduled_instances)
		nr_of_scheduled_instances--;

	// remove row for deleted ID (both in fancybox and in original table)
	$('#report-' + id).remove();
	$('#fancy_content #report-' + id).remove();
	if (nr_of_scheduled_instances == 0) {
		// last item deleted
		$('#schedule_report').hide(); // hide entire table/div
		$('#show_schedule').hide(); // remove 'View schedules' button
		$('#is_scheduled').remove();
		if ($('#report_id')) {
			var chk_text = '';
			chk_text = $('#report_id option:selected').text();
			chk_text = chk_text.replace(" ( *" + _scheduled_label + "* )", '');
			$('#report_id option:selected').text(chk_text);
		}
		if ($(".fancybox").is(':visible')) {
			$(".fancybox").fancybox.close();
		}
	}

	jgrowl_message(_reports_schedule_deleted, _reports_success);
	setTimeout('hide_response()', time);
}

function setup_editable()
{
	var save_url = _site_domain + _index_page + "/reports/save_schedule_item/";
	$(".iseditable").editable(save_url, {
		id   : 'elementid',
		name : 'newvalue',
		type : 'text',
		event : 'dblclick',
		width : 'auto',
		height : '14px',
		submit : _ok_str,
		cancel : _cancel_str,
		placeholder:_reports_edit_information
	});
	$(".period_select").editable(save_url, {
		data : $('#autoreport_periods').text(),
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : _ok_str,
		cancel : _cancel_str
	});
	$(".iseditable_txtarea").editable(save_url, {
		indicator : "<img src='" + _site_domain + "application/media/images/loading.gif'>",
		id   : 'elementid',
		name : 'newvalue',
		type : 'textarea',
		event : 'dblclick',
		rows: '3',
		submit : _ok_str,
		cancel : _cancel_str,
		cssclass: "txtarea",
		placeholder:_reports_edit_information
	});
	$(".report_name").editable(save_url, {
		data : function (){
			return fetch_report_data(this.id);
		},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : 'OK',
		cancel : 'cancel'
	});

}

var is_visible = false;
function toggle_edit() {
	if (is_visible) {
		$("#schedule_report").hide();
		$("#show_scheduled").text('[' + _edit_str + ']');
		is_visible = false;
	} else {
		$('#schedule_report').show();
		$("#show_scheduled").text('[' + _hide_str + ']');
		is_visible = true;
	}
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
		default:
			return false;
	}
}

function get_type_id(str)
{
	parts = str.split('.');
	return parts[0];
}
