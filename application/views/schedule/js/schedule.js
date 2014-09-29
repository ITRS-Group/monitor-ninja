function remove_scheduled_str(in_str)
{
	in_str = in_str.replace(/\*/g, '');
	in_str = in_str.replace(" ( " + _scheduled_label + " )", '');
	return in_str;
}

function create_filename()
{
	if (!$('#saved_report_id option:selected').val()) {
		$('input[name=filename]').val('');
		return false;
	}
	var new_filename = $('#saved_report_id option:selected').text();
	new_filename = remove_scheduled_str(new_filename);
	new_filename += '_' + $('#period option:selected').text() + '.pdf';
	new_filename = new_filename.replace(/ /g, '_');
	$('input[name=filename]').val(new_filename);
	return true;
}

function fill_saved_reports() {
	var report_type = this.value;
	$.ajax(
		_site_domain + _index_page + "/schedule/list_by_type/"+report_type,
		{
			error: function(xhr) {
				$.notify(_reports_error + ": " + xhr.responseText, {'sticky': true});
		},
			success: function(response) {
				var saved_reports = document.getElementById("saved_report_id");
				var default_opt = saved_reports.children[0];
				saved_reports.length = 1;
				var options = document.createDocumentFragment();
				for(var i = 0; i < response.length; i++) {
					var option = document.createElement("option");
					var result = response[i];
					option.appendChild(document.createTextNode(result.report_name));
					option.setAttribute("value", result.id);
					options.appendChild(option);
				}
				saved_reports.appendChild(options);
				create_filename();
			},
			dataType: 'json'
		}
	);
}

$(document).ready(function() {
	$("#saved_report_id, #period").change(create_filename);
	fill_scheduled();
	setup_editable();

		// delete single schedule
	$('body').on('click', '.delete_schedule', schedule_delete);
	$('body').on('click', '.send_report_now', send_report_now);

	$("#type").change(fill_saved_reports).each(fill_saved_reports);

	$('#new_schedule_report_form').submit(function(ev) {
		ev.preventDefault();

		var rep_type_str = $('#type option:selected').val();

		var recipients = $.trim($('#recipients').fieldValue()[0]);
		if (recipients.indexOf('@') === -1) {
			$.notify(_reports_invalid_email, {'sticky': true});
			return false;
		}

		if(!validate_form()) {
			return false;
		}
		show_progress('progress', _wait_str);
		$.ajax({
			url: _site_domain + _index_page + '/schedule/schedule',
			type: 'POST',
			data: {
				report_id: 0,
				type: $('#type').fieldValue()[0],
				saved_report_id: $('#saved_report_id').fieldValue()[0],
				period: $('#period').fieldValue()[0],
				recipients: recipients,
				filename: $('#filename').fieldValue()[0],
				description: $('#description').fieldValue()[0],
				attach_description: $('#attach_description').fieldValue()[0],
				local_persistent_filepath: $.trim($('#local_persistent_filepath').val())
			},
			complete: function() {
				$('#progress').hide();
				// make sure we hide message about no schedules and show table headers
				$('#' + rep_type_str + '_no_result').hide();
				$('#' + rep_type_str + '_headers').show();
			},
			error: function(data) {
				jgrowl_message(data.responseText, _reports_error);
			},
			success: function(data) {
				var rep_type = $('#type').attr('value');
				var saved_report_id = $('#saved_report_id').attr('value');
				var report_name = $('#saved_report_id option:selected').text();
				var period_str = $('#period option:selected').text();
				var recipients = $('#recipients').attr('value');
				var filename = $('#filename').attr('value');
				var local_persistent_filepath = $('#local_persistent_filepath').attr('value');
				var description = $('#description').attr('value');
				var attach_description = $('#attach_description').attr('value');
				create_new_schedule_rows(data.id, rep_type, report_name, saved_report_id, period_str, recipients, filename, local_persistent_filepath, description, attach_description)
				setup_editable();
				$('#new_schedule_report_form').clearForm();

				jgrowl_message(_reports_schedule_create_ok, _reports_success);
			},
			dataType: 'json'
		});
	});
});

function schedule_delete(ev)
{
	ev.preventDefault();
	if (!confirm(_reports_confirm_delete_schedule)) {
		return false;
	}

	var elem = $(this);
	var type = elem.data('type');
	var schedule_id = elem.data('schedule');
	var report_id = elem.data('report_id');

	var img = $('img', elem);
	var img_src = img.attr('src');
	img.attr('src', loadimg_sml.src);

	$.ajax({
		url:_site_domain + _index_page + '/schedule/delete_schedule',
		data: {'id': schedule_id},
		complete: function() {
			img.attr('src', img_src);
		},
		success: function(data) {
			jgrowl_message(data, _reports_success);
			var table = $('#'+type+'_scheduled_reports_table tbody');
			$('tr#report-'+schedule_id, table).detach();
			if (!$(':visible', table).length)
				$('.no-result', table).show();
		},
		error: function(data) {
			jgrowl_message(data, _reports_error);
		},
		type: 'POST',
		dataType: 'json'
	});
}

