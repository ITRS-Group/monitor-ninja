$(document).ready(function() {
	var netw_health = new widget('netw_health', 'widget-content');

	// the form fields we need an event handler for
	var fields = new Array('health_warning_percentage', 'health_critical_percentage');

	for (field in fields) {
		$('#' + fields[field]).live('change', function() {
			if (!isNaN($(this).val())) {
				netw_health.save_custom_val($(this).val(), fields[field]);
				netw_health.update_display();
			}
		});
	}
});
