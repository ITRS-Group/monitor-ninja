
$(document).ready(function() {

	$('#usergroup').change(function() {
		if ($(this).val() == '') return;
		$("#editmenu_form").trigger('submit');
	});

});