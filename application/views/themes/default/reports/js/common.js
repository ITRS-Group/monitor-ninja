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
var sla_month_disabled_color = '#dcdcd7';
var sla_month_enabled_color  = 'white';
var _scheduled_label = '';
var invalid_report_names = '';

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

	$("#optiontoggle").click(function() {$("#options").toggle();});

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

	/*
	$("#cal_end").bind('blur', function() {
		setTimeout('validate_date()', 1000);
	});
	*/
	$("#delete_report").click(function() {
		confirm_delete_report($("#report_id").attr('value'));
	});

	$('#new_report').click(function() {
		var uri = self.location.href;
		self.location.href=uri;
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
			if (!confirm("Would you like to propagate this value to all months")) {
				return false;
			}
			set_report_form_values(the_val);
		} else {
			if (!confirm("Would you like to remove all values from all months")) {
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
				console.log(d.addDays(1).asString());
				$('#end_time').attr('value', d.asString());
				endDate = d.asString();
				//console.log( Math.round(d.getTime()/1000) ); // working valid timestamp
			}
		}
	);

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
		.html('<br /><img id="progress_image_id" src="' + Image1.src + '"> ' + info_str)
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
		elem.style.fontWeight = val ? 'bold' : '';
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
				err_str += "<li>You haven't entered a valid Start date.</li>";
			}
			if (!endDate) {
				errors++;
				err_str += "<li>You haven't entered a valid End date.</li>";
			}
		} else {
			if (endDate > now) {
				if (!confirm("You have entered an End date in the future.\nClick OK to change this to current time or cancel to modify.")) {
					return false;
				} else {
					endDate = now;
				}
			}
		}
	}

	if ($("#" + field_obj.map[rpt_type]).is('select') && $("#" + field_obj.map[rpt_type] + ' option').length == 0) {
		errors++;
		err_str += "<li>Please select what objects to base the report on by moving <br />";
		err_str += " objects from the left selectbox to the right selectbox.</li>";
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
							err_str += "<li>You have entered a name for your report that already exists. <br />Please select a new name.</li>";
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
			if(!confirm("The entered name already exists. Press 'Ok' to replace the entry with this name"))
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
	set_initial_state('report_name', reportObj['report_name']);
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
		var info_str = "Some items in your saved report doesn't exist anymore and has been removed: ";
		info_str += "<ul><li><img src=\"" + _site_domain + _theme_path + "icons/arrow-right.gif" + "\" /> " + removed_items.join('</li><li><img src="' + _site_domain + _theme_path + 'icons/arrow-right.gif' + '" /> ') + '</li></ul>';
		info_str += 'Please modify the objects to include in your report below and then save it.';
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
	console.log(Date.fromString(start));
	console.log(Date.fromString(end));

	if (end < start) {
		console.log('That is BAD');
	} else {
		console.log('seems OK');
	}
}

function confirm_delete_report(the_val)
{
	var the_path = self.location.href;
	console.log($("#report_id").attr('value'));
	console.log('input: ' + the_val);

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = "Are you really sure that you would like to remove this saved report?";
	if (the_val!="" && the_path!="") {
		if (is_scheduled) {
			msg += "\n\nPlease note that this is a scheduled report and if you decide to delete it, \n";
			msg += "the corresponding schedule will be deleted as well.\n\n";
			msg += "Are you really sure that this is what you want?";
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
