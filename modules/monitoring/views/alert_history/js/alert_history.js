$(document).ready(function() {
	$('#show_all').on('change', show_hide_obj_sel);
	show_hide_obj_sel.call($('#show_all').get(0));
});

function show_hide_obj_sel() {
	if (this.checked)
		$('.obj_selector').hide();
	else
		$('.obj_selector').show();
}
