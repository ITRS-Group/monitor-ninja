$(document).ready(function() {
	$('#type').bind('change', function() {
		$("#notification_form").trigger('submit');
	});
});
