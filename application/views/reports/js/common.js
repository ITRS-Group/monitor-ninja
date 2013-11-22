var invalid_report_names = '';
var current_filename;
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
var nr_of_scheduled_instances = 0;
var current_obj_type = false; // keep track of what we are viewing
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

	$(".fancybox").fancybox({
		'overlayOpacity'	:	0.7,
		'overlayColor'		:	'#ffffff',
		'hideOnContentClick' : false,
		'autoScale':true,
		'autoDimensions': true,
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
				.html('<form>'+_label_direct_link+' <input class="wide" type="text" value="'
					+ document.location.protocol + '//'
					+ document.location.host
					+ $('#current_report_params').attr('href')
					+ '"></form>')
				.css('position', 'absolute')
				.css('top', this.offsetHeight + this.offsetTop + 5)
				.css('right', '0')
				.show();
				direct_link_visible = true;
		} else {
			$('#link_container').hide();
			direct_link_visible = false;
		}
		return false;
	});

	$('#save_report').click(function() {
		if (!direct_link_visible) {
			$('#save_report_form')
				.css('position', 'absolute')
				.css('top', this.offsetHeight + this.offsetTop + 5)
				.css('right', '0')
				.show()
				.find('input[name=report_name]')
					.map(function() {
						var input = this;
						if(input.value == "") {
							input.focus();
						}
					});
				direct_link_visible = true;
		} else {
			$('#save_report_form').hide();
			direct_link_visible = false;
		}
		return false;
	});

	$("#report_id").bind('change', function() {
		$("#saved_report_form").trigger('submit');
	});

	$('.save_report_btn').parents('form').submit(function(ev) {
		ev.preventDefault();
		loopElements();
		var form = $(this);
		if (!(check_form_values(this[0]))) {
			return;
		}
		var btn = form.find('.save_report_btn');
		btn.after(loadimg);
		$.ajax({
			url: form[0].action,
			type: form[0].method,
			data: form.serialize(),
			complete: function() {
				btn.parent().find('img:last').remove();
			},
			success: function(data, status_msg, xhr) {
				if (data == null) {
					jgrowl_message(xhr.responseText, _reports_error);
					return;
				}
				jgrowl_message(data.status_msg, _reports_success);
				if (!btn[0].form.report_id)
					$('form').append('<input type="hidden" name="report_id" value="'+data.report_id+'"/>');
				else
					$('#save_report_form').hide();
			},
			error: function(data) {
				jgrowl_message(data.responseText, _reports_error);
				btn.parent().find('img:last').remove();
			},
			dataType: 'json'
		});
	});

	$('#report_type').on('change', function() {
		var value = this.value;
		set_selection(value);
		get_members(value);
	}).each(function() {
		var val = $(this).val();
		if (window['_report_data']) {
			expand_and_populate(_report_data);
		} else if (val) {
			set_selection(val);
			get_members(val);
		}
	});
	$('#sel_report_type').on('click', function() {
		var value = this.form.report_type.value;
		set_selection(value);
		get_members(value);
	});

	var rpcust = function() {
		if (this.value == 'custom')
			js_print_date_ranges();
	};
	$('#report_period').on('change', rpcust).each(rpcust);

	$("#delete_report").click(confirm_delete_report);
});

var loadimg = new Image(16,16);
loadimg.src = _site_domain + 'application/media/images/loading_small.gif';

