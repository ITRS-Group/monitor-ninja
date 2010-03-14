var _time_error = false;
var _time_error_start = false;
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';

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

	$("#hide_response").click(function() {
		hideMe('response');
	});

	// only init datepicker onload on setup page
	// when we already have a report it is initialized on demand
	if (_current_uri.indexOf('generate') == -1){
		init_datepicker();
	}

	$(".fancybox").fancybox({
		'overlayOpacity'	:	0.7,
		'overlayColor'		:	'#ffffff',
		'hideOnContentClick' : false,
		'autoScale':true,
		'autoDimensions': true,
		'callbackOnShow': function() {
			if ($("#report_period").val() == 'custom' && $('input[name=sla_save]').attr('value') == '') {
				$(".fancydisplay").each(function() {
					$(this).show();
				});
				init_timepicker();
			}
		}
	});

});
function fancybox_datepicker()
{
	// datePicker Jquery plugin
	var datepicker_enddate = new Date().addDays(1).asString();
	$('.date-pick').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});

	$('.datepick-start').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				startDate = d.asString();
				$('#start_time').attr('value', d.asString());
				$("input[name=start_time]").attr('value', d.asString());
				$('#cal_end').dpSetStartDate(d.addDays(1).asString());
			}
		}
	);
	$('.datepick-end').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_start').dpSetEndDate(d.addDays(-1).asString());
				//console.log(d.addDays(1).asString());
				$('#end_time').attr('value', d.asString());
				$("input[name=end_time]").attr('value', d.addDays(1).asString());
				endDate = d.asString();
				//console.log( Math.round(d.getTime()/1000) ); // working valid timestamp
			}
		}
	);
}


function init_datepicker()
{
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
				$('#end_time').attr('value', d.addDays(1).asString());
				endDate = d.asString();
				//console.log( Math.round(d.getTime()/1000) ); // working valid timestamp
			}
		}
	);
}

function show_hide(id,h1) {
	if ($('#' + id) && !$('#' + id).is(':visible')) {
		$('#' + id)
		.show()
		.css('background', 'url(icons/arrows/grey-down.gif) 7px 7px no-repeat');
	} else {
		$('#' + id)
		.hide()
		.css('background', 'url(icons/arrows/grey.gif) 11px 3px no-repeat');
	}
}

