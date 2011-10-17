$(document).ready(function() {
	var old_refresh = 0;

	$('.filterboxfield').focus(function() {
		if (!$('#ninja_refresh_control').attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
			$("#ninja_refresh_control").attr('checked', true);
		}
	});

	$('.filterboxfield').blur(function() {
		if ($(this).val() != '') {
			// don't do anything if we have a current filter value
			return;
		}
		if ($('#ninja_refresh_control').attr('checked')) {
			// restore previous refresh rate
			ninja_refresh(old_refresh);
			$("#ninja_refresh_control").attr('checked', false);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});


	/**
		========================
		comment filters
		========================
	*/
	// host comments
	$('#hostcomments_table').tablesorter({headers:{0:{sorter:false}}});

	$('#clearhostsearch').click(function() {
		$('#hostfilterbox').val('').trigger('keyup').trigger('blur');
		return false;
	});

	$('#hostfilterbox').keyup(function(){
		filter_table(this, 'hostcomments_table');})
		.focus(function(){
			if(this.value==_filter_label) {
				this.value='';
			}
	})
	.blur(function() {
		if (this.value == '') {
			this.value = _filter_label;
		}
	});

	// service comments
	$('#servicecomments_table').tablesorter({headers:{0:{sorter:false}}});

	$('#clearservicesearch').click(function() {
		$('#servicefilterbox').val('').trigger('keyup').trigger('blur');
		return false;
	});

	$('#servicefilterbox').keyup(function(){
		filter_table(this, 'servicecomments_table');})
		.focus(function(){
			if(this.value==_filter_label) {
				this.value='';
			}
	})
	.blur(function() {
		if (this.value == '') {
			this.value = _filter_label;
		}
	});


	/**
		========================
		scheduled downtime filters
		========================
	*/
	// host scheduled downtime
	$('#scheduled_host_downtime').tablesorter({headers:{0:{sorter:false}}});

	$('#clearhostsearch_sched').click(function() {
		$('#hostfilterbox_sched').val('').trigger('keyup').trigger('blur');
		return false;
	});

	$('#hostfilterbox_sched').keyup(function(){
		filter_table(this, 'scheduled_host_downtime');})
		.focus(function(){
			if(this.value==_filter_label) {
				this.value='';
			}
	})
	.blur(function() {
		if (this.value == '') {
			this.value = _filter_label;
		}
	});


	// service scheduled downtime
	$('#scheduled_service_downtime').tablesorter({headers:{0:{sorter:false}}});

	$('#clearservicesearch_sched').click(function() {
		$('#servicefilterbox_sched').val('').trigger('keyup').trigger('blur');
		return false;
	});

	$('#servicefilterbox_sched').keyup(function(){
		filter_table(this, 'scheduled_service_downtime');})
		.focus(function(){
			if(this.value==_filter_label) {
				this.value='';
			}
	})
	.blur(function() {
		if (this.value == '') {
			this.value = _filter_label;
		}
	});



	$('.extinfo_contactgroup').each(function() {
		$(this).bind('click', function() {
			var the_id = $(this).attr('id');
			the_id = the_id.replace('extinfo_contactgroup_', '');
			$('#extinfo_contacts_' + the_id).toggle();
		});
	});

	setTimeout('hide_del_msg()', 3000);

	// restore left border for first cell of each row
	$('table').find('tr:eq(0) th:eq(0)').css('border-left', '1px solid #dcdccd');
	$('table').find('tr:eq(0) th:eq(1)').css('border-left', '1px solid #dcdccd');
	$('table').find('tr td:nth-child(2)').css('border-left', '1px solid #dcdccd');

	$('#del_host_downtime_form').bind('submit', function() {
		//return false;
	});

	var validate_host_submit = function() {
		$('.deletecommentbox_host').each(function() {
			if (!$(this).is(':visible'))
				$(this).attr('checked', false);
		});
		if (!$('.deletecommentbox_host').filter(':checked').length) {
			$('.host_feedback').text('   Nothing selected...');
			setTimeout(function() {$('.host_feedback').hide();}, 5000);
			return false;
		} else {
			$('.host_feedback').text('');
		}
	}

	$('#del_submithost_svc').click(function() {
		if (validate_host_submit() == false)
			return false;

		var hostform = $(this).parents('form');
		// borderline evil:
		// this moves the checkboxes from the service form to the host form before submitting.
		var cb = trigger_cb_on_nth_call(function() {hostform.submit();}, $('.deletecommentbox_host').length);
		hostform.append('<input type="hidden" name="del_submithost_svc" value="1" />');
		$('.deletecommentbox_host').each(function() {
			if ($(this).attr('checked')) {
				var hostname = $(this).closest('tr').find('td:nth-child(2)').text();
				$('#scheduled_service_downtime').find('tr td:nth-child(2)').each(function() {
					if ($(this).text() == hostname) {
						hostform.append($(this).closest('tr').find('td:nth-child(1) input[type="checkbox"]').css('display', 'none').attr('checked', true));
					}
				});
			}
			cb();
		});
		return false;
	});

	// check that user selected any checkboxes before submitting
	$('#del_submithost').click(validate_host_submit);

	$('#del_submitservice').click(function() {
		$('.deletecommentbox_service').each(function() {
			if (!$(this).is(':visible'))
				$(this).attr('checked', false);
		});
		if (!$('.deletecommentbox_service').filter(':checked').length) {
			$('.service_feedback').text('   Nothing selected...');
			setTimeout(function() {$('.service_feedback').hide();}, 5000);
			return false;
		} else {
			$('.service_feedback').text('');
		}
	});
});

function hide_del_msg() {
	$('#comment_del_msg').hide('slow');
}

function filter_table (phrase, _id){
	var words = phrase.value.toLowerCase().split(" ");
	var table = document.getElementById(_id);
	var ele;

	for (var r = 1; r < table.rows.length; r++){
		ele = table.rows[r].innerHTML.replace(/<[^>]+>/g,"");
		var displayStyle = 'none';
		if (table.rows[r].className.indexOf('submit') == -1) {
			for (var i = 0; i < words.length; i++) {
				if (ele.toLowerCase().indexOf(words[i])>=0)
					displayStyle = '';
				else {
					displayStyle = 'none';
					break;
				}
			}
		}
		table.rows[r].style.display = displayStyle;
	}
};
