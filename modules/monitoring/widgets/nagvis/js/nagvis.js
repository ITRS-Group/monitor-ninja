widget.register_widget_load('nagvis', function() {
	var nagvis = this;
	$('#nagvis-height-'+nagvis.instance_id).change(function() {
		var value = parseFloat(this.value);
		if (value > 0) {
			$("#" + nagvis.widget_id + " .nagvis-widget-content iframe").css('height', value);
			nagvis.save_custom_val(value, 'height');
		}
	});
});
