$(document).ready(function() {
	$('#upload_form').submit(function() {
		if ($('#upload_file').val() == '') {
			return false;
		}
	});

	$('#dummy_href').click(function() {
		if ($('#xml_info').is(':visible')) {
			$('#xml_info').hide('fast');
		} else {
			$('#xml_info').show('fast');
		}
		return false
	});
});