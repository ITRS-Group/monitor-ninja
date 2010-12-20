$(document).ready(function() {
	var geomap = new widget('geomap', 'widget-content');

	$('#geomap_map').change(function() {
		geomap.save_custom_val(this.value, 'map');
		geomap.update_display();
	});
});
