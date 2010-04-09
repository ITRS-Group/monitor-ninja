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
var _trends_objects_visible = false;
//var _scheduled_label = '';
var invalid_report_names = '';
var current_filename;

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

$(document).ready(function() {
	//show_state_options($('#assumeinitialstates').attr('checked'));

	$("#report_form").bind('submit', function() {
		loopElements();
		return check_form_values();
	});

	$("#saved_report_form").bind('submit', function() {
		return check_and_submit($(this));
	});

	$("#dummy_form").bind('submit', function() {
		return false;
	});

	$("#report_id").bind('change', function() {
		if (check_and_submit($("#saved_report_form"))) {
			$("#saved_report_form").trigger('submit');
		}
	});

	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});

	$("#trends_show_hide_objects").bind('click', function() {
		//show_hide_objects($(this).attr('value'));
		if (!_trends_objects_visible) {
			$("#trends_many_objects").show();
			$("#trends_show_hide_objects").text(_label_click_to_hide);
			_trends_objects_visible = true;
		} else {
			$("#trends_many_objects").hide();
			$("#trends_show_hide_objects").text(_label_click_to_view);
			_trends_objects_visible = false;
		}


	});

	// ajax post form options
	var options = {
		target:			'#response',		// target element(s) to be updated with server response
		beforeSubmit:	validate_form,	// pre-submit callback
		success:		show_response	// post-submit callback
	};

	$('.fancybox').click(function() {
		// set initial states
		set_initial_state('assumeinitialstates', assumeinitialstates);
		set_initial_state('assumestatesduringnotrunning', assumestatesduringnotrunning);
		show_state_options(assumeinitialstates=='1' ? true:false);
	});

	var class_rest = '';
	$('.trend_event').mouseover(function(e) {
		var right = $(window).width();

		var x_offset = e.pageX-195;
		var y_offset = e.pageY-170;
		if ((x_offset + 455) > right) {
			x_offset = right - 455;
		}

		y_offset = y_offset < 5 ? 5 : y_offset;

		class_rest = $(this).attr('class');
		class_rest = class_rest.replace('trend_event ', '');
		class_rest = class_rest + '_d';
		$('#trend_event_display')
			.html($(this).attr('title'))
			.css('left', x_offset)
			.css('top', y_offset)
			.addClass(class_rest)
			.show();
		$(this).addClass('trend_event_highlight');
	});

	$('.trend_event').mouseout(function() {
		$('#trend_event_display')
		.removeClass(class_rest)
		.hide();
		$(this).removeClass('trend_event_highlight');
	});

});


function show_row(the_id) {
	$("#"+the_id).show();
}

function toggle_label_weight(val, the_id)
{
	var val_str = val ? 'bold' : 'normal';
	$('#' + the_id).css('font-weight', val_str);
	$('#fancy_content #' + the_id).css('font-weight', val_str);
}

