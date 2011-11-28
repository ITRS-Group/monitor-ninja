widget.register_widget_load('tac_problems', function() {
	var tac_problems = this;
	var problem_field_register = function(name) {
		$('#' + tac_problems.widget_id + ' #col_' + name + '-' + tac_problems.instance_id).live('colorpicked', function () {
			$('#' + tac_problems.widget_id + ' #id_' + name + tac_problems.instance_id).css('background', $(this).val());
			tac_problems.save_custom_val($(this).val(), 'col_' + name);
			if ($(this).val() == '#ffffff') {
				$('#' + tac_problems.widget_id + ' #id_' + name + tac_problems.instance_id).css('background', '');
			}
		});
	};

	problem_field_register('outages');
	problem_field_register('host_down');
	problem_field_register('services_critical');
	problem_field_register('host_unreachable');
	problem_field_register('services_warning');
	problem_field_register('services_unknown');
});
