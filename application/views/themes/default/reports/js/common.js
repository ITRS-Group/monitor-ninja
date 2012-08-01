var _time_error = false;
var _time_error_start = false;
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
var nr_of_scheduled_instances = 0;
var is_populated = false; // flag list population when done
var current_obj_type = false; // keep track of what we are viewing
$(document).ready(function() {
	// because chrome, ie AND ff differs
	if($.browser.mozilla) {
		$('#availability_toolbox').css('marginTop', '-33px');
	}

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

	$('.fancybox').click(function() {
		// check if we should re-initialize datepicker
		// NOT for alert summary as this will result in an error
		if (_current_uri != 'summary/generate'){
			fancybox_datepicker();
		}
		init_timepicker();
	});

	init_regexpfilter();
	$('#filter_field').keyup(function() {
		if ($(this).attr('value') == '') {
			MyRegexp.resetFilter($("select[id$=_tmp]").filter(":visible").attr('id'));
			return;
		}
		MyRegexp.selectFilter($("select[id$=_tmp]").filter(":visible").attr('id'), this.value);
	});

	$('#clear_filter').click(function() {
		$('#filter_field').attr('value', '');
		MyRegexp.resetFilter($("select[id$=_tmp]").filter(":visible").attr('id'));
		$('#filter_field').focus();
	});

	var direct_link_visible = false;
	$('#current_report_params').click(function() {
		// make sure we always empty the field
		$('#link_container').html('');
		// .html('<form><input type="text" size="200" value="' + $('#current_report_params').attr('href') + '"></form>')
		if (!direct_link_visible) {
			$('#link_container')
				.html('<form class="directlink">'+_label_direct_link+' <input class="wide" type="text" value="'
					+ document.location.protocol + '//'
					+ document.location.host
					+ $('#current_report_params').attr('href')
					+ '"></form>')
				.css('position', 'absolute')
				.css('top', 20)
				.css('left', '1%')
				.show();
				direct_link_visible = true;
		} else {
			$('#link_container').hide();
			direct_link_visible = false;
		}
		return false;
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
});


function setup_editable(mode)
{
	var mode_str = '';
	if (mode == 'fancy') {
		var mode_str = '#fancybox-content ';
	}
	var save_url = _site_domain + _index_page + "/reports/save_schedule_item/";
	$(mode_str +".iseditable").editable(save_url, {
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
	$(mode_str +".period_select").editable(save_url, {
		data : $('#autoreport_periods').text(),
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : _ok_str,
		cancel : _cancel_str
	});
	$(mode_str +".iseditable_txtarea").editable(save_url, {
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
	$(mode_str +".report_name").editable(save_url, {
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

var loadimg = new Image(16,16);
loadimg.src = _site_domain + 'application/media/images/loading_small.gif';

function send_report_now(type, id)
{
	if (type=='' || id =='') {
		// missing info
		return false;
	}

	var html_id = 'send_now_' + type + '_' + id;

	$('#' + html_id)
		.css('background', 'url(' + loadimg.src + ') no-repeat scroll 0 0 transparent')
		.css('height', '16px')
		.css('width', '16px')
		.css('float', 'left');

	// since we now support reports generated in
	// other controllers than the reports controller,
	// we have to let the correct controller handle
	// the actual creation and sending of the report.
	var controller = (typeof _reports_link != 'undefined') ? _reports_link : 'reports';
	if (type == 'summary') {
		controller = 'summary';
	}

	$.ajax({
		url: _site_domain + _index_page + '/' + controller + '/generate',
		type: 'POST',
		data: {type: type, schedule_id: id},
		success: function(data) {
			if (data == '' || !data.error) {
				jgrowl_message(_reports_schedule_send_ok, _reports_success);
			} else {
				if(data.error) {
					jgrowl_message(_reports_schedule_send_error + ': ' + data.error, _reports_error);
				} else {
					jgrowl_message(_reports_schedule_send_error, _reports_error);
				}
				setTimeout(function() {restore_sendimg(html_id)}, 1000);
			}
		},
		dataType: 'json'
	});

}

function schedule_delete(id, remove_type)
{
	if (!confirm(_reports_confirm_delete_schedule)) {
		return false;
	}

	var img_src = $('#' + id + " img").attr('src');
	var in_id = id;

	$('#' + in_id + ' img').attr('src', loadimg.src);

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
				remove_schedule(id, remove_type);
			} else {
				jgrowl_message(data, _reports_error);
				setTimeout('hide_response()', time);
				setTimeout(function() {restore_delimg(in_id, img_src)}, 1000);
			}
		}
	});
}

function restore_sendimg(id)
{
	var old_icon = _site_domain + _theme_path + "icons/16x16/send-report.png";
	$('#' + id)
		.css('background', 'url(' + old_icon + ') no-repeat scroll 0 0 transparent')
		.css('height', '16px')
		.css('width', '16px').css('float', 'left');

}

function restore_delimg(id, src)
{
	$('#' + id + ' img').attr('src', src);
}

function remove_schedule(id, remove_type)
{
	var time = 3000;

	update_visible_schedules(true);

	// remove row for deleted ID (both in fancybox and in original table)
	$('#report-' + id).remove();
	$('#fancybox-content #report-' + id).remove();

	// fancybox workaound
	if (remove_type == 'summary' && $('#fancybox-content #schedule_report_table').is(':visible')) {
		nr_of_scheduled_instances = $('#fancybox-content #schedule_report_table tr').not('#schedule_header').length;
	}
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
			$.fancybox.close();
		}
	}

	if (remove_type!='' && remove_type != 'undefined') {
		if ($('#' + remove_type + '_scheduled_reports_table tbody').not('.no-result').length == 0) {
			$('#' + remove_type + '_headers').hide();
			$('#' + remove_type + '_no_result').show();
		}
	}

	jgrowl_message(_reports_schedule_deleted, _reports_success);
	setTimeout('hide_response()', time);
}

function fancybox_datepicker()
{
	var datepicker_enddate = (new Date()).asString();
	$('.date-pick').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});

	if ($('#fancybox-content #cal_start').attr('value')) {
		var ds = Date.fromString($('#fancybox-content #cal_start').attr('value'));
		$('#fancybox-content #cal_end').dpSetStartDate(ds.asString());
	}
	if ($('#fancybox-content #cal_end').attr('value')) {
		var ds = Date.fromString($('#fancybox-content #cal_end').attr('value'));
		$('#fancybox-content #cal_start').dpSetEndDate(ds.asString());
	}

	$('.datepick-start').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				startDate = d.asString();
				$('#fancybox-content #start_time').attr('value', d.asString());
				$("input[name=start_time]").attr('value', d.asString());
				$('#fancybox-content #cal_end').dpSetStartDate(d.asString());
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
				endDate = d.asString();
				$('#fancybox-content #end_time').attr('value', d.asString());
				$("input[name=end_time]").attr('value', d.asString());
				$('#fancybox-content #cal_start').dpSetEndDate(d.asString());
			}
		}
	);
}


