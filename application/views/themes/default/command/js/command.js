$(document).ready(function() {

	$('#command_form').bind('submit', function() {
		/*
		 *	Since all command input fields are required, we check
		 *	them all and prompt user in case they are empty.
		 */

		$('select').each(function() {
			if ($(this).attr('multiple')) {
				$(this).children(':option').attr('selected', true);
			}
		});
		var inputs = $('#command_form :input');
		$('select[type="select-multiple"]').each(function() {
			$(this).children(':option').attr('selected', true);
		});
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
					if ( key_str != '_perfdata' && (typeof val == 'string' && $.trim(val) == '') || (typeof val == 'object' && !val.length) ) {
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

	if ($('#field_fixed').is(':checked')) {
		showhide_some_fields(new Array('duration', 'trigger_id'), 0);
	}

	$('#field_fixed').click(function() {
		if ($('#field_fixed').is(':checked')) {
			showhide_some_fields(new Array('duration', 'trigger_id'), 0);
		} else {
			showhide_some_fields(new Array('duration', 'trigger_id'), 1);
		}
	})

	$('#Commit').click(function() {
		$(this).attr('disabled', true);
	});
});


function showhide_some_fields(fieldarr, state)
{
	if (!fieldarr.length) {
		return false;
	}
	if (!state) {
		for (field in fieldarr) {
			$('#' + fieldarr[field]).closest('tr').hide();
		}
	} else {
		for (field in fieldarr) {
			$('#' + fieldarr[field]).closest('tr').show();
		}
	}
}