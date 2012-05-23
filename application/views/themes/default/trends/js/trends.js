var current_obj_type = false; // keep track of what we are viewing
var is_populated = false; // flag list population when done
var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
var _trends_objects_visible = false;

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
	show_calendar($("#report_period").attr('value'));

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
		if (typeof assumestatesduringnotrunning != 'undefined') {
			set_initial_state('assumestatesduringnotrunning', assumestatesduringnotrunning);
		}
		show_state_options(assumeinitialstates=='1' ? true:false);
	});
});

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
