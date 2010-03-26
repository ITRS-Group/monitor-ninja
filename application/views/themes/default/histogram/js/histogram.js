var startDate;
var endDate;
var DEBUG = false;
var host_tmp = false;
var host = false;
var service_tmp = false;
var service = false;
var current_obj_type = false; // keep track of what we are viewing
var is_populated = false; // flag list population when done

// to keep last valid value. Enables restore of value when an invalid value is set.
var start_time_bkup = '';
var end_time_bkup = '';

$(document).ready(function() {
	$("#histogram_form").bind('submit', function() {
		loopElements();
		return check_form_values();
	});

	var previousPoint = null;
	$("#histogram_graph").bind("plothover", function (event, pos, item) {
		$("#x").text(pos.x.toFixed(2));
		$("#y").text(pos.y.toFixed(2));

		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				$("#tooltip").remove();
				var x = item.datapoint[0].toFixed(0),
				y = item.datapoint[1].toFixed(0);

				showTooltip(item.pageX, item.pageY,
				item.series.label + ": " + y + " (" + get_label(x) + ")");
				//item.series.label + " of " + get_label(x) + " = " + y);
			}
		} else {
			$("#tooltip").remove();
			previousPoint = null;
		}
	});
	$("#report_period").bind('change', function() {
		show_calendar($(this).attr('value'));
	});

	$('#show_all_objects').click(function() {
		$('#all_objects').toggle('slow');
	});

});

function get_label(x)
{
	return graph_xlables[x];
}

function showTooltip(x, y, contents) {
	$('<div id="tooltip">' + contents + '</div>').css( {
		position: 'absolute',
		display: 'none',
		top: y + 5,
		left: x + 5,
		border: '1px solid #fdd',
		padding: '2px',
		'background-color': '#fee',
		opacity: 0.80
	}).appendTo("body").fadeIn(200);
}
