$(document).ready(function() {

	$('#command_form').bind('submit', function() {
		var form = $(this);
		if(form.data('already_submitted')) {
			return false;
		}
		/*
		 *	Since all command input fields are required, we check
		 *	them all and prompt user in case they are empty.
		 */

		var requested_command = $('input[name=requested_command]').val();
		if (requested_command != 'DEL_ALL_SVC_COMMENTS' && requested_command != 'DEL_ALL_HOST_COMMENTS') {
			// don't select all options in multi-select if we are trying to delete
			// service/host comments since we want to let the user choose objects
			$('select').each(function() {
				if ($(this).attr('multiple')) {
					$(this).children('option').attr('selected', true);
				}
			});
		}
		var inputs = $('#command_form input');
		$('select[type="select-multiple"]').each(function() {
			$(this).children('option').attr('selected', true);
		});
		var err_str = '';
		inputs.each(function() {
			var val = $(this).val();
			var key_str = /\[.*?\]/.exec(this.name);
			if (key_str) {
				key_str = key_str[0].replace('[', '').replace(']', '');
				if ((key_str == 'duration' && $("input[name='cmd_param\\[fixed\\]']").attr('checked') && $.trim(val) == '') ||
					( key_str != '_perfdata' && (typeof val == 'string' && $.trim(val) == '') || (typeof val == 'object' && !val.length) )) {
					// Only require 'duration' when 'fixed' is checked
					err_str += ' - ' + sprintf(_command_empty_field, key_str)+"\n";
				}
			}
		});
		if (err_str.length) {
			// alert user using translated string from master template
			alert(sprintf(_form_error_header, "\n", "\n\n") + err_str);
			return false;
		}
		form.data('already_submitted', true);
		return true;
	});

	var hidden_by_fixed = $('#field_duration, #field_trigger_id').parents('tr');
	var fixed = $('#field_fixed');

	if (fixed.is(':checked')) {
		hidden_by_fixed.hide();
	}


	fixed.click(function() {
		if (fixed.is(':checked')) {
			hidden_by_fixed.hide();
		} else {
			hidden_by_fixed.show();
		}
	})
});