function send_report_now(ev)
{
	ev.preventDefault();
	var elem = $(this);
	var type = elem.data('type');
	var sched_id = elem.data('schedule');
	var report_id = elem.data('report_id');
	var img = $('img', elem);
	var img_src = img.attr('src');
	img.attr('src', loadimg_sml.src);

	$.ajax({
		url: _site_domain + _index_page + '/schedule/send_now/' + sched_id,
		type: 'POST',
		complete: function() {
			img.attr('src', img_src);
		},
		success: function(data) {
			jgrowl_message(data, _reports_success);
		},
		error: function(data) {
			if(data.responseText) {
				jgrowl_message(_reports_schedule_send_error + ': ' + data.responseText, _reports_error);
			} else {
				jgrowl_message(_reports_schedule_send_error, _reports_error);
			}
			img.attr('src', img_src);
		},
		dataType: 'json'
	});
}

function setup_editable()
{
	var save_url = _site_domain + _index_page + "/schedule/save_schedule_item/";
	$(".iseditable").editable(save_url, {
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
	$(".period_select").editable(save_url, {
		data : function(value) {
			var intervals = [];
			$('#period option').map(function() {
				intervals.push("'"+$(this).val()+"': '"+$(this).text()+"' ");
			});
			intervals = "{"+intervals.join(",")+"}";
			return intervals;
		},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : _ok_str,
		cancel : _cancel_str
	});
	$(".iseditable_txtarea").editable(save_url, {
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
	$(".report_name").editable(save_url, {
		data : function (){
			switch (_report_types_json[this.id.split('-')[0].split('.')[0]]) {
				case 'avail':
					return _saved_avail_reports;
				case 'sla':
					return _saved_sla_reports;
				case 'summary':
					return _saved_summary_reports;
			}
			return false;
		},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : 'OK',
		cancel : 'cancel'
	});
	$(".attach_description").editable(save_url, {
		data : {0: "No", 1: "Yes"},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : _ok_str,
		cancel : _cancel_str
	});
}

function create_new_schedule_rows(schedule_id, rep_type, report_name, report_id, report_period, recipients, filename, local_persistent_filepath, description, attach_description)
{
	var template_row = $('#schedule_template tr');

	var report_type_id = -1;
	for (var i in _report_types_json) {
		if (_report_types_json[i] == rep_type) {
			report_type_id = i;
		}
	}

	template_row = template_row.clone()
	$('#' + rep_type + '_scheduled_reports_table .no-result').hide();
	template_row.attr('id', 'report-'+schedule_id);
	$('.report_name', template_row)
		.attr('id', '' + report_type_id + '.report_id-'+schedule_id)
		.text(report_name.replace(/ \( \*Scheduled\* \)$/, ""));
	$('.description', template_row)
		.attr('id', 'description-'+schedule_id)
		.text(description);
	$('.period_select', template_row)
		.attr('id', 'period_id-'+schedule_id)
		.text(report_period);
	$('.recipients', template_row)
		.attr('id', 'recipients-'+schedule_id)
		.text(recipients);
	$('.filename', template_row)
		.attr('id', 'filename-'+schedule_id)
		.text(filename);
	$('.local-path', template_row)
		.attr('id', 'local_persistent_filepath-'+schedule_id)
		.text(local_persistent_filepath);
	$('.attach_description', template_row)
		.attr('id', 'attach_description-'+schedule_id)
		.text(parseInt(attach_description) ? 'Yes' : 'No');
	var actions = $('.action', template_row);
	$('.direct_link', actions).attr('href', _site_domain + _index_page + '/' + rep_type + '/generate?report_id=' + report_id);
	$('.send_report_now, .delete_schedule', actions).data('schedule', schedule_id).data('report_id', report_id).data('type', rep_type);
	var par = $('#' + rep_type + '_scheduled_reports_table tbody');
	if (par.children().last().hasClass('odd'))
		template_row.attr('class', 'even');
	else
		template_row.attr('class', 'odd');
	par.append(template_row);
}

function fill_scheduled() {
	for (var type in _scheduled_reports) {
		if (!_scheduled_reports[type].length) {
			$('#' + type + '_scheduled_reports_table .no-result').show();
			continue;
		}
		for (var i = 0; i < _scheduled_reports[type].length; i++) {
			var report = _scheduled_reports[type][i];
			create_new_schedule_rows(report.id, type, report.reportname, report.report_id, report.periodname, report.recipients, report.filename, report.local_persistent_filepath, report.description, report.attach_description);
		}
	}
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
	if ($.trim(recipients) == '') {
		err_str += _reports_schedule_recipient_error + "<br />";
		errors++;
	}
	if (!saved_report_id) {
		$.notify(fatal_err_str, {'sticky': true});
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
