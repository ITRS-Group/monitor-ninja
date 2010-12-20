$(document).ready(function() {
	var geomap = new widget('geomap', 'widget-content');

	$('#'+geomap.name+'_map').change(function() {
		geomap.save_custom_val(this.value, 'map');
		geomap.update_display();
	});

	$('#'+geomap.name+'_height').change(function() {
		var value = parseFloat(this.value);
		if (value > 0) {
			$('#nagvis').css('height', value);
			geomap.save_custom_val(this.value, 'height');
		}
	});
});
