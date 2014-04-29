var current_filename;
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
var nr_of_scheduled_instances = 0;
var current_obj_type = false; // keep track of what we are viewing
$(document).ready(function() {
	// handle the move-between-lists-button (> + <) and double click events
	function move_right() {
		var selects = $(this).parent().parent().find('select');
		moveAndSort(selects.filter(':first'), selects.filter(':last'));
	}
	function move_left() {
		var selects = $(this).parent().parent().find('select');
		moveAndSort(selects.filter(':last'), selects.filter(':first'));
	}
	$('.arrow-right').click(move_right);
	$('.arrow-left').click(move_left);
	$('#hostgroup_tmp, #servicegroup_tmp, #host_tmp, #service_tmp, #objects_tmp').dblclick(move_right);
	$('#hostgroup, #servicegroup, #host_name, #service_description, #objects').dblclick(move_left);

	$('#response').on('click', "#hide_response", function() {
		$('#response').hide('slow');
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
					$.notify(_reports_error + ": " + xhr.responseText, {'sticky': true});
					return;
				}
				jgrowl_message(data.status_msg, _reports_success);
				if (!btn[0].form.report_id)
					$('form').append('<input type="hidden" name="report_id" value="'+data.report_id+'"/>');
				else
					$('#save_report_form').hide();
			},
			error: function(data) {
				$.notify(_reports_error + ": " + data.responseText, {'sticky': true});
				btn.parent().find('img:last').remove();
			},
			dataType: 'json'
		});
	});

	$('select[name=report_type]').on('change', function() {
		var value = this.value;
		set_selection(value);
		get_members(value, function(all_names) {
			populate_options($('#objects_tmp'), $('#objects'), all_names);
		});
	}).each(function() {
		var value = this.value;
		set_selection(value);
		get_members(value, function(all_names) {
			populate_options($('#objects_tmp'), $(), all_names);
			var tmp = $('#objects_tmp');
			var mo = new missing_objects();
			var objs = $('#objects');
			var elems = objs.children();
			for (var i = 0; i < elems.length; i++) {
				var prop = elems[i];
				if (tmp.containsOption(prop.value)) {
					tmp.removeOption(prop.value);
				} else {
					mo.add(prop.value);
					objs.removeOption(prop.value);
				}
			}
			mo.display_if_any();
		});
	});
	$('#sel_report_type').on('click', function() {
		var value = this.form.report_type.value;
		set_selection(value);
		get_members(value, function(all_names) {
			populate_options($('#objects_tmp'), $('#objects'), all_names);
		});
	});

	$('#start_year, #end_year').on('change', function () {
		var start = 0;
		var end = 11;
		// check_custom_months is supposedly initialized by the onload
		// handler in application/views/reports/js/reports.js or equivalent.
		if (check_custom_months.start_date == undefined || check_custom_months.end_date == undefined) {
			return;
		}
		if (this.value == check_custom_months.start_date.getFullYear()) {
			start = check_custom_months.start_date.getMonth();
		}
		if (this.value == check_custom_months.end_date.getFullYear()) {
			end = check_custom_months.end_date.getMonth();
		}
		var html = '<option></option>';
		for (i = start; i <= end; i++) {
			html += '<option value="' + (i+1) + '">' + Date.monthNames[i] + '</option>';
		}
		if (this.id == 'start_year')
			$('#start_month').html(html);
		else
			$('#end_month').html(html);
	});

	$('#start_year, #end_year, #start_month, #end_month').on('change', check_custom_months);
	$("#delete_report").click(confirm_delete_report);

	$(".report_form").on('submit', function() {
		loopElements();
		return check_form_values();
	});
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

function show_calendar(val, update) {
	if (val=='custom') {
		$("#custom_time").show();

		init_datepicker();
		init_timepicker();

		if (update == '') {
			$('input[name=start_time]').attr('value', '');
			$('input[name=end_time]').attr('value', '');
		}
	} else {
		$("#custom_time").hide();
	}
	disable_sla_fields(val);
}

function set_selection(val) {
	if ($.inArray(val, ['servicegroups', 'hostgroups', 'services', 'hosts']) === -1)
		val = 'hostgroups'; // Why? Because I found it like this
	$('.object-list-type').text(val);
	$('*[data-show-for]').hide()
	$('*[data-show-for~='+val+']').show()
}

function get_members(type, cb) {
	if (!type)
		return;
	var field_name = false;
	var empty_field = false;

	show_progress('progress', _wait_str);
	$.ajax({
		url: _site_domain + _index_page + '/ajax/group_member',
		data: {type: type},
		error: function(data) {
			$.notify("Unable to fetch objects: " + data.responseText, {'sticky': true});
		},
		success: function(all_names) {
			if(typeof cb == 'function')
				cb(all_names);
			$('#progress').css('display', 'none');
		},
		dataType: 'json'
	});
}

