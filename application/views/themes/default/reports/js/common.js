var startDate;
var endDate;
var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;
var current_obj_type = false; // keep track of what we are viewing
var is_populated = false; // flag list population when done
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
//var _scheduled_label = '';
var invalid_report_names = '';
var current_filename;

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

$(document).ready(function() {
	// handle the move-between-lists-button (> + <) and double click events
	// hostgroups >
	$("#mv_hg_r").click(function() {moveAndSort('hostgroup_tmp', 'hostgroup');});
	$("#hostgroup_tmp").dblclick(function() {moveAndSort('hostgroup_tmp', 'hostgroup');});
	// hostgroups <
	$("#mv_hg_l").click(function() {moveAndSort('hostgroup', 'hostgroup_tmp');});
	$("#hostgroup").dblclick(function() {moveAndSort('hostgroup', 'hostgroup_tmp');});

	// servicegroup >
	$("#mv_sg_r").click(function() {moveAndSort('servicegroup_tmp', 'servicegroup');});
	$("#servicegroup_tmp").dblclick(function() {moveAndSort('servicegroup_tmp', 'servicegroup');});
	// servicegroup <
	$("#mv_sg_l").click(function() {moveAndSort('servicegroup', 'servicegroup_tmp');});
	$("#servicegroup").dblclick(function() {moveAndSort('servicegroup', 'servicegroup_tmp');});

	// hosts >
	$("#mv_h_r").click(function() {moveAndSort('host_tmp', 'host_name');});
	$("#host_tmp").dblclick(function() {moveAndSort('host_tmp', 'host_name');});
	// hosts <
	$("#mv_h_l").click(function() {moveAndSort('host_name', 'host_tmp');});
	$("#host_name").dblclick(function() {moveAndSort('host_name', 'host_tmp');});

	// services >
	$("#mv_s_r").click(function() {moveAndSort('service_tmp', 'service_description');});
	$("#service_tmp").dblclick(function() {moveAndSort('service_tmp', 'service_description');});
	// services <
	$("#mv_s_l").click(function() {moveAndSort('service_description', 'service_tmp');});
	$("#service_description").dblclick(function() {moveAndSort('service_description', 'service_tmp');});

	show_state_options($('#assumeinitialstates').attr('checked'));

	//$("#optiontoggle").click(function() {$("#options").toggle();});

	$("#report_form").bind('submit', function() {
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

	$(".send_report_now").click(function() {
		var type_id = $(this).attr('id');
		type_id = type_id.replace('send_now_', '');
		type_id = type_id.split('_');
		var type = type_id[0];
		var id = type_id[1];
		send_report_now(type, id);
	});

	/*
	$("#cal_end").bind('blur', function() {
		setTimeout('validate_date()', 1000);
	});
	*/
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

	$("#hide_response").click(function() {
		hideMe('response');
	});

	disable_sla_fields($('#report_period').attr('value'));

	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});
	//set_selection(document.getElementsByName('report_type').item(0).value);
	/*
	$('#sel_report_type').click(function() {
		//set_selection(document.forms['report_form'].report_type.value);
		set_selection($('select[name=report_form] option:selected').attr('value'));
	});
	*/
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

	// datePicker Jquery plugin
	var datepicker_enddate = new Date().addDays(1).asString();
	$('.date-pick').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});
	$('#cal_start').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				startDate = d.asString();
				$('#start_time').attr('value', d.asString());
				$('#cal_end').dpSetStartDate(d.addDays(1).asString());
			}
		}
	);
	$('#cal_end').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_start').dpSetEndDate(d.addDays(-1).asString());
				//console.log(d.addDays(1).asString());
				$('#end_time').attr('value', d.asString());
				endDate = d.asString();
				//console.log( Math.round(d.getTime()/1000) ); // working valid timestamp
			}
		}
	);
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

	$('#report-tabs').tabs();

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
	$(".fancybox").fancybox({
		'overlayOpacity'	:	0.7,
		'overlayColor'		:	'#ffffff',
		'hideOnContentClick' : false,
	});
});

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
			jgrowl_message(_reports_schedule_create_ok, _reports_successs);
		}
	});
	return false;
}

