var startDate;
var endDate;
var DEBUG = false;
var is_populated = false; // flag list population when done

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

/**
*	store_start_date
*	Validate that start_time is BEFORE end_date
*	Called from end_time cal
*/
function store_start_date(cal) {
	cal.hide();
	if(!cal.dateClicked)
		return false;

	var prev_start_date = startDate;
	startDate = new Date(cal.date);
	var now = new Date();
	if (endDate) {
		if (endDate <= startDate) {
			log('endDate <= startDate');
			alert("Please select a Start date BEFORE End date");

			log("setting start_time = " + start_time_bkup);
			$('input[name=start_time]').val(start_time_bkup);
			$('#start_time_tmp').text(start_time_bkup);
			startDate = prev_start_date;
			return false;
		}
	}
	if (startDate > now) {
		log('startDate > now!');
		alert("Please select a Start date in the past");

		$('input[name=start_time]').val(start_time_bkup);
		$('#start_time_tmp').text(start_time_bkup);
		startDate = prev_start_date;
		return false;
	}
	start_time_bkup = $('input[name=start_time]').val();
}

/**
*	check_start_date
*	Validate that end_date is AFTER start_time
*	Called from end_time cal
*/
function check_start_date(cal) {
	cal.hide();
	if(!cal.dateClicked)
		return false;

	var prev_end_date = endDate;
	endDate = new Date(cal.date);
	//endDate.setHours(0,0,0,0);
	log('StartDate: ' + startDate);
	log('EndDate: ' + endDate);

	if (startDate) {
		log('we HAVE startDate');
		//startDate.setHours(0,0,0,0);
		log('startDate: ' + startDate);
		if (startDate >= endDate) {
			log('startDate >= endDate');
			log('setting end_time = ' + end_time_bkup);
			alert("Please select an End date AFTER Start date");

			$('input[name=end_time]').val(end_time_bkup);
			$('#end_time_tmp').text(end_time_bkup);
			endDate = prev_end_date;
			return false;
		}
	}
	end_time_bkup = $('input[name=end_time]').val();
}

function show_calendar(val, update) {
	if (val=='custom') {
		$("#display").show();
		if (update == '') {
			document.forms['avail_form'].start_time.value='';
			document.forms['avail_form'].end_time.value='';
		}
	} else {
		$("#display").hide();
	}
}

// Restore has to work in both IE and FF...
var canSee = navigator.appName.indexOf("Microsoft") > -1 ? 'block' : 'table-row';

function hide_rows(input) {
	for (i=0;i<input.length;i++) {
		document.getElementById(input[i]).style.display='none';
	}
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
	show_progress('progress', 'Please wait...');
	for (var i = 0; i < json_data.length; i++) {
		addSelectOption(tmp_field, json_data[i].optionValue, json_data[i].optionValue, document.forms['avail_form'].elements[field]);
	}
	is_populated = true;
	setTimeout('delayed_hide_progress()', 1000);
}

// delay hiding of progress indicator
function delayed_hide_progress()
{
	setup_hide_content('progress');
}

function addSelectOption(theSel, theText, theValue, theSelFrom)
{
	theSel = theSel.replace('[', '\\[');
	theSel = theSel.replace(']', '\\]');
	$("#"+theSel).addOption(theValue, theText, false);
}

function add_if_not_exist(tmp_field, field, val) {
	addSelectOption(tmp_field, val, val, document.forms['avail_form'].elements[field]);
}

function log(msg) {
	if (DEBUG) {
		console.log(msg);
	}
}

function setup_hide_content(d) {
	if(d.length < 1) {
		return;
	}
	$('#' + d).hide();
}

function toggle_label_weight(val, the_id)
{
	if (document.getElementById(the_id)) {
		elem = document.getElementById(the_id);
		//elem.className = val ? 'bold' : '';
		elem.style.fontWeight = val ? 'bold' : '';
	}
}

