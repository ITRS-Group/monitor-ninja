$(document).ready(function() {
	// reset options and reload page
	$('#new_report').click(function() {
		var current_report = $('input[name=type]').val();
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		var uri_xtra = current_report == 'avail' ? '' : '?type=sla';
		self.location.href = base_uri + uri_xtra;
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

	$('#sla_report_id').change(function(ev) {
		var sla_id = $(this).attr('value');
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
			data: data,
			complete: function() {
				$('#progress').hide();
			},
			error: function() {
				$.notify(_reports_error + ': Unable to fetch saved sla values...', {'sticky':true});
			},
			success: function(data) {
				for (var i = 1; i <= 12; i++) {
					$("#month_"+i).attr('value','');
				}
				for (var i = 0; i < data.length; i++) {
					var name = data[i].name;
					var value = data[i].value;
					var checkbox = $('#'+name);
					if (checkbox.attr('disabled') !== 'disabled') {
						checkbox.val(value);
					}
				}
				$('.sla_values').show();
			},
			dataType: 'json'
		});
	});
});

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