function init_datepicker()
{
	// datePicker Jquery plugin
	var datepicker_enddate = (new Date()).asString();
	$('.date-pick').filter(':visible').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});
	$('#cal_start').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				startDate = d.asString();
				$('#start_time').attr('value', d.asString());
				$('#cal_end').dpSetStartDate(d.asString());
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
				$('#cal_start').dpSetEndDate(d.asString());
				$('#end_time').attr('value', d.asString());
				endDate = d.asString();
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
			init_datepicker();

			if (update == '') {
				$('input[name=start_time]').attr('value', '');
				$('input[name=end_time]').attr('value', '');
			}
		} else {
			$("#display").show();
			js_print_date_ranges();
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
Image1.src = _site_domain + 'application/media/images/loading.gif';

/**
*	Show a progress indicator to inform user that something
*	is happening...
*/
function show_progress(the_id, info_str) {
	$("#" + the_id).html('<img id="progress_image_id" src="' + Image1.src + '"> <em>' + info_str +'</em>').show();
}

function get_members(val, type, no_erase) {
	if (type=='') return;
	is_populated = false;
	show_progress('progress', _wait_str);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "group_member/";
	var data = {input: val, type: type};
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
		var val = json_data[i];
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
		if ($('#fancybox-content').is(':visible')) {
			$('tr#state_options').show();
		}
	} else {
		$('#state_options').hide();
		if ($('#fancybox-content').is(':visible')) {
			$('tr#state_options').hide();
		}
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
			$(this).children('option').attr('selected', 'selected');
		} else {
			$(this).children('option').attr('selected', false);
		}
	});

	// unselect the rest
	$('.multiple[name*=' + nosave_suffix + ']').each(function() {
		$(this).children('option').attr('selected', false);
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
	var fancy_str = '';
	var curval_starttime = '';
	var curval_endtime = '';

	if ($('#fancybox-content').is(':visible')) {
		fancy_str = '#fancybox-content ';
	}
	var rpt_type = $("input[name=report_type]").val();
	if (rpt_type == '' || rpt_type == undefined) {
		var rpt_type = $("select[name=report_type]").val();
	}
	if ($(fancy_str + "#report_period").val() == 'custom') {
		if ($('input[name=type]').val() != 'sla') {
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
			$(fancy_str + ".time_start").each(function() {
				curval_starttime = $(this).val();
			});
			$(fancy_str + ".time_end").each(function() {
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
		} else {
			// verify that we have years and month fields
			if ($('#start_year').val() == '' || $('#start_month').val() == ''
			|| $('#end_year').val() == '' || $('#end_month').val() == '') {
				errors++;
				//@@@Fixme: Add translated string
				err_str += "<li>Please select year and month for both start and end. ";
				err_str += "<br />Please note that SLA reports can only be generated for previous months</li>";
			}
		}
	}

	if ($("#" + field_obj.map[rpt_type]).is('select') && $("#" + field_obj.map[rpt_type] + ' option').length == 0) {
		errors++;
		err_str += "<li>" + _reports_err_str_noobjects + ".</li>";
	}

	if($('#display_host_status').is('visible') && !$('#display_host_status input[type="checkbox"]:checked').length) {
		errors++;
		err_str += "<li>" + _reports_err_str_nostatus + ".</li>";
	} else if($('#display_service_status').is('visible') && !$('#display_service_status input[type="checkbox"]:checked').length) {
		errors++;
		err_str += "<li>" + _reports_err_str_nostatus + ".</li>";
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

	// create array prototype to sole the lack of in_array() in javascript
	Array.prototype.has = function(value) {
		var i;
		for (var i = 0, loopCnt = this.length; i < loopCnt; i++) {
			if (this[i] === value) {
				return true;
			}
		}
		return false;
	};

	var report_name 	= $(fancy_str + "input[name=report_name]").attr('value');
	report_name = $.trim(report_name);
	var saved_report_id = $("input[name=saved_report_id]").attr('value');
	var do_save_report 	= $(fancy_str + 'input[name=save_report_settings]').is(':checked') ? 1 : 0;

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
						return false;
					}
				}
			}
		});
	} else if (do_save_report && report_name == '') {
		// trying to save a report without a name
		errors++;
		err_str += "<li>" + _reports_name_empty + "</li>";
		jgrowl_message(_reports_name_empty, _error_header);
	}

	// display err_str if any
	if (!errors) {
		$('#response').html('');

		// check if report name is unique
		if(report_name && saved_report_id == '' && invalid_report_names && invalid_report_names.has(report_name))
		{
			if(!confirm(_reports_error_name_exists_replace))
			{
				return false;
			}
		}

		if (curval_starttime) {
			curval_starttime = ' ' + curval_starttime;
		}
		if (curval_endtime) {
			curval_endtime = ' ' + curval_endtime;
		}
		if ($('#fancybox-content').is(':visible')) {
			$('#fancybox-content #start_time').attr('value', $('#fancybox-content #cal_start').attr('value') + curval_starttime);
			$('#fancybox-content #end_time').attr('value', $('#fancybox-content #cal_end').attr('value') + curval_endtime);
		} else {
			$("input[name=start_time]").attr('value', $("input[name=cal_start]").attr('value') + curval_starttime);
			$("input[name=end_time]").attr('value', $("input[name=cal_end]").attr('value') + curval_endtime);
		}
		$('#response').hide();
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

function show_message(class_name, msg)	{
	$('#response').show().html('<ul class="' + class_name + '">' + msg + '<br /></ul>');
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
	if (report_id == '' || report_id == undefined) {
		report_id = $('#report_id').val();
	}
	var fatal_err_str = _reports_fatal_err_str;// + "<br />";
	$('.schedule_error').hide();

	var err_str = "";
	var errors = 0;
	if (interval == '' || !interval) {
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
	if ($("#time_start").is(':visible')) {
		$("#time_start, #time_end").timePicker();
	} else {
		if ($("#fancybox-content #time_start").is(':visible')) {
			$("#fancybox-content #time_start, #fancybox-content #time_end").timePicker();
		} else {
			return false;
		}
	}

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
		case 'lastyear':
		case 'last12months':
			disable_months(0, 12);
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
				$('#month_' + i).attr('disabled', false).css('bgcolor', sla_month_enabled_color);
			}
	}
}


