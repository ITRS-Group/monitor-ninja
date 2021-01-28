var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
$(document).ready(function() {
	$(".fancybox").fancybox({
		'overlayOpacity'        :       0.7,
		'overlayColor'          :       '#ffffff',
		'hideOnContentClick' : false,
		'autoScale':true,
		'autoDimensions': true,
		'onComplete': function(obj) { $($(obj).attr('href')).find('.filter-status').each(filter_mapping_mapping); }
	});

	$('.filter-status').on('change', filter_mapping_mapping).each(filter_mapping_mapping);

	var direct_link_visible = false;
	$('#current_report_params').on('click', function() {
		// make sure we always empty the field
		$('#link_container').html('');
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

	$('#save_report').on('click', function() {
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
							input.trigger('focus');
						}
					});
				direct_link_visible = true;
		} else {
			$('#save_report_form').hide();
			direct_link_visible = false;
		}
		return false;
	});

	$("#report_id").on('change', function() {
		$("#saved_report_form").trigger('submit');
	});

	$('.save_report_btn').parents('form').on('submit', function(ev) {
		ev.preventDefault();
		var form = $(this);
		var btn = form.find('.save_report_btn');
		btn.after(loadimg_sml);
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
				Notify.message(data.status_msg, {type: "success"});
				if (!btn[0].form.report_id)
					$('form').append('<input type="hidden" name="report_id" value="'+data.report_id+'"/>');
				$('#save_report_form').hide();
			},
			error: function(data) {
				var resp;
				try {
					resp = $.parseJSON(data.responseText).error;
				} catch (ex) {
					resp = "Unknown error";
				}
				$.notify(_reports_error + ": " + resp, {'sticky': true});
				btn.parent().find('img:last').remove();
			},
			dataType: 'json'
		});
	});

	$(document).on('change', '#report_type', function( e ) {

		set_selection();
		var filterable = jQuery.fn.filterable.find( $('select[name="objects[]"]') ),
			type = e.target.value.replace( /s$/, "" );

		var url = _site_domain + _index_page;
		url += '/listview/fetch_ajax?query=[' + type + 's] all&columns[]=key&limit=1000000';

		if ( filterable ) {
			$.ajax({
				url: url,
				dataType: 'json',
				error: function( xhr ) {
					console.log( xhr.responseText );
				},
				success: function( data ) {

					var names = [];

					for ( var i = 0; i < data.data.length; i++ ) {
						names.push( data.data[ i ].key );
					}

					filterable.data = new Set( names );
					filterable.reset();

				}
			});
		}

	});

	$(document).on('change', '#start_year, #end_year',function () {
		var start = 0;
		var end = 11;
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
	$("#delete_report").on('click', confirm_delete_report);

	$(document).on('submit', '.report_form', function() {
		$('.filter-status:visible:checked', this).each(function() {
			$('#' + $(this).data('which')).find('input, select').attr('name', '');
		});
		$('.filter-status:not(:visible)', this).each(function() {
			$('#' + $(this).data('which')).find('input, select').attr('value', '-2');
		});
		return check_form_values();
	});
	$('#report_type').on('change', set_selection);
	set_selection();
});

function init_datepicker()
{

  $('#cal_start').datepicker({
    firstDay: 1, 
    dateFormat: 'yy-mm-dd', 
    startDate: _start_date, 
    maxDate: new Date(),
    onClose: function () {
      if($(this).val()  !== "") {
        $('#cal_end').datepicker( "option", "minDate", $(this).val() );
      }
     }
   });
  $('#cal_end').datepicker({
     firstDay: 1, 
     dateFormat: 'yy-mm-dd', 
     startDate: _start_date, 
     maxDate: new Date(),
     onClose: function () {
      if($(this).val()  !== "") {
        $('#cal_start').datepicker( "option", "maxDate", $(this).val() );
      }
    }
    });
  

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

		if ($('#month_1').length) {
			check_custom_months();
		}
	} else {
		$("#custom_time").hide();
	}
}

function set_selection() {
	var val = $('#report_type').val();
	if ($.inArray(val, ['servicegroups', 'hostgroups', 'services', 'hosts']) === -1)
		val = 'hostgroups'; // Why? Because I found it like this
	$('.object-list-type').text(val);
	$('*[data-show-for]').hide();
	$('*[data-show-for~='+val+']').show();
}

function check_form_values(form)
{
	if (!form)
		form = document.documentElement;
	var errors = 0;
	var err_str = '';
	var cur_start = '';
	var cur_end = '';

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

	if ($('input[name=report_mode]:checked', form).val() != 'standard' && !$('#show_all', form).is(':checked') && $("[name=objects\\[\\]]", form).is('select') && $('[name=objects\\[\\]] option', form).length == 0) {
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
			var value = input.prop('value');
			value = value.replace(',', '.');
			if (value > max_val || isNaN(value)) {
				input.css('background', sla_month_error_color);
				errors++;
				red_error = true;
			}
			if (value != '') {
				nr_of_slas++;
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

// init timepicker once it it is shown
function init_timepicker()
{
	$("#time_start, #time_end").timepicker({ 'scrollDefault': 'now' });
}

function check_custom_months()
{
	var f = $('.report_form').get(0);
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
	$('#progress').hide();
}

function confirm_delete_report()
{
	var id = $("#report_id").prop('value')

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	var type = $('input[name=type]').prop('value');
	if (!id)
		return;
	if (is_scheduled) {
		msg += _reports_confirm_delete_warning;
	}
	msg = msg.replace("this saved report", "the saved report '"+$('#report_id option[selected=selected]').text()+"'");
	if (confirm(msg)) {
		$(this).after(loadimg_sml);
		$.ajax({
			url: _site_domain + _index_page + '/' + _controller_name + '/delete/',
			type: 'POST',
			data: {'report_id': id, csrf_token: _csrf_token},
			complete: function() {
				$(loadimg_sml).remove();
			},
			success: function(data) {
				var a = document.createElement("a");
				a.href = window.location.href;
				if(a.search && a.search.indexOf("report_id="+id) !== -1) {
					window.location.href = a.search.replace(new RegExp("report_id="+id+"&?"), "");
				}
			},
			error: function(data) {
				var msg;
				try {
					msg = $.parseJSON(data.responseText).error;
				} catch (ex) {
					msg = "Unknown error";
				}
				$.notify("Failed to delete report: " + msg, {'sticky': true});
			},
			dataType: 'json'
		});
	}
}

function filter_mapping_mapping()
{
	if ($(this).is(':checked'))
		$('#' + $(this).data('which')).hide();
	else
		$('#' + $(this).data('which')).show();
	// when checking if the child is visible, the container must be visible
	// or we'd be checking the wrong thing.
	$(this).siblings('.configure_mapping').show();
	if (!$(this).siblings('.configure_mapping').find('.filter_map:visible').length)
		$(this).siblings('.configure_mapping').hide();
}