function init_datepicker()
{
	// datePicker Jquery plugin
	var datepicker_enddate = (new Date()).asString();
	$('.date-pick').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});
	$('#cal_start').on(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_end').dpSetStartDate(d.asString());
			}
		}
	);
	$('#cal_end').on(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_start').dpSetEndDate(d.asString());
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

function js_print_date_ranges(the_year, type, item)
{
	show_progress('progress', _wait_str);
	the_year = typeof the_year == 'undefined' ? 0 : the_year;
	type = typeof type == 'undefined' ? '' : type;
	item = typeof item == 'undefined' ? '' : item;

	if (!the_year && type!='' && item!='') {
		return false;
	}
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_date_ranges/";
	var data = {the_year: the_year, type: type, item: item};

	if (type !='') {
		empty_list(type + '_month');
	}

	set_selected_period(type);

	$.ajax({
		url: url,
		type: 'GET',
		data: data,
		dataType: 'json',
		success: function(data) {
			if (data != '') {
				// OK, continue
				if (data['start_year']) {
					for (i in data['start_year']) {
						$('#start_year').addOption(data['start_year'][i], data['start_year'][i]);
					}
					$('#start_year').find('option:first').attr('selected', 'selected');
				}

				if (data['end_year']) {
					for (i in data['end_year']) {
						$('#end_year').addOption(data['end_year'][i], data['end_year'][i]);
					}
					$('#end_year').find('option:first').attr('selected', 'selected');
				}

				if (data['type_item']) {
					for (i in data['type_item']) {
						$('#' + data['type_item'][i][0]).addOption(data['type_item'][i][2], data['type_item'][i][1]);
					}
					$('#' + data['type_item'][0][0]).find('option:first').attr('selected', 'selected');
				}

			} else {
				// error
				jgrowl_message('Unable to fetch date ranges...', _reports_error);
			}
			check_custom_months();
		}
	});
}

function show_calendar(val, update) {
	if (val=='custom') {
		$("#display").show();

			init_timepicker();
			init_datepicker();

			if (update == '') {
				$('input[name=start_time]').attr('value', '');
				$('input[name=end_time]').attr('value', '');
			}
	} else {
		$("#display").hide();
	}
	disable_sla_fields(val);
}

function set_selection(val) {
	if ($.inArray(val, ['servicegroups', 'hostgroups', 'services', 'hosts']) === -1)
		val = 'hostgroups'; // Why? Because I found it like this
	$('*[data-show-for]').hide()
	$('*[data-show-for~='+val+']').show()
}

function get_members(type, cb) {
	if (type=='') return;
	var field_name = false;
	var empty_field = false;

	field_name = new field_maps3().map[type];
	empty_field = new field_maps().map[type];

	show_progress('progress', _wait_str);
	$.ajax({
		url: _site_domain + _index_page + '/ajax/group_member',
		data: {type: type},
		error: function(data) {
			jgrowl_message('Unable to fetch objects: ' + data.responseText, _reports_error);
		},
		success: function(data) {
			empty_list(field_name);
			populate_options(field_name, empty_field, data);
			empty_list(empty_field);
			if(typeof cb == 'function')
				cb();
			$('#progress').hide();
		},
		dataType: 'json'
	});
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

	var select = $('#' + field);
	select.children().each(function() {
		if (this.value)
			$(this).remove();
	});
	// truncate select list
	//$("#"+field).removeOption(/./);
}

/**
*	Populate HTML select list with supplied JSON data
*/
function populate_options(tmp_field, field, json_data)
{
	show_progress('progress', _wait_str);
	var fragment = document.createDocumentFragment();
	for (var i = 0; i < json_data.length; i++) {
		var option = document.createElement("option");
		option.setAttribute("value", json_data[i]);
		option.appendChild(document.createTextNode(json_data[i]));
		fragment.appendChild(option);
	}
	document.getElementById(tmp_field.replace('[', '\\[').replace(']', '\\]')).appendChild(fragment);
}

/**
*	Re-populate report_period select field
*/
function populate_report_periods(json_data)
{
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

function setup_hide_content(d) {
	if(d.length < 1) {
		return;
	}
	$('#' + d).hide();
}

function hide_response() {setup_hide_content('response');}

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

function check_form_values(form)
{
	if (!form)
		form = document.documentElement;
	var errors = 0;
	var err_str = '';
	var field_obj = new field_maps();
	var cur_start = '';
	var cur_end = '';

	var rpt_type = $("input[name=report_type]", form).val();
	if (rpt_type == '' || rpt_type == undefined) {
		var rpt_type = $("select[name=report_type]", form).val();
	}
	if ($("#report_period", form).val() == 'custom') {
		if ($('input[name=type]', form).val() != 'sla') {
			// date validation
			cur_start = Date.fromString($("input[name=cal_start]", form).val());
			var time =  $(".time_start", form).val().split(':');
			cur_start.addHours(time[0]);
			cur_start.addMinutes(time[1]);
			cur_end = Date.fromString($("input[name=cal_end]", form).val());
			time = $(".time_end", form).val().split(':');
			cur_end.addHours(time[0]);
			cur_end.addMinutes(time[1]);
			var now = new Date();
			if (!cur_start || !cur_end) {
				if (!cur_start) {
					errors++;
					err_str += "<li>" + _reports_invalid_startdate + ".</li>";
				}
				if (!cur_end) {
					errors++;
					err_str += "<li>" + _reports_invalid_enddate + ".</li>";
				}
			} else {
				if (cur_end > now) {
					if (!confirm(_reports_enddate_infuture)) {
						return false;
					} else {
						cur_end = now;
					}
				}
			}

			if (cur_end < cur_start) {
				errors++;
				err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
				$(".datepick-start", form).addClass("time_error");
				$(".datepick-end", form).addClass("time_error");
			} else {
				$(".datepick-start", form).removeClass("time_error");
				$(".datepick-end", form).removeClass("time_error");
			}
		} else {
			// verify that we have years and month fields
			if ($('#start_year', form).val() == '' || $('#start_month', form).val() == ''
			|| $('#end_year', form).val() == '' || $('#end_month', form).val() == '') {
				errors++;
				//@@@Fixme: Add translated string
				err_str += "<li>Please select year and month for both start and end. ";
				err_str += "<br />Please note that SLA reports can only be generated for previous months</li>";
			}
			else {
				// remember: our months are 1-indexed
				cur_start = new Date(0);
				cur_start.setYear($("select[name=start_year]", form).val());
				cur_start.addMonths(Number($("select[name=start_month]", form).val()) - 1);

				cur_end = new Date(0);
				cur_end.setYear($("select[name=end_year]", form).val());
				cur_end.addMonths(Number($("select[name=end_month]", form).val()));
			}

			if (cur_end < cur_start) {
				errors++;
				err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
				$(".datepick-start", form).addClass("time_error");
				$(".datepick-end", form).addClass("time_error");
			} else {
				$(".datepick-start", form).removeClass("time_error");
				$(".datepick-end", form).removeClass("time_error");
			}
		}
	}

	if ($('input[name=report_mode]:checked', form).val() != 'standard' && !$('#show_all', form).is(':checked') && $("#" + field_obj.map[rpt_type], form).is('select') && $("#" + field_obj.map[rpt_type] + ' option', form).length == 0) {
		errors++;
		err_str += "<li>" + _reports_err_str_noobjects + ".</li>";
	}

	if ($("#enter_sla", form).is(":visible")) {
		// check for sane SLA values
		var red_error = false;
		var max_val = 100;
		var nr_of_slas = 0;

		for (i=1;i<=12;i++) {
			var field_name = 'month_' + i;
			var input = $('input[id="' + field_name + '"]', form);
			var value = input.attr('value');
			value = value.replace(',', '.');
			if (value > max_val || isNaN(value)) {
				input.css('background', sla_month_error_color);
				errors++;
				red_error = true;
			} else {
				if (value != '') {
					nr_of_slas++;
				}
				if (input.attr('disabled'))
					input.css('background', sla_month_disabled_color);
				else
					input.css('background', sla_month_enabled_color);
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

	var report_name 	= $("input[name=report_name]", form).attr('value');
	report_name = $.trim(report_name);
	var saved_report_id = $("input[name=saved_report_id]", form).attr('value');
	var do_save_report 	= $('input[name=save_report_settings]', form).is(':checked') ? 1 : 0;

	/*
	*	Only perform checks if:
	*		- Saved report exists
	*		- User checked the 'Save Report' checkbox
	*		- We are currently editing a report (i.e. have saved_report_id)
	*/
	if ($('#report_id', form) && do_save_report && saved_report_id) {
		// Saved reports exists
		$('#report_id option', form).each(function(i) {
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
		$('#response', form).html('');

		// check if report name is unique
		if(report_name && saved_report_id == '' && invalid_report_names && invalid_report_names.has(report_name))
		{
			if(!confirm(_reports_error_name_exists_replace))
			{
				return false;
			}
		}

		$('#response', form).hide();
		return true;
	}

	// clear all style info from progress
	var resp = $('#response', form);
	if (!resp.length)
		resp = $('#response');
	resp.attr("style", "");
	resp.html("<ul class='alert error'>" + err_str + "</ul>");
	window.scrollTo(0,0); // make sure user sees the error message
	return false;
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

// init timepicker once it it is shown
function init_timepicker()
{
	$("#time_start, #time_end").timePicker();
}

function disable_sla_fields(report_period)
{
	if (!$('#month_1').length)
		return;
	var now = new Date();
	var this_month = now.getMonth()+1;
	switch (report_period) {
		case 'thisyear':
			// weird as it seems, the following call actually ENABLES
			// all months. If not, we could end up with all months being
			// disabled for 'thisyear'
			disable_months(0, 12);
			for (i=this_month + 1;i<=12;i++)
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
	// not SLA?
	if (!f['start_month'])
		return;
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

function missing_objects()
{
	this.objs = [];
}

missing_objects.prototype.add = function(name)
{
	this.objs.push(name);
}

missing_objects.prototype.display_if_any = function()
{
	if (!this.objs.length)
		return;

	var info_str = _reports_missing_objects + ": ";
	info_str += "<ul><li><img src=\"" + _site_domain + "application/views/icons/arrow-right.gif" + "\" /> " + this.objs.join('</li><li><img src="' + _site_domain + 'application/views/icons/arrow-right.gif' + '" /> ') + '</li></ul>';
	info_str += _reports_missing_objects_pleaseremove;
	info_str += '<a href="#" id="hide_response" onclick="hideMe(\'response\')" style="position:absolute;top:8px;left:700px;">Close <img src="' + _site_domain + '' + 'application/views/icons/12x12/cross.gif" /></a>';
	$('#response')
		.css('background','#f4f4ed url(' + _site_domain + 'application/views/icons/32x32/shield-info.png) 7px 7px no-repeat')
		.css("position", "relative")
		.css('top', '0px')
		.css('width','748px')
		.css('left', '0px')
		.css('padding','15px 2px 5px 50px')
		.css('margin-left','5px')
		.html(info_str);
}

function confirm_delete_report()
{
	var btn = $(this);
	var id = $("#report_id").attr('value')

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
				var a = document.createElement("a");
				a.href = window.location.href;
				if(a.search && a.search.indexOf("report_id="+id) !== -1) {
					window.location.href = a.search.replace(new RegExp("report_id="+id+"&?"), "");
				}
			},
			error: function() {
				jgrowl_message(_reports_error, _reports_error);
			},
			dataType: 'json'
		});
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
		if(!MyRegexp.selectFilterData[selectId]) {
			//if we don't have a list of all the options, cache them now'
			MyRegexp.selectFilterData[selectId] = new Array();
			for(var i = 0; i < list.options.length; i++)
				MyRegexp.selectFilterData[selectId][i] = list.options[i];
		}
		list.options.length = 0;   //remove all elements from the list
		var r = new RegExp(filter, 'i');
		for(var i = 0; i < MyRegexp.selectFilterData[selectId].length; i++) {
			//add elements from cache if they match filter
			var o = MyRegexp.selectFilterData[selectId][i];
			//if(o.text.toLowerCase().indexOf(filter.toLowerCase()) >= 0) list.add(o, null);
			if(!o.parentNode && r.test(o.text)) list.add(o, null);
		}
	}
	MyRegexp.resetFilter = function(selectId) {
		if (typeof MyRegexp.selectFilterData[selectId] == 'undefined' || !MyRegexp.selectFilterData[selectId].length)
			return;
		var list = document.getElementById(selectId);
		list.options.length = 0;   //remove all elements from the list
		for(var i = 0; i < MyRegexp.selectFilterData[selectId].length; i++) {
			//add elements from cache if they match filter
			var o = MyRegexp.selectFilterData[selectId][i];
			if (!o.parentNode)
				list.add(o, null);
		}

	};
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
	if (!field_str)
		field_str = 'hostgroups';
	$('#report_type').val(field_str);
	set_selection(field_str);
	get_members(field_str, function() {
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
	show_calendar(reportObj.report_period);
}