function show_state_options(val)
{
	if (val) {
		$('#fancy_content #state_options').show();
	} else {
		$('#fancy_content #state_options').hide();
	}
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
				$('input[name=' + what + ']').attr('checked', true);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				toggle_label_weight(0, 'include_softstates');
				$('input[name=' + what + ']').attr('checked', false);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
			}
			break;
		case 'assumeinitialstates':
			if (val=='1') {
				edit_state_options(1);
				toggle_label_weight(1, 'assume_initial');
				$('input[name=' + what + ']').attr('checked', true);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				$('input[name=' + what + ']').attr('checked', false);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
				edit_state_options(0);
				toggle_label_weight(0, 'assume_initial');
			}
			break;
		case 'assumestatesduringnotrunning':
			if (val!='0' && val!='') {
				edit_state_options(1);
				toggle_label_weight(1, 'assume_statesnotrunning');
				$('input[name=' + what + ']').attr('checked', true);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				$('input[name=' + what + ']').attr('checked', false);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
				edit_state_options(0);
				toggle_label_weight(0, 'assume_statesnotrunning');
			}
			break;
		case 'scheduleddowntimeasuptime':
			if (val!='0' && val!='') {
				toggle_label_weight(1, 'sched_downt');
				$('input[name=' + what + ']').attr('checked', true);
				if ($('#fancy_content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				$('input[name=' + what + ']').attr('checked', false);
				toggle_label_weight(0, 'sched_downt');
				if ($('#fancy_content').is(':visible')) {
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

var tl = false;
// loader function for timeline
function onLoad(start, end)
{
	var eventSource = new Timeline.DefaultEventSource(0);
	var startProj = SimileAjax.DateTime.parseIso8601DateTime(start);
	var endProj = SimileAjax.DateTime.parseIso8601DateTime(end);
	var theme = Timeline.ClassicTheme.create();
	theme.autoWidth = true;
	theme.event.bubble.width = 350;
	theme.event.bubble.height = 300;
	theme.timeline_start = startProj;
	theme.timeline_stop  = endProj;
	var center_val = _graph_center!='' ? _graph_center : start;
	var dcenter = SimileAjax.DateTime.parseIso8601DateTime(center_val);
	//var dleft = SimileAjax.DateTime.parseIso8601DateTime(_left_boundary);
	var d = SimileAjax.DateTime.parseIso8601DateTime(start);
	if (_is_single) {
		theme.event.track.height = "20";
		theme.event.tape.height = 10; // px
		theme.event.track.height = theme.event.tape.height + 6;
	}
	theme.event.track.offset = 20;

	// control report period resolution
	switch (_zoneperiod) {
		case 'HOUR':
			zone_timeline_var = Timeline.DateTime.HOUR;
			break;
		case 'DAY':
			zone_timeline_var = Timeline.DateTime.DAY;
			break;
		case 'WEEK':
			zone_timeline_var = Timeline.DateTime.WEEK;
			break;
		case 'MONTH':
			zone_timeline_var = Timeline.DateTime.MONTH;
			break;
	}
	switch (_intervalunit) {
		case 'DAY':
			_timeline_var = Timeline.DateTime.DAY;
			break;
		case 'WEEK':
			_timeline_var = Timeline.DateTime.WEEK;
			break;
		case 'MONTH':
			_timeline_var = Timeline.DateTime.MONTH;
			break;
	}

	var zones = [
		{   start:    startProj,
			end:      endProj,
			magnify:  _magnify,
			unit:     zone_timeline_var
		}
	];
	var bandInfos = [
		Timeline.createHotZoneBandInfo({
			width:			"85%",
			intervalUnit:	_timeline_var,
			intervalPixels: 50,
			eventSource:	eventSource,
			date:			dcenter,
			zones:			zones,
			theme:			theme,
			align:			"Top",
			layout:			'original'  // original, overview, detailed
		}),
		Timeline.createBandInfo({
			layout:			'overview',
			date:			d,
			trackHeight:	0.5,
			trackGap:		0.2,
			eventSource:	eventSource,
			width:			"15%",
			intervalUnit:	Timeline.DateTime.MONTH,
			//    showEventText:  false,
			intervalPixels: 100,
			theme :theme
		})
	];

	bandInfos[1].highlight = false;
	bandInfos[1].syncWith = 0;

	bandInfos[0].decorators = [
		new Timeline.SpanHighlightDecorator({
			startDate:  startProj,
			endDate:    endProj,
			inFront:    false,
			color:      "#FFFFFF",
			opacity:    30,
			theme:      theme
		})
	];
	bandInfos[1].decorators = [
		new Timeline.SpanHighlightDecorator({
			startDate:  startProj,
			endDate:    endProj,
			inFront:    false,
			color:      "#FFC080",
			opacity:    30,
			//startLabel: "Begin",
			//endLabel:   "End",
			theme:      theme
		})
	];

	tl = Timeline.create(document.getElementById("tl"), bandInfos, Timeline.HORIZONTAL);
	eventSource.loadJSON(json, document.location.href);

	//tl.getBand(0).setMinVisibleDate(dleft);
	//tl.getBand(0).setMaxVisibleDate(endProj);
	tl.finishedEventLoading();
	setupFilterHighlightControls(document.getElementById("controls"), tl, [0,1], theme);

}

function onResize() {
	if (resizeTimerID == null) {
		resizeTimerID = window.setTimeout(function() {
			resizeTimerID = null;
			tl.layout();
		}, 500);
	}
}

function centerSimileAjax(date) {
    tl.getBand(0).setCenterVisibleDate(SimileAjax.DateTime.parseIso8601DateTime(date));
}

function setupFilterHighlightControls(div, timeline, bandIndices, theme) {
    var table = document.createElement("table");
    var tr = table.insertRow(0);

    var td = tr.insertCell(0);
    td.innerHTML = _filter_str + ":";

    td = tr.insertCell(1);
    td.innerHTML = _highlight_str + ":";

    var handler = function(elmt, evt, target) {
        onKeyPress(timeline, bandIndices, table);
    };

    tr = table.insertRow(1);
    tr.style.verticalAlign = "top";

    td = tr.insertCell(0);

    var input = document.createElement("input");
    input.type = "text";
    SimileAjax.DOM.registerEvent(input, "keypress", handler);
    td.appendChild(input);

    for (var i = 0; i < theme.event.highlightColors.length; i++) {
        td = tr.insertCell(i + 1);

        input = document.createElement("input");
        input.type = "text";
        input.id = "text_filter";
        SimileAjax.DOM.registerEvent(input, "keypress", handler);
        td.appendChild(input);

        var divColor = document.createElement("div");
        divColor.style.height = "0.5em";
        divColor.style.background = theme.event.highlightColors[i];
        td.appendChild(divColor);
    }

    td = tr.insertCell(tr.cells.length);
    var button = document.createElement("button");
    button.innerHTML = _clear_all;
    SimileAjax.DOM.registerEvent(button, "click", function() {
        clearAll(timeline, bandIndices, table);
    });
    td.appendChild(button);

	// == hard and state value filter
	$('#hard_filter').bind('click', function() {
		filterHard_States(timeline, bandIndices, table);
	});

	$('#filter_states').bind('change', function() {
		filterHard_States(timeline, bandIndices, table);
	});

    div.appendChild(table);
}

/**
*	Filter on hard states and different state values
*/
function filterHard_States(timeline, bandIndices, table)
{
	var filterMatcher = null;

	var hard = $('#hard_filter').attr('checked');
	var state_val = $('#filter_states').val();
	var filterMatcher = null;
	filterMatcher = function(evt) {

		if (!hard) {
			if (state_val != '') {
				return (evt.getProperty(state_val) || false);
			} else {
				if (!$('#text_filter').val()) {
					// no hard state and no state to filter on
					clearAll(timeline, bandIndices, table);
					return;
				} else {
					//return performFiltering(timeline, bandIndices, table);
					var tr = table.rows[1];
					var text = cleanString(tr.cells[0].firstChild.value);
					if (text.length > 0) {
				        var regex = new RegExp(text, "i");
			            return regex.test(evt.getText()) || regex.test(evt.getDescription());
				    }
				}
			}
		} else {
			if (state_val != '') {
				// combine state and hard filter
				return ((evt.getProperty('hard') && evt.getProperty(state_val)) || false);
			} else {
				return (evt.getProperty('hard') || false);
			}
		}
	};
	for (var i = 0; i < bandIndices.length; i++) {
		var bandIndex = bandIndices[i];
		timeline.getBand(bandIndex).getEventPainter().setFilterMatcher(filterMatcher);
	}
	timeline.paint();
}

var timerID = null;
function onKeyPress(timeline, bandIndices, table) {
    if (timerID != null) {
        window.clearTimeout(timerID);
    }
    timerID = window.setTimeout(function() {
		performFiltering(timeline, bandIndices, table);
    }, 300);
}
function cleanString(s) {
    return s.replace(/^\s+/, '').replace(/\s+$/, '');
}
function performFiltering(timeline, bandIndices, table) {
    timerID = null;

    var tr = table.rows[1];
    var text = cleanString(tr.cells[0].firstChild.value);

    var filterMatcher = null;
    if (text.length > 0) {
        var regex = new RegExp(text, "i");
        filterMatcher = function(evt) {
            return regex.test(evt.getText()) || regex.test(evt.getDescription());
        };
    }

    var regexes = [];
    var hasHighlights = false;
    for (var x = 1; x < tr.cells.length - 1; x++) {
        var input = tr.cells[x].firstChild;
        var text2 = cleanString(input.value);
        if (text2.length > 0) {
            hasHighlights = true;
            regexes.push(new RegExp(text2, "i"));
        } else {
            regexes.push(null);
        }
    }
    var highlightMatcher = hasHighlights ? function(evt) {
        var text = evt.getText();
        var description = evt.getDescription();
        for (var x = 0; x < regexes.length; x++) {
            var regex = regexes[x];
            if (regex != null && (regex.test(text) || regex.test(description))) {
                return x;
            }
        }
        return -1;
    } : null;

    for (var i = 0; i < bandIndices.length; i++) {
        var bandIndex = bandIndices[i];
        timeline.getBand(bandIndex).getEventPainter().setFilterMatcher(filterMatcher);
        timeline.getBand(bandIndex).getEventPainter().setHighlightMatcher(highlightMatcher);
    }
    timeline.paint();
}
function clearAll(timeline, bandIndices, table) {
    var tr = table.rows[1];
    for (var x = 0; x < tr.cells.length - 1; x++) {
        tr.cells[x].firstChild.value = "";
    }

    for (var i = 0; i < bandIndices.length; i++) {
        var bandIndex = bandIndices[i];
        timeline.getBand(bandIndex).getEventPainter().setFilterMatcher(null);
        timeline.getBand(bandIndex).getEventPainter().setHighlightMatcher(null);
    }
    timeline.paint();
}