function check_form_values(f)
{
	var errors = 0;
	var err_str = '';
	var field_obj = new field_maps();
	var rpt_type = f.report_type.value;
	if (f.report_period.value == 'custom') {
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

	// display err_str if any
	if (!errors) {
		document.getElementById('response').innerHTML = '';

		// check if report name is unique
		if(saved_avail_id == '' && invalid_report_names && invalid_report_names.indexOf(report_name) != -1)
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

/**
*	Set internal date objects
*/
function parse_date_str(field_id, form_id)
{
	f = document.forms[form_id];
	if (field_id==1) { // js_start_time
		if (!startDate) {
			var start_date = new Date();
			start_date.setTime(Date.parse(f.js_start_time.value));
			startDate = start_date;
		} else { // js_end_time
			if (!endDate) {
				var end_date = new Date();
				end_date.setTime(Date.parse(f.js_end_time.value));
				endDate = end_date;
			}
		}
	}
}

/**
*	Called onload to set report time internal date objects
*/
function init_dates(form_id) {
	parse_date_str(1, form_id); // set startDate
	parse_date_str(2, form_id); // set endDate
}

function check_form_update_values(f)
{
	parse_date_str(1, 'avail_form'); // set startDate
	parse_date_str(2, 'avail_form'); // set endDate
	var errors = false;

	// date validation
	var now = new Date();
	if (endDate > now) {
		if (!confirm("You have entered an End date in the future.\nClick OK to change this to current time or cancel to modify.")) {
			return false;
		} else {
			endDate = now;
		}
	}
	if (endDate) {
		if (endDate <= startDate) {
			alert("Please select a Start date BEFORE End date");
			document.forms['avail_form'].start_time.value='';
			document.getElementById('start_time_tmp').innerHTML = '';
			document.forms['avail_form'].start_time.focus();
			startDate = false;
			return false;
		}
	}
	if (startDate > now) {
		alert("Please select a Start date in the past");
		document.forms['avail_form'].start_time.value='';
		document.getElementById('start_time_tmp').innerHTML = '';
		document.forms['avail_form'].start_time.focus();
		startDate = false;
		return false;
	}

	// check for unique report names
	var report_name 	= $("#report_name").attr('value');
	var old_report_name = $("input[name=old_report_name]").val();
	var saved_avail_id 	= $("input[name=saved_avail_id]").val();
	var do_save_report 	= $('input[name=save_avail_settings]').attr('checked') ? 1 : 0;

	/*
	*	Only perform checks if:
	*		- Saved report exists
	*		- User checked the 'Save Report' checkbox
	*		- We are currently editing a report (i.e. have saved_avail_id)
	*/
	if ($('#avail_id') && do_save_report && saved_avail_id && report_name!=old_report_name && invalid_report_names && invalid_report_names.indexOf(report_name) != -1) {
		// Saved reports exists
		// trying to save an item with an existing name
		// check against all names EXCEPT current
		alert("You have entered a name for your report that already exists.\nPlease select a new name.");
		errors++;
		return false;
	} else {
		if (do_save_report && $.trim(report_name) == '') {
			alert('Please give your report a meaningful name.');
			errors++;
			return false;
		}
	}

	// display err_str if any
	if (!errors) {
		// check if report name is unique
		if(saved_avail_id == '' && invalid_report_names && invalid_report_names.indexOf(report_name) != -1)
		{
			if(!confirm("The entered name already exists. Press 'Ok' to replace the entry with this name"))
			{
				return false;
			}
		}
		return true;
	}

	return true;
}

function filter_table (phrase, _id){
	var words = phrase.value.toLowerCase().split(" ");
	var table = document.getElementById(_id);
	var ele;

	for (var r = 1; r < table.rows.length; r++){
		ele = table.rows[r].innerHTML.replace(/<[^>]+>/g,"");
		var displayStyle = 'none';
		for (var i = 0; i < words.length; i++) {
			if (ele.toLowerCase().indexOf(words[i])>=0)
				displayStyle = '';
			else {
				displayStyle = 'none';
				break;
			}
		}
		table.rows[r].style.display = displayStyle;
	}
}

function hideMe(elem)
{
	$('#' + elem).hide('slow');
}

function format_date_str(date) {
	var YY = date.getFullYear();
	var MM = date.getMonth() + 1;
	var DD = date.getDate();
	var hh = date.getHours();
	var mm = date.getMinutes();
	MM = MM<10 ? '0' + MM :MM;
	DD = DD<10 ? '0' + DD : DD;
	hh = hh<10 ? '0' + hh : hh;
	mm = mm<10 ? '0' + mm : mm;
	var ret_val = YY + '-' + MM + '-' + DD + ' ' + hh + ':' + mm;
	return ret_val;
}

function epoch_to_human(val){
	var datum = new Date(val * 1000);
	return datum;
}

/**
*	Uncheck form element by name
*	Used to set correct initial values
*	since some browser seem to cache checkbox state
*/
function uncheck(the_name, form_name)
{
	document.forms[form_name].elements[the_name].checked=false;
}

function toggle_field_visibility(val, theId) {
	if (val) {
		document.getElementById(theId).style.display='block';
	} else {
		document.getElementById(theId).style.display='none';
	}
}

function check_and_submit(f)
{
	if (f.avail_id.value!="") {
		f.submit();
		return true;
	} else {
		$('#is_scheduled').text('');
	}
	return false;
}

// use jquery if available
if(typeof window.jQuery != "undefined") {
	$(document).ready(function(){
		// changed to id-selector, to not clash with same named variables in other form
		start_time_bkup = $('#start_time').val();
		end_time_bkup = $('#end_time').val();

		$('#avail_save_information').hide();

		// create js date objects if passed from previous call
		if ($('input[name=js_end_time]').val())
			endDate = new Date($('input[name=js_end_time]').val());

		if ($('input[name=js_start_time]').val())
			startDate = new Date($('input[name=js_start_time]').val());

		var len = $('table#log-table tr').length;
		if (len<10) {
			$('#filterbox').hide();
		}
		$('#filterbox').keyup(function(){
			filter_table(this, 'log-table', 1);})
			.focus(function(){
				if(this.value=='Enter text to filter') {
					this.value='';
				}
		});

		if(typeof window.jQuery.tablesorter != "undefined") {
			$("#log-table").tablesorter({
			//	sortColumn: 'Start time',
				sortClassAsc: 'headerSortUp',		// class name for ascending sorting action to header
				sortClassDesc: 'headerSortDown',	// class name for descending sorting action to header
				highlightClass: 'highlight',
				headerClass: 'headerSortUp'			// class name for headers (th's)
			});
		}
		$("#show_scheduled").click(function(){toggle_edit()});
		setup_editable();
	});
	log("start_time_bkup = " + start_time_bkup);
	log("end_time_bkup = " + end_time_bkup);
}

function setup_editable()
{
	$(".iseditable").editable("/monitor/op5/auto-reports/save_item.php", {
		id   : 'elementid',
		name : 'newvalue',
		type : 'text',
		event : 'dblclick',
		width : 'auto',
		height : '14px',
		submit : 'OK',
		cancel : 'cancel',
		placeholder:'Double-click to edit'
	});
	$(".period_select").editable("/monitor/op5/auto-reports/save_item.php", {
		data : $('#autoreport_periods').text(),
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : 'OK',
		cancel : 'cancel'
	});
	$(".iseditable_txtarea").editable("/monitor/op5/auto-reports/save_item.php", {
		indicator : "<img src='icons/arrows/indicator.gif'>",
		id   : 'elementid',
		name : 'newvalue',
		type : 'textarea',
		event : 'dblclick',
		rows: '3',
		submit : 'OK',
		cancel : 'cancel',
		cssclass: "txtarea",
		placeholder:'Double-click to edit'
	});
}
