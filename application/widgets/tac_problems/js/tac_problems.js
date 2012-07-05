widget.register_widget_load('tac_problems', function() {
	var tac_problems = this;
	var problem_field_register = function(name) {
		var input = $('#' + tac_problems.widget_id + ' .col_' + name);
		var row_selector = '#' + tac_problems.widget_id + ' #id_' + name + tac_problems.instance_id;
		input.live('colorpicked', function () {
			var color = $(this).val();
			adjust_colors($(row_selector), color);
			tac_problems.save_custom_val(color, 'col_' + name);
		});
		var row = $(row_selector);
		if(row.length) {
			// when the widget's registered, we might not have host problems, for example,
			// but there may be after any updates, which will be handled by live() above
			adjust_colors(row, rgb_to_hex(row.css('backgroundColor')));
		}
	};

	problem_field_register('outages');
	problem_field_register('host_down');
	problem_field_register('service_critical');
	problem_field_register('host_unreachable');
	problem_field_register('service_warning');
	problem_field_register('service_unknown');
});

function rgb_to_hex(rgb_string) {
	var parts = rgb_string.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	if (!parts) // hey, perhaps it's hex already?
		return rgb_string;

	delete (parts[0]);
	for (var i = 1; i <= 3; ++i) {
		parts[i] = parseInt(parts[i]).toString(16);
		if (parts[i].length == 1) parts[i] = '0' + parts[i];
	}
	return parts.join('');
}

function get_contrast(hexcolor){
	var r = parseInt(hexcolor.substr(0,2),16);
	var g = parseInt(hexcolor.substr(2,2),16);
	var b = parseInt(hexcolor.substr(4,2),16);
	if((r * 299) + (g * 587) + (b * 114) > 127500) {
		return 'black';
	}
	return 'white';
}

function adjust_colors(jquery_el, color) {
	jquery_el
		.css('background', color)
		.css('color', get_contrast(color))
		.find('a')
			.css('color', get_contrast(color));
	if (color == '#ffffff') {
		jquery_el
			.css('background', '')
			.css('color', '#414141')
			.find('a')
				.css('color', '#414141');
	}
}
