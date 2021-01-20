$(function() {
	// since we bundle our javascript to be included in all pages, we need
	// to safeguard against this piece of code being called globally
	if($("#histogram_graph").length === 0) {
		return;
  }
  

	var get_label = function(x) {
		return graph_xlables[x];
	};
	var showTooltip = function(x, y, contents) {
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
	};
	var previousPoint = null;
	$("#histogram_graph").on("plothover", function (event, pos, item) {
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
			}
		} else {
			$("#tooltip").remove();
			previousPoint = null;
		}
	});

	$('#show_all_objects').on('click', function() {
		$('#all_objects').toggle('slow');
	});

	var choiceContainer = $('#choices');
	$.each(datasets, function(key, val) {
		choiceContainer.append('<br/><input type="checkbox" name="' + key +
		'" checked="checked" id="id' + key + '">' +
		'<label for="id' + key + '">'
		+ val.label + '</label>');
    });
    $('#choices input[type="checkbox"]').on('click', plotAccordingToChoices);  

	function plotAccordingToChoices() {
		var data = [];

		choiceContainer.find("input:checked").each(function () {
			var key = $(this).attr("name");
			if (key && datasets[key])
				data.push(datasets[key]);
		});

		if (data.length > 0)
			$.plot($('#histogram_graph'), data, graph_options);
	}

	plotAccordingToChoices();
});
