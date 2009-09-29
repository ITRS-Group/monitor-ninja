$(document).ready(function() {

	$('#command_form').bind('submit', function() {
		/*
		 *	Since all command input fields are required, we check
		 *	them all and prompt user in case they are empty.
		 */
		var inputs = $('#command_form :input');
		var empty = 0;
		var err_str = '';
		inputs.each(function() {
			var val = $(this).val();
			var key_str = /\[.*?\]/.exec(this.name);
			if (key_str) {
				key_str = key_str[0];
				key_str = key_str.replace('[', '');
				key_str = key_str.replace(']', '');
				if (key_str == 'duration') {
					// Only require 'duration' when 'fixed' is checked
					if ($("input[name='cmd_param\\[fixed\\]']").attr('checked') && $.trim(val) == '') {
						err_str += ' - ' + sprintf(_command_empty_field, $("#" + key_str).text())+"\n";
						empty++;
					}
				} else {
					if ($.trim(val) == '') {
						err_str += ' - ' + sprintf(_command_empty_field, $("#" + key_str).text())+"\n";
						empty++;
					}
				}
			}
		});
		if (empty != 0) {
			// alert user using translated string from master template
			alert(sprintf(_form_error_header, "\n", "\n\n") + err_str);
			return false;
		}
		return true;
	});

});
