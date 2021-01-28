$(function() {
	// reset options and reload page
	$('#new_report').on('click', function() {
		var current_report = $('input[name=type]').val();
		var base_uri = _site_domain + _index_page + '/' + _current_uri;
		var uri_xtra = current_report == 'avail' ? '' : '?type=sla';
		self.location.href = base_uri + uri_xtra;
	});

	$('#include_trends').on('click', function() {
		if (this.checked) {
			$('#include_trends_scaling').attr('disabled', false);
		} else {
			$('#include_trends_scaling').attr('disabled', true);
			$('#include_trends_scaling').attr('checked', false);
		}
	});

	$("#report_period").on('change', function() {
		show_calendar($(this).prop('value'));
	});
	show_calendar($("#report_period").prop('value'));

	$('.autofill').on('click', function() {
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

	$("#new_schedule_btn").on('click', function() {$('.schedule_error').hide();})

	$('#filename').on('blur', function() {
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

  $(document).on('click', '#include_trends', function(ev) {
		if (ev.target.checked)
			$('.trend_options').show();
		else
			$('.trend_options').hide();
	});

	$('#sla_report_id').on('change', function(ev) {
		var sla_id = $(this).prop('value');
		if (!sla_id) {
			// don't try to fetch sla values when we have no id
			return;
		}
		show_progress('progress', _wait_str);
		var url = _site_domain + _index_page + '/sla/per_month_sla_for_report';
		$.ajax({
			url: url,
			data: {
				sla_id: sla_id,
				csrf_token: _csrf_token
			},
			complete: function() {
				$('#progress').hide();
			},
			error: function() {
				$.notify(_reports_error + ': Unable to fetch saved sla values...', {'sticky':true});
			},
			success: function(data) {
				for (var i = 0; i < data.length; i++) {
					var input = $('#month_' + i);
					if (!data[i]) {
						input.val('');
					} else {
						input.val(data[i]);
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
		input.attr('value', the_val);
	}
}