function show_calendar(val, update) {
	$('#response').html('');
	if (val=='custom') {
		if ($('input[name=type]').val() != 'sla') {
			$("#display").show();

			$(".fancydisplay").each(function() {
				$(this).show();
			});

			init_timepicker();

			if (update == '') {
				$('input[name=start_time]').attr('value', '');
				$('input[name=end_time]').attr('value', '');
			}
		} else {
			// known issue - custom report period does not work for SLA reports
			$('#response').html("<ul class=\"error\"><li>Known issue in this beta: <br />Custom report period does not currently work for SLA</li></ul><br />");
		}
	} else {
		$("#display").hide();
		$(".fancydisplay").each(function() {
			$(this).hide();
		});
	}
	disable_sla_fields(val);
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
			$('#block_host_states').show();
			$('#block_service_states').hide();
			break;
		case 'servicegroups':
			get_members('', 'servicegroup', no_erase);
			$('#servicegroup_row').show();
			$('#block_host_states').hide();
			$('#block_service_states').show();
			break;
		case 'hosts':
			get_members('', 'host', no_erase);
			$('#host_row_2').show();
			$('#block_host_states').show();
			$('#block_service_states').hide();
			break;
		case 'services':
			get_members('', 'service', no_erase);
			$('#service_row_2').show();
			$('#block_host_states').hide();
			$('#block_service_states').show();
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
		if ($(this).is(':visible')) {
			$(this).children(':option').attr('selected', 'selected');
		} else {
			$(this).children(':option').attr('selected', false);
		}
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
		if ($('input[name=type]').val() == 'sla') {
			return false;
		}
		// date validation
		var cur_startdate = startDate = Date.fromString($("input[name=cal_start]").attr('value'));
		var cur_enddate = endDate = Date.fromString($("input[name=cal_end]").attr('value'));
		var now = new Date();
		if (!cur_startdate || !cur_enddate) {
			if (!cur_startdate) {
				errors++;
				err_str += "<li>" + _reports_invalid_startdate + ".</li>";
			}
			if (!cur_enddate) {
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

		// time validation: _time_error and _time_error_start
		if (_time_error || _time_error_start) {
			errors++;
			err_str += "<li>" + _reports_invalid_timevalue + ".</li>";
		}

		// date and time seems OK so let's add time to date field
		// since fancybox has the (bad) habit of duplicating
		// the element that is shows, it means that the contained form
		// values will be duplicated as well.
		// By looping through these fields (class names) we can use the last one for
		// the correct value. If we are NOT using fancybox, we will get
		// the (only) value anyway.
		var curval_starttime = false;;
		$(".time_start").each(function() {
			curval_starttime = $(this).val();
		});

		var curval_endtime = false;
		$(".time_end").each(function() {
			curval_endtime = $(this).val();
		});

		if (endDate < startDate || ($("input[name=cal_start]").val() === $("input[name=cal_end]").val() && curval_endtime < curval_starttime) ) {
			errors++;
			err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
			$(".datepick-start").addClass("time_error");
			$(".datepick-end").addClass("time_error");
		} else {
			$(".datepick-start").removeClass("time_error");
			$(".datepick-end").removeClass("time_error");
		}
	}

	if ($("#" + field_obj.map[rpt_type]).is('select') && $("#" + field_obj.map[rpt_type] + ' option').length == 0) {
		errors++;
		err_str += "<li>" + _reports_err_str_noobjects + ".</li>";
	}

	if ($("#enter_sla").is(":visible")) {
		// check for sane SLA values
		var red_error = false;
		var max_val = 100;
		var nr_of_slas = 0;

		for (i=1;i<=12;i++) {
			var field_name = 'month_' + i;
			var value = $('input[name=' + field_name + ']').attr('value');
			value = value.replace(',', '.');
			if (value > max_val || isNaN(value)) {
				$('input[name=' + field_name + ']').css('background', sla_month_error_color);
				errors++;
				red_error = true;
			} else {
				if (value != '') {
					nr_of_slas++;
				}
				if ($("input[name='" + field_name + "']").attr('disabled'))
					$('input[name=' + field_name + ']').css('background', sla_month_disabled_color);
				else
					$('input[name=' + field_name + ']').css('background', sla_month_enabled_color);
			}
		}
		if (red_error) {
			err_str += '<li>' + _reports_sla_err_str + '</li>';
		}

		if (nr_of_slas == 0 && !red_error) {
			errors++;
			err_str += "<li>" + _reports_no_sla_str + "</li>";
		}
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
		if ($('#fancy_content').is(':visible')) {
			$('#fancy_content #start_time').attr('value', $('#fancy_content #cal_start').attr('value') + ' ' + curval_starttime);
			$('#fancy_content #end_time').attr('value', $('#fancy_content #cal_end').attr('value') + ' ' + curval_endtime);
		} else {
			$("input[name=start_time]").attr('value', $("input[name=cal_start]").attr('value') + ' ' + curval_starttime);
			$("input[name=end_time]").attr('value', $("input[name=cal_end]").attr('value') + ' ' + curval_endtime);
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
	var interval = $('#period').val();
	var recipients = $('input[name=recipients]').attr('value');
	var filename = $('input[name=filename]').attr('value');
	var description = $('input[name=description]').attr('value');
	var saved_report_id = $('input[name=saved_report_id]').attr('value');
	if (!saved_report_id) {
		saved_report_id = $('#saved_report_id').attr('value');
	}
	var report_id = $('input[name=report_id]').attr('value');
	if (report_id == '' || report_id == 'undefined') {
		report_id = $('#report_id').val();
	}
	var fatal_err_str = _reports_fatal_err_str;// + "<br />";
	$('.schedule_error').hide();

	var err_str = "";
	var errors = 0;
	if (interval[0] == '' || !interval[0]) {
		err_str += _reports_schedule_interval_error + "<br />";
		errors++;
	}

	recipients = recipients.replace(/;/g, ',');
	// @@@FIXME: split multiple addresses on ',' and check each one using regexp
	if (trim(recipients) == '') {
		err_str += _reports_schedule_recipient_error + "<br />";
		errors++;
	}
	if (!saved_report_id) {
		alert(fatal_err_str);
		return false;
	}

	if (errors) {
		/*
		$('#response').attr("style", "");
		$('#response').html("<ul class=\"error\">" + err_str + "</ul>").show();
		*/
		var str = _reports_errors_found + ':<br />' + err_str + '<br />' + _reports_please_correct + '<br />';
		$("#new_schedule_area").prepend("<div id=\"response\" class=\"schedule_err_display\"><ul class=\"error\">" + str + "</ul></div>");
		window.scrollTo(0,0); // make sure user sees the error message
		return false;
	}
	$('.schedule_err_display').remove();
    return true;
}

// init timepicker once it it is shown
function init_timepicker()
{
	// Use default timepicker settings
	$("#time_start, #time_end").timePicker();

	// Store time used by duration.
	var oldTime = $.timePicker("#time_start").getTime();

	// Keep the duration between the two inputs.
	$("#time_start").change(function() {
		if (!validate_time($("#time_start").val())) {
			$(this).addClass("time_error");
			_time_error_start = true;
		} else {
			$(this).removeClass("time_error");
			_time_error_start = false;
		}
		if ($("#time_end").val()) { // Only update when second input has a value.
			// Calculate duration.
			var duration = ($.timePicker("#time_end").getTime() - oldTime);
			var time = $.timePicker("#time_start").getTime();
			// Calculate and update the time in the second input.
			$.timePicker("#time_end").setTime(new Date(new Date(time.getTime() + duration)));
			oldTime = time;
		}
	});
}

function validate_time(tmp_time)
{
	var time_parts = tmp_time.split(':');
	if (time_parts.length!=2 || isNaN(time_parts[0]) || isNaN(time_parts[1])) {
		return false;
	}
	return true;
}

function disable_sla_fields(report_period)
{
	if (!$("#enter_sla").is(":visible"))
		return;
	$('#csv_cell').hide();
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
	return false;
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
