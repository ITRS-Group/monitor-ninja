$(function() {
	$('#command_form').submit(function(ev) {
		if(!$('#field_fixed:checked').length) {
			return;
		}
		var form = $(this);
		var timestamp = new Date($('#field_start_time').val());
		var grace_time_in_ms = grace_time_in_s * 1000;
		console.log(timestamp.getTime());
		console.log(new Date().getTime() - grace_time_in_ms);
		if(timestamp.getTime() < (new Date().getTime() - grace_time_in_ms)) {
			return confirm("Since you submitted a starting time that's more than "+grace_time_in_s+" seconds old, this will be a retroactively scheduled downtime and thus it will show up separately in logs.\n\nIf this is a mistake, choose 'cancel' and correct the value.");
		}
	});
});