function disable_months(start, end)
{
	var disabled_state 		= false;
	var not_disabled_state 	= false;
	var col 				= false;
	start 	= Number(start);
	end 	= Number(end);
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
	} else {
		setTimeout('check_custom_months()', 1000);
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

function toggle_label_weight(val, the_id)
{
	var val_str = val ? 'bold' : 'normal';
	$('#' + the_id + ', label[for='+the_id+']').css('font-weight', val_str);
	$('#fancybox-content #' + the_id + ', label[for='+the_id+']').css('font-weight', val_str);
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
	var tmp_field = $("select[name='" + tmp_fields.map[field_str]+ '[]' + "']");
	var removed_items = new Array();
	var i = 0;

	$("select[name='" + field + "'] option").each(function() {
		var this_item = $(this).val();
		if (tmp_field.containsOption(this_item)) {
			tmp_field.removeOption(this_item);
		} else {
			//$("select[name='" + field + "']").removeOption(this_item);
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

function trigger_schedule_save(f)
{
	// ajax post form options
	show_progress('progress', _wait_str);
	// fetch values from form
	var report_id = 0; // new schedule has no ID
	var rep_type = $('input[name=type]').attr('value');
	if (!rep_type) {
		rep_type = $('#fancybox-content input[name=type]').attr('value');
	}
	var saved_report_id = $('#fancybox-content #saved_report_id').attr('value');
	var period = $('#fancybox-content #period').attr('value');
	var period_str = $('#fancybox-content #period option:selected').text();
	var recipients = $('#fancybox-content #recipients').attr('value');
	var filename = $('#fancybox-content #filename').attr('value');
	var local_persistent_filepath = $('#fancybox-content #local_persistent_filepath').attr('value');
	var description = $('#fancybox-content #description').attr('value');

	$.ajax({
		url:_site_domain + _index_page + '/reports/schedule',
		type: 'POST',
		data: {
			report_id: report_id,
			rep_type: rep_type,
			saved_report_id: saved_report_id,
			period: period,
			recipients: recipients,
			filename: filename,
			local_persistent_filepath: local_persistent_filepath,
			description: description
		},
		success: function(data) {
			if (data.error) {
				jgrowl_message(data.error, _reports_error);
			} else {
				// @todo: remove this row, we should fetch that information on each click
				// on that button since that data might be a bad cache, i.e. it's NEVER guaranteed
				// to be 1:1 vs the stored data
				$('#schedule_report_table').append(create_new_schedule_rows(data.result.id));
				jgrowl_message(_reports_schedule_create_ok, _reports_success);
				$('#show_schedule').show(); // show the link to view available schedules
				$.fancybox.close();
			}
		},
		dataType: 'json'
	});

	setTimeout('delayed_hide_progress()', 1000);
	return false;
}

// used from options template
function create_new_schedule_rows(id)
{
	var return_str = '';
	var rep_type = $('input[name=type]').attr('value');

	var saved_report_id = $('#fancybox-content #saved_report_id').attr('value');
	if (saved_report_id == '')
		saved_report_id = $('#saved_report_id').attr('value');

	var period = $('#fancybox-content #period').attr('value');
	if (period == '')
		period = $('#period').attr('value');

	var period_str = $('#fancybox-content #period option:selected').text();
	if (period_str == '')
		period_str = $('#period option:selected').text();

	var recipients = $('#fancybox-content #recipients').attr('value');
	if (recipients == '')
		recipients = $('#recipients').attr('value');

	var filename = $('#fancybox-content #filename').attr('value');
	if (filename == '')
		filename = $('#filename').attr('value');

	var local_persistent_filepath = $('#fancybox-content #local_persistent_filepath').attr('value');
	if (local_persistent_filepath == '')
		local_persistent_filepath = $('#local_persistent_filepath').attr('value');

	var description = $('#fancybox-content #description').attr('value');
	if (description == '')
		description = $('#description').attr('value');
	if (description == '')
		description = '&nbsp;';

	return_str += '<tr id="report-' + id + '" class="odd">';
	return_str += '<td class="period_select" title="' + _reports_edit_information + '" id="period_id-' + id + '">' + period_str + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="recipients-' + id + '">' + recipients + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="filename-' + id + '">' + filename + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="local_persistent_filepath-' + id + '">' + local_persistent_filepath + '</td>';
	return_str += '<td class="iseditable_txtarea" title="' + _reports_edit_information + '" id="description-' + id + '">' + description + '</td>';
	return_str += '<td><form><input type="button" class="send_report_now" id="send_now_' + rep_type + '_' + id + '" title="' + _reports_send_now + '" value="&nbsp;"></form>';
	return_str += '<div class="delete_schedule" onclick="schedule_delete(' + id + ', \'' + rep_type + '\');" id="delid_' + id + '"><img src="' + _site_domain + _theme_path + 'icons/16x16/delete-schedule.png" class="deleteimg" /></div></td></tr>';
	update_visible_schedules(false);
	return return_str;
}

var avail_schedules = 0;
var sla_schedules = 0;
var summary_schedules = 0;
function update_visible_schedules(count)
{
	if ($('#avail_scheduled_reports_table').is(':visible')) {
		avail_schedules = $('#avail_scheduled_reports_table tbody tr').filter(':visible').not('.no-result').length;
		if (count) {
			avail_schedules--;
		}
	}

	if ($('#sla_scheduled_reports_table').is(':visible')) {
		sla_schedules = $('#sla_scheduled_reports_table tbody tr').filter(':visible').not('.no-result').length;
		if (count) {
			sla_schedules--;
		}
	}

	if ($('#summary_scheduled_reports_table').is(':visible')) {
		summary_schedules = $('#summary_scheduled_reports_table tbody tr').filter(':visible').not('.no-result').length;
		if (count) {
			summary_schedules--;
		}
	}

	// special case for summary reports in fancybox
	if ($('#fancybox-content #summary_scheduled_reports_table').is(':visible')) {
		summary_schedules = $('#summary_scheduled_reports_table tbody tr').filter(':visible').not('.no-result').length;
		if (count) {
			summary_schedules--;
		}
	}

	if ($('#schedule_report_table').is(':visible')) {
		// setup and options templates
		if ($('#fancybox-content').is(':visible')) {
			// check the fancybox layer (options template)
			nr_of_scheduled_instances = $('#fancybox-content #schedule_report_table tr').not('#schedule_header').length;
		} else {
			nr_of_scheduled_instances = $('#schedule_report_table tr').not('#schedule_header').length;
		}
		if (count) {
			nr_of_scheduled_instances--;
		}
	}
}

jQuery.extend(
	jQuery.expr[':'], {
		regex: function(a, i, m, r) {
			var r = new RegExp(m[3], 'i');
			return r.test(jQuery(a).text());
		}
	}
);

/**
*	Regexp filter that (hopefully) works for all browsers
*	and not just FF
*/
function init_regexpfilter() {
	MyRegexp = new Object();
	MyRegexp.selectFilterData = new Object();
	MyRegexp.selectFilter = function(selectId, filter) {
		var list = document.getElementById(selectId);
		if(!MyRegexp.selectFilterData[selectId]) { //if we don't have a list of all the options, cache them now'
			MyRegexp.selectFilterData[selectId] = new Array();
			for(var i = 0; i < list.options.length; i++) MyRegexp.selectFilterData[selectId][i] = list.options[i];
		}
		list.options.length = 0;   //remove all elements from the list
		var r = new RegExp(filter, 'i');
		for(var i = 0; i < MyRegexp.selectFilterData[selectId].length; i++) { //add elements from cache if they match filter
			var o = MyRegexp.selectFilterData[selectId][i];
			//if(o.text.toLowerCase().indexOf(filter.toLowerCase()) >= 0) list.add(o, null);
			if(r.test(o.text)) list.add(o, null);
		}
	}
	MyRegexp.resetFilter = function(selectId) {
		if (typeof MyRegexp.selectFilterData[selectId] == 'undefined' || !MyRegexp.selectFilterData[selectId].length) return;
		var list = document.getElementById(selectId);
		list.options.length = 0;   //remove all elements from the list
		for(var i = 0; i < MyRegexp.selectFilterData[selectId].length; i++) { //add elements from cache if they match filter
			var o = MyRegexp.selectFilterData[selectId][i];
			if (!o.parentNode)
				list.add(o, null);
		}

	};
}
