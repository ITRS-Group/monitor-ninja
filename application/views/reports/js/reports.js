$(document).ready(function() {
	// reset options and reload page
	$('#new_report').click(function() {
		var current_report = $('input[name=type]').val();
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		var uri_xtra = current_report == 'avail' ? '' : '?type=sla';
		self.location.href = base_uri + uri_xtra;
	});

	disable_sla_fields($('#report_period').attr('value'));

	$("#report_form").bind('submit', function() {
		loopElements();
		return check_form_values();
	});

	$('#include_trends').click(function() {
		if (this.checked) {
			$('#include_trends_scaling').attr('disabled', false);
		} else {
			$('#include_trends_scaling').attr('disabled', true);
			$('#include_trends_scaling').attr('checked', false);
		}
	});

	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});
	show_calendar($("#report_period").attr('value'));

	$('.autofill').click(function() {
		var the_val = $(this).siblings('input').val();
		if (the_val!='') {
			if (!confirm(_reports_propagate.replace('this value', the_val+'%'))) {
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

	$("#new_schedule_btn").click(function() {$('.schedule_error').hide();})

	$('#filename').blur(function() {
		// Make sure the filename is explicit by adding it when focus leaves input
		var input = $(this);
		var filename = input.val();
		if(!filename) {
			return;
		}
		if(!filename.match(/\.(csv|pdf)$/)) {
			filename += '.pdf';
		}
		input.val(filename);
	});

	$('#include_trends').live('click', function(ev) {
		if (ev.target.checked)
			$('.trend_options').show();
		else
			$('.trend_options').hide();
	});
});

function populate_saved_sla_data(json_data) {
	json_data = eval(json_data);
	for (var i = 1; i <= 12; i++) {
		$("#sla_month_"+i).attr('value','');
	}
	for (var i = 0; i < json_data.length; i++) {
		var j = i+1;
		var name = json_data[i].name;
		var value = json_data[i].value;
		if (document.getElementById("sla_"+name).style.backgroundColor != 'rgb(205, 205, 205)')
			$("#sla_"+name).attr('value',value);
	}
	setTimeout(delayed_hide_progress, 1000);
}

// Propagate sla values
function set_report_form_values(the_val)
{
	for (i=1;i<=12;i++) {
		var field_name = 'month_' + i;
		var input = $("#"+field_name);
		if (input.attr('disabled')) {
			input.attr('value', '');
		} else {
			input.attr('value', the_val);
		}
	}
}

function set_initial_state(what, val)
{
	var rep_type = $('input[name=type]').attr('value');
	f = $('#report_form').get(0);
	var item = '';
	var elem = false;
	switch (what) {
		case 'includesoftstates':
			if (val!='0') {
				f.elements.includesoftstates.checked = true;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', true);
				}
			} else {
				f.elements.includesoftstates.checked = false;
				if ($('#fancybox-content').is(':visible')) {
					$('input[name=' + what + ']').attr('checked', false);
				}
			}
			break;
		case 'rpttimeperiod':
			item = 'rpttimeperiod';
			break;
		default:
			item = what;
	}
	if (item) {
		elem = f[item];
		if (elem) {
			for (i=0;i<elem.length;i++) {
				if (elem.options[i].value==val) {
					elem.options[i].selected = true;
				}
			}
		}
	}
}

/**
*	create ajax call to reports/fetch_field_value
*	to fetch a specific field value and asssign it to html element.
*/
function fetch_field_value(type, id, elem_id)
{
	$.ajax({
		url: _site_domain + _index_page + '/reports/fetch_field_value?id=' + id + '&type=' + type,
		success: function(data) {
			$('#' + elem_id).text(data);
		}
	});
}

function get_sla_values() {
	var sla_id = $('#sla_report_id').attr('value');

	if (!sla_id) {
		// don't try to fetch sla values when we have no id
		return;
	}
	show_progress('progress', _wait_str);
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "get_sla_from_saved_reports/";
	var data = {sla_id: sla_id}

	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		error: function() {
			jgrowl_message('Unable to fetch saved sla values...', _reports_error);
		},
		success: function(data) {
			populate_saved_sla_data(data);
			$('.sla_values').show();
		},
		dataType: 'json'
	});
}

function toggle_state(the_id)
{
	if ($('#' + the_id).attr('checked') ) {
		$('#' + the_id).attr('checked', false);
	} else {
		$('#' + the_id).attr('checked', true);
	}
}