function show_hide(id,h1) {
	if (document.getElementById(id).style.display == 'none') {
		document.getElementById(id).style.display = '';
		h1.style.background = 'url(icons/arrows/grey-down.gif) 7px 7px no-repeat';
	}
	else {
		document.getElementById(id).style.display = 'none';
		h1.style.background = 'url(icons/arrows/grey.gif) 11px 3px no-repeat';
	}
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
				jgrowl_message(_reports_schedule_send_ok, _reports_successs);
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
		$("#report_type_label").text(_label_sla + ' ' + _label_report);
	} else {
		other_report = 'avail';
		$('#switch_report_type').text(_label_switch_to + ' ' + _label_sla + ' ' + _label_report);
		$('#enter_sla').hide();
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

function show_calendar(val, update) {
	if (val=='custom') {
		$("#display").show();
		if (update == '') {
			document.forms['report_form'].start_time.value='';
			document.forms['report_form'].end_time.value='';
		}
	} else {
		$("#display").hide();
	}
	disable_sla_fields(val);
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

function disable_sla_fields(report_period)
{
	if (!$("#enter_sla").is(":visible"))
		return;
	var now = new Date();
	var this_month = now.getMonth()+1;
	switch (report_period) {
		case 'thisyear':
			// weird as it seems, the following call actually ENABLES
			// all months. If not, we could end up with all months being
			// disabled for 'thisyear'
			disable_months(0, 12);
			for (i=this_month;i<=12;i++)
			{
				document.forms['report_form'].elements['month_' + i].value='';
				document.forms['report_form'].elements['month_' + i].disabled=true;
				document.forms['report_form'].elements['month_' + i].style.backgroundColor=sla_month_disabled_color;
			}
			break;
		case 'custom':
			check_custom_months();
			break;
		case 'lastmonth':
			disable_last_months(1);
			break;
		case 'last3months':
			disable_last_months(3);
			break;
		case 'last6months':
			disable_last_months(6);
			break;
		case 'lastquarter':
			if(this_month <= 3){
				from = 10;
				to = 12;
			} else if (this_month <= 6) {
				from = 1;
				to = 3;
			} else if (this_month <= 9){
				from = 4;
				to = 6;
			} else {
				from = 7;
				to = 9;
			}
			disable_months(from, to);
			break;
		default:
			for (i=1;i<=12;i++)
			{
				document.forms['report_form'].elements['month_' + i].disabled=false;
				document.forms['report_form'].elements['month_' + i].style.backgroundColor=sla_month_enabled_color;
			}
	}
}

function disable_months(start, end)
{
	var disabled_state 		= false;
	var not_disabled_state 	= false;
	var col 				= false;
	start 	= eval(start);
	end 	= eval(end);
	for (i=1;i<=12;i++) {
		if (start>end) {
			if ( i >= start || i <= end) {
				disabled_state = false;
				col = sla_month_enabled_color;
			} else {
				document.forms['report_form'].elements['month_' + i].value='';
				disabled_state = true;
				col = sla_month_disabled_color;
			}
		} else {
			if ( i>= start && i <= end) {
				disabled_state = false;
				col = sla_month_enabled_color;
			} else {
				document.forms['report_form'].elements['month_' + i].value='';
				disabled_state = true;
				col = sla_month_disabled_color;
			}
		}
		document.forms['report_form'].elements['month_' + i].disabled=disabled_state;
		document.forms['report_form'].elements['month_' + i].style.backgroundColor=col;
	}
}

function check_custom_months()
{
	var f		 	= document.forms['report_form'];
	var start_year 	= f.start_year.value;
	var start_month = f.start_month.value;
	var end_year 	= f.end_year.value;
	var end_month 	= f.end_month.value;
	if (start_year!='' && end_year!='' && start_month!='' && end_month!='') {
		if (start_year < end_year) {
			// start and end months will have to "restart"
			disable_months(start_month, end_month);
		} else {
			if (start_year < end_year || start_year == end_year) {
				// simple case - disable from start_month to end_month
				disable_months(start_month, end_month);
			} else {
				// start_year > end_year = ERROR
				// handled by check_form_values but let's disable all months?
				disable_months(0, 0);
			}
		}
	}
	setup_hide_content('progress');
}

/**
 * Generic function to disable month_ fields
 * depending on if selection is last 1, 3 or 6 months.
 */
function disable_last_months(mnr)
{
	var now = new Date();
	var this_month = now.getMonth()+1;
	if (!mnr)
		return false;
	var from = (this_month-mnr);
	var to = (this_month-1);
	from = from<=0 ? (from + 12) : from;
	to = to<=0 ? (to + 12) : to;
	disable_months(from, to);
}

/**
*	Loop through all elements of a form
*	Verify that all multiselect fields (right hand side)
*	are set to selected
*/
function loopElements(f) {
	// Specify which form fields (select) we are NOT interested in
	var nosave_suffix = "_tmp";

	// select all elements that doesn't contain the nosave_suffix
	$('.multiple:not([name*=' + nosave_suffix + '])').each(function() {
		$(this).children(':option').attr('selected', 'selected');
	});

	// unselect the rest
	$('.multiple[name*=' + nosave_suffix + ']').each(function() {
		$(this).children(':option').attr('selected', false);
	});
}

function field_maps()
{
	this.map = new Object();
	this.map['hosts']="host_name";
	this.map['services']="service_description";
	this.map['hostgroups']="hostgroup";
	this.map['servicegroups']="servicegroup";
}

function field_maps3()
{
	this.map = new Object();
	this.map['hosts']="host_tmp";
	this.map['services']="service_tmp";
	this.map['hostgroups']="hostgroup_tmp";
	this.map['servicegroups']="servicegroup_tmp";
}

function check_form_values()
{
	var errors = 0;
	var err_str = '';
	var field_obj = new field_maps();
	var rpt_type = $("#report_type").val();
	if ($("#report_period").val() == 'custom') {
		// date validation
		var now = new Date();
		if (!startDate || !endDate) {
			if (!startDate) {
				errors++;
				err_str += "<li>" + _reports_invalid_startdate + ".</li>";
			}
			if (!endDate) {
				errors++;
				err_str += "<li>" + _reports_invalid_enddate + ".</li>";
			}
		} else {
			if (endDate > now) {
				if (!confirm(_reports_enddate_infuture)) {
					return false;
				} else {
					endDate = now;
				}
			}
		}
	}

	if ($("#" + field_obj.map[rpt_type]).is('select') && $("#" + field_obj.map[rpt_type] + ' option').length == 0) {
		errors++;
		err_str += "<li>" + _reports_err_str_noobjects + ".</li>";
	}

	/**
	*	Using JQuery to ensure that an existing report
	*	can't use an already existing (saved) name.
	*/
	if(typeof window.jQuery != "undefined") {
		var report_name 	= $("input[name=report_name]").val();
		var saved_report_id = $("input[name=saved_report_id]").val();
		var do_save_report 	= $('input[name=save_report_settings]').attr('checked') ? 1 : 0;

		/*
		*	Only perform checks if:
		*		- Saved report exists
		*		- User checked the 'Save Report' checkbox
		*		- We are currently editing a report (i.e. have saved_report_id)
		*/
		if ($('#report_id') && do_save_report && saved_report_id) {
			// Saved reports exists
			$('#report_id option').each(function(i) {
				if ($(this).val()) {// first item is empty
					if (saved_report_id != $(this).val()) {
						// check all the other saved reports
						// make sure we don't miss the scheduled reports
						var chk_text = $(this).text();
						chk_text = chk_text.replace(" ( *" + _scheduled_label + "* )", '');
						if (report_name == chk_text) {
							// trying to save an item with an existing name
							errors++;
							err_str += "<li>" + _reports_error_name_exists + ".</li>";
						}
					}
				}
			});
		}
	}
	// display err_str if any
	if (!errors) {
		$('#response').html('');

		// check if report name is unique
		if(saved_report_id == '' && invalid_report_names && invalid_report_names.indexOf(report_name) != -1)
		{
			if(!confirm(_reports_error_name_exists_replace))
			{
				return false;
			}
		}
		return true;
	}

	// clear all style info from progress
	$('#response').attr("style", "");
	$('#response').html("<ul class=\"error\">" + err_str + "</ul>");
	window.scrollTo(0,0); // make sure user sees the error message
	return false;
}

function check_and_submit(f)
{
	if ($("#report_id").attr('value')!="") {
		return true;
	} else {
		$('#is_scheduled').text('');
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

function add_if_not_exist(tmp_field, val) {
	addSelectOption(tmp_field, val);
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

function epoch_to_human(val){
	var the_date = new Date(val * 1000);
	return the_date;
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

function hideMe(elem)
{
	$('#' + elem).hide('slow');
}

function validate_date(what)
{
	var start = $('#cal_start').attr('value');
	var end = $('#cal_end').attr('value');
	//console.log(Date.fromString(start));
	//console.log(Date.fromString(end));

	if (end < start) {
		//console.log('That is BAD');
	} else {
		//console.log('seems OK');
	}
}

function confirm_delete_report(the_val)
{
	var the_path = self.location.href;
	the_path = the_path.replace('#', '');

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	if (the_val!="" && the_path!="") {
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

function show_message(class_name, msg)	{
	$('#response').show().html('<ul class="' + class_name + '">' + msg + '</ul>');
	setTimeout('hide_response()', 5000);
}

function move_option(from_id, to_id)
{
	 return !$('#' + from_id + ' option:selected').remove().appendTo('#' + to_id);
}

function moveAndSort(from_id, to_id)
{
	move_option(from_id, to_id);
	$("#" + to_id).sortOptions();
}

/**
*	Make sure all values are properly entered
*/
function validate_form(formData, jqForm, options) {
	var interval = $('#period').fieldValue();
	var recipients_tmp = $('#recipients').fieldValue();
	var recipients = recipients_tmp[0];
	var filename = $('#filename').fieldValue();
	var description = $('#description').fieldValue();
	var saved_report_id = $('#saved_report_id').fieldValue();
	var report_id = $('#report_id').fieldValue();
	var fatal_err_str = _reports_fatal_err_str;// + "<br />";
	$('.schedule_error').hide();

	var err_str = "";
	var errors = 0;
	if (interval[0] == '' || !interval[0]) {
		err_str += _reports_schedule_interval_error + "<br />";
		errors++;
	}

	recipients = recipients.replace(/;/g, ',');
	$('#recipients').fieldValue();
	// @@@FIXME: split multiple addresses on ',' and check each one using regexp
	if (trim(recipients) == '') {
		err_str += _reports_schedule_recipient_error + "<br />";
		errors++;
	}
	if (!saved_report_id[0]) {
		alert(fatal_err_str);
		return false;
	}

	if (errors) {
		var str = _reports_errors_found + ':<br />' + err_str + '<br />' + _reports_please_correct + '<br />';
		$("#TB_ajaxContent").prepend('<div class="schedule_error">' + str + '</div');
		return false;
	}

    return true;
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
			jgrowl_message(_reports_schedule_update_ok, _reports_successs);
		} else { //new save
			jgrowl_message(_reports_schedule_create_ok, _reports_successs);
			$('#schedule_report_table').append(create_new_schedule_rows(responseText));
			$('#schedule_report_table').show();
			schedule_is_visible = true;
			$('#schedule_report_form').clearForm();
			setup_editable();
			//tb_remove();
			nr_of_scheduled_instances++;
			if (nr_of_scheduled_instances==1) {
				// add 'Vew schedules' Button
				// @@@FIXME: update when we have fancybox or whatever to replace thickbox
				//$('#view_add_schedule').append('<input type="button" id="show_schedule" alt="#TB_inline?height=500&width=550&inlineId=schedule_report" class="button view-schedules20 thickbox" value="' + _reports_view_schedule + '">');
				//tb_init('#show_schedule');
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
	return_str += '<td class="report_name" id="' + report_type_id + '.report_id-' + id + '">' + reportname + '</td>'
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
	id = id.replace('delid_', '');  // from single report listing
	id = id.replace('alldel_', ''); // from all schedules list

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
	jgrowl_message(_reports_schedule_deleted, _reports_successs);
	nr_of_scheduled_instances--;
	// remove row for deleted ID
	$('#report-' + id).remove();
	if (nr_of_scheduled_instances == 0) {
		// last item deleted
		$('#schedule_report').hide(); // hide entire table/div
		$('#show_schedule').remove(); // remove 'View schedules' button
		$('#is_scheduled').remove();
		if ($('#report_id')) {
			var chk_text = '';
			chk_text = $('#report_id option:selected').text();
			chk_text = chk_text.replace(" ( *" + _scheduled_label + "* )", '');
			$('#report_id option:selected').text(chk_text);
		}

		if (!$("#report_type").is(":visible")) { // setup doesn't use thickbox
			//@@@FIXME: fix when thickbox replacement is in place
			//tb_remove(); // close thickbox
		}
	}
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