/**
*	Populate HTML select list with supplied JSON data
*/
function populate_options(tmp_field, field, json_data)
{
	tmp_field.empty();
	field.empty();
	show_progress('progress', _wait_str);
	var available = document.createDocumentFragment();
	var selected = document.createDocumentFragment();
	for (i = 0; i < (json_data ? json_data.length : 0); i++) {
		var option = document.createElement("option");
		option.appendChild(document.createTextNode(json_data[i]));
		available.appendChild(option);
	}
	tmp_field.append(available);
	field.append(selected);
}

/**
*	Loop through all elements of a form
*	Verify that all multiselect fields (right hand side)
*	are set to selected
*/
function loopElements(f) {
	// select all elements that doesn't contain the nosave_suffix
	$('.multiple:not([id$=_tmp])').each(function() {
		if ($(this).is(':visible')) {
			$(this).children('option').attr('selected', 'selected');
		} else {
			$(this).children('option').attr('selected', false);
		}
	});
}

function check_form_values(form)
{
	if (!form)
		form = document.documentElement;
	var errors = 0;
	var err_str = '';
	var cur_start = '';
	var cur_end = '';

	var rpt_type = $("select[name=report_type]", form).val();
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

	if ($('input[name=report_mode]:checked', form).val() != 'standard' && !$('#show_all', form).is(':checked') && $("#objects", form).is('select') && $('#objects option', form).length == 0) {
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
	}

	// display err_str if any
	if (!errors) {
		$('#response', form).html('');

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

function moveAndSort(from, to)
{
	from.find('option:selected').remove().appendTo(to);
	to.sortOptions();
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
				$('.report_form #month_' + i).val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
			break;
		case 'custom':
			check_custom_months();
			break;
		case 'lastmonth':
			enable_last_months(1);
			break;
		case 'last3months':
			enable_last_months(3);
			break;
		case 'last6months':
			enable_last_months(6);
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
		var cell = $('.report_form #month_' + i);
		if (start>end) {
			if ( i >= start || i <= end) {
				cell.attr('disabled', false).css('background-color', sla_month_enabled_color);
			} else {
				cell.val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
		} else {
			if ( i>= start && i <= end) {
				cell.attr('disabled', false).css('background-color', sla_month_enabled_color);
			} else {
				cell.val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
		}
	}
}


function check_custom_months()
{
	var f		 	= $('.report_form').get(0);
	// not SLA?
	if (!f['start_month'])
		return;

	if (check_custom_months.start_date == undefined) {
		check_custom_months.start_date = new Date(0);
		check_custom_months.end_date = new Date();
		$.ajax({
			url:  _site_domain + _index_page + '/sla/custom_start/',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				if (!data.timestamp) {
					$.notify("Unable to fetch oldest report timestamp: " + data.responseText, {'sticky': true});
				}
				check_custom_months.start_date.setTime(data.timestamp * 1000);
				var html = '<option></option>';
				for (i = check_custom_months.start_date.getFullYear(); i <= check_custom_months.end_date.getFullYear(); i++) {
					html += '<option>' + i + '</option>';
				}
				$('#start_year').html(html);
				$('#end_year').html(html);
			}
		});
	}

	var start_year 	= f.start_year.value;
	var start_month = f.start_month.value;
	var end_year 	= f.end_year.value;
	var end_month 	= f.end_month.value;
	if (start_year!='' && end_year!='' && start_month!='' && end_month!='') {
		disable_months(0, 0);
	} else if (start_year == end_year - 1 || start_year == end_year) {
		disable_months(start_month, end_month);
	} else {
		disable_months(0, 0);
	}
	$('#progress').hide();
}

/**
 * Generic function to enable month_ fields
 * depending on if selection is last 1, 3 or 6 months.
 */
function enable_last_months(mnr)
{
	var now = new Date();
	var this_month = now.getMonth()+1;
	var from = this_month - mnr;
	var to = this_month - 1;
	if (from <= 0)
		from += 12;
	if (to <= 0)
		to += 12;
	disable_months(from, to);
}

function missing_objects()
{
	this.objs = [];
}

missing_objects.prototype.add = function(name)
{
	if (name != '*')
		this.objs.push(name);
}

missing_objects.prototype.display_if_any = function()
{
	if (!this.objs.length)
		return;

	var info_str = _reports_missing_objects + ": ";
	info_str += "<ul><li><img src=\"" + _site_domain + "application/views/icons/arrow-right.gif" + "\" /> " + this.objs.join('</li><li><img src="' + _site_domain + 'application/views/icons/arrow-right.gif' + '" /> ') + '</li></ul>';
	info_str += _reports_missing_objects_pleaseremove;
	info_str += '<a href="#" id="hide_response" style="position:absolute;top:8px;left:700px;">Close <img src="' + _site_domain + '' + 'application/views/icons/12x12/cross.gif" /></a>';
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
				$.notify(_reports_error + ": failed to save report.", {'sticky': true});
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
