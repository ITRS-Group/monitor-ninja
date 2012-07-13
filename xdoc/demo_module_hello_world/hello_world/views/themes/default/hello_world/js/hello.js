$(document).ready(function() {
	$('#hello_header').click(function() {
		var data = $('#hello_data');
		if (data.is(':visible')) {
			data.hide();
		} else {
			data.show();
		}
	});
});
