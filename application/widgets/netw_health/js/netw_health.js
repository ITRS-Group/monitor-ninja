$(document).ready(function() {
	var netw_health = new widget('netw_health', 'widget-content');

	$('#health_warning_percentage').live('change', function() {
		if (!isNaN($(this).val())) {
			netw_health.save_custom_val($(this).val(), 'health_warning_percentage');
			netw_health.update_display();
		}
	});
	$('#health_critical_percentage').live('change', function() {
		if (!isNaN($(this).val())) {
			netw_health.save_custom_val($(this).val(), 'health_critical_percentage');
			netw_health.update_display();
		}
	});
});
