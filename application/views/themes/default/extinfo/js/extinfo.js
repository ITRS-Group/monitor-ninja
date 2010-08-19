$(document).ready(function() {
	$('.extinfo_contactgroup').each(function() {
		$(this).bind('click', function() {
			var the_id = $(this).attr('id');
			the_id = the_id.replace('extinfo_contactgroup_', '');
			$('#extinfo_contacts_' + the_id).toggle();
		});
	});

	var pnp_area_visible = false;
	$('#pnp_inline_graph').click(function() {
		if (!_pnp_web_path) {
			// follow href in case we can't find _pnp_web_path variable
			return true;
		}
		$('#pnp_area').html('');
		var base_path = _site_domain + _index_page + '/pnp/';
		var the_path = $('#pnp_inline_graph').attr('href');
		the_path = the_path.replace(base_path, '');

		the_path = _pnp_web_path + the_path + '&source=1&view=1&display=image';
		if (!pnp_area_visible) {
			$('#pnp_area')
			.html('<a href="' + $('#pnp_inline_graph').attr('href') + '" style="border:0"><img src="'
				+ the_path
				+ '" style="width: 600; height: 195px" /></a>')
			.show();
			pnp_area_visible = true;
		} else {
			$('#pnp_area').hide();
			pnp_area_visible = false;
		}
		return false;
	});
});