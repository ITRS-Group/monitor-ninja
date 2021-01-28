
$(function() {

	$('#usergroup').on('change', function() {
		if ($(this).val() == '') return;
		$("#editmenu_form").trigger('submit');
	});

});