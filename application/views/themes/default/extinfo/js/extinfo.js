$(document).ready(function() {
	$('.extinfo_contactgroup').each(function() {
		$(this).bind('click', function() {
			var the_id = $(this).attr('id');
			the_id = the_id.replace('extinfo_contactgroup_', '');
			$('#extinfo_contacts_' + the_id).toggle();
		});
	});

	setTimeout('hide_del_msg()', 3000);

	$('.deletecommentbox_host').hide();
	$('#selectall_host').hide();
	$('.td_host_checkbox').hide();
	$('.submithost').hide();

	$('.deletecommentbox_service').hide();
	$('#selectall_service').hide();
	$('.td_service_checkbox').hide();
	$('.submitservice').hide();

	// restore left border for first cell of each row
	$('table').find('tr:eq(0) th:eq(0)').css('border-left', '1px solid #dcdccd');
	$('table').find('tr td:nth-child(2)').css('border-left', '1px solid #dcdccd');

	var host_hidden = true;
	$('#select_multiple_delete_host').click(function() {
		if (!host_hidden) {
			$('.deletecommentbox_host').hide();
			$('.td_host_checkbox').hide();
			$('#selectall_host').hide();
			$('.submithost').hide();
			host_hidden = true;
		} else {
			$('.deletecommentbox_host').show();
			$('.td_host_checkbox').show();
			$('#selectall_host').show();
			$('.submithost').show();
			host_hidden = false;
		}
		return false;
	});

	var svc_hidden = true;
	$('#select_multiple_delete_service').click(function() {
		if (!svc_hidden) {
			$('.deletecommentbox_service').hide();
			$('.td_service_checkbox').hide();
			$('#selectall_service').hide();
			$('.submitservice').hide();
			svc_hidden = true;
		} else {
			$('.deletecommentbox_service').show();
			$('.td_service_checkbox').show();
			$('#selectall_service').show();
			$('.submitservice').show();
			svc_hidden = false;
		}
		return false;
	});

	$('#selectall_host').change(function() {
		$('.deletecommentbox_host').each(function() {
			$(this).attr('checked', $('#selectall_host').attr('checked'));
		});
	});
	$('#selectall_service').change(function() {
		$('.deletecommentbox_service').each(function() {
			$(this).attr('checked', $('#selectall_service').attr('checked'));
		});
	});

});

function hide_del_msg() {
	$('#comment_del_msg').hide('slow');
}