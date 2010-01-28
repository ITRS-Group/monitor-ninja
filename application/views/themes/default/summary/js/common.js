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

	// handle standard/custom report switching
	$("#report_mode_standard").click(function() {
		$("#std_report_table").show();
		$("#custom_report").hide();
	});
	$("#report_mode_custom").click(function() {
		$("#std_report_table").hide();
		$("#custom_report").show();
	});

	/*
	$("#cal_end").bind('blur', function() {
		setTimeout('validate_date()', 1000);
	});
	*/

	$("#hide_response").click(function() {
		hideMe('response');
	});

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

	/*
	$(".fancybox").fancybox({
		'overlayOpacity'	:	0.7,
		'overlayColor'		:	'#ffffff',
		'hideOnContentClick' : false,
	});
	*/
});


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
}

function set_selection(val, no_erase) {
	// start by hiding ALL rows
	hide_these = Array('hostgroup_row', 'servicegroup_row', 'host_row_2', 'service_row_2', 'settings_table', 'submit_button', 'enter_sla');
	hide_rows(hide_these);
	show_progress('progress', _wait_str);
	switch (val) {
		case 'hostgroups':
			get_members('', 'hostgroup', no_erase);
			$('#hostgroup_row').show();
			break;
		case 'servicegroups':
			get_members('', 'servicegroup', no_erase);
			$('#servicegroup_row').show();
			break;
		case 'hosts':
			get_members('', 'host', no_erase);
			$('#host_row_2').show();
			break;
		case 'services':
			get_members('', 'service', no_erase);
			$('#service_row_2').show();
			break;
	}
	$('#settings_table').show();
	if ($('input[name=type]').val() == 'sla')
		$('#enter_sla').show();
	$('#submit_button').show();
}

/**
*	Uncheck form element by name
*	Used to set correct initial values
*	since some browser seem to cache checkbox state
*/
function uncheck(the_name, form_name)
{
	$("input[name='" + the_name + "']").attr('checked', false);
}

function hide_rows(input) {
	for (i=0;i<input.length;i++) {
		if (document.getElementById(input[i]))
			$("#" + input[i]).hide();
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
	$('#settings_table').show();
	$('#submit_button').show();
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

function add_if_not_exist(tmp_field, val) {
	addSelectOption(tmp_field, val);
}


function epoch_to_human(val){
	var the_date = new Date(val * 1000);
	return the_date;
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
