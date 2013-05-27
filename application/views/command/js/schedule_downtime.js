$(function() {
	$('#command_form').submit(function(ev) {
		if(!$('#field_fixed:checked').length) {
			return;
		}
		var form = $(this);
		var timestamp = new Date($('#field_start_time').val());
		var grace_time_in_ms = grace_time_in_s * 1000;
		var js_utc_offset = new Date().getTime() + new Date().getTimezoneOffset() * 60 * 1000;
		if(timestamp.getTime() < (js_utc_offset + (_server_utc_offset*1000) - grace_time_in_ms)) {
			return confirm("Since you submitted a starting time that's more than "+grace_time_in_s+" seconds old, this will be a retroactively scheduled downtime and thus it will show up separately in logs.\n\nIf this is a mistake, choose 'cancel' and correct the value.");
		}
	});
});
