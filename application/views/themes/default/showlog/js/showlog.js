var startDate;
var endDate;

$(document).ready(function() {
	init_datepicker();
	init_timepicker();
	$('#summary_form').bind('submit', function() {
		return validate_showlog();
	});
});

// date validation
function validate_showlog()
{
	var err_str = '';
	var errors = false;
	if
	(
		$.trim($("input[name=cal_start]").attr('value')) == ''
		&& $.trim($("input[name=cal_end]").attr('value')) == ''
		&& $.trim($("input[name=time_start]").attr('value')) == ''
		&& $.trim($("input[name=time_end]").attr('value')) == ''
	)
	{
		return true;
	}

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
	var curval_starttime = false;;
	$(".time_start").each(function() {
		curval_starttime = $(this).val();
	});

	var curval_endtime = false;
	$(".time_end").each(function() {
		curval_endtime = $(this).val();
	});

	if (cur_enddate < cur_startdate || ($("input[name=cal_start]").val() === $("input[name=cal_end]").val() && curval_endtime < curval_starttime) ) {
		errors++;
		err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
		$(".datepick-start").addClass("time_error");
		$(".datepick-end").addClass("time_error");
	} else {
		$(".datepick-start").removeClass("time_error");
		$(".datepick-end").removeClass("time_error");
	}

	// display err_str if any
	if (!errors) {
		$('#response').html('');

		$("input[name=first]").attr('value', $("input[name=cal_start]").attr('value') + ' ' + curval_starttime);
		$("input[name=last]").attr('value', $("input[name=cal_end]").attr('value') + ' ' + curval_endtime);

		return true;
	}

	// clear all style info from progress
	$('#response').attr("style", "");
	$('#response').html("<ul class=\"error\">" + err_str + "</ul>");
	window.scrollTo(0,0); // make sure user sees the error message
	return false;
}