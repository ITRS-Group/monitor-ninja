$(document).ready(function() {
	$('.menubox').click(function() {
		var the_id = $(this).attr('id');
		the_id = the_id.replace('checkbox_', '');
		if ($(this).attr('checked')) {
			$('#' + the_id).css('color', '#c0c0c0');
		} else {
			$('#' + the_id).css('color', '#000000');
		}
	});

	$('#editmenu_username').change(function() {
		if ($(this).val() == '') return;
		$("#editmenu_form").trigger('submit');
	});
});