
var listview_selection = [];
var listview_selection_type = "";

function populate_select(elem, values) {
	elem.empty();
	for( var val in values ) {
		var tag = values[val];
		elem.append($('<option />').text(tag).attr('value',val));
	}
}

var multiselect_commands = {
		'hosts': {
			'': _('Select action'),
			'SCHEDULE_HOST_DOWNTIME': _('Schedule downtime'),
			'DEL_HOST_DOWNTIME': _('Cancel Scheduled downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM': _('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT': _('Remove problem acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS': _('Disable host notifications'),
			'ENABLE_HOST_NOTIFICATIONS': _('Enable host notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS': _('Disable notifications for all services'),
			'DISABLE_HOST_CHECK': _('Disable active checks'),
			'ENABLE_HOST_CHECK': _('Enable active checks'),
			'SCHEDULE_HOST_CHECK': _('Reschedule host checks'),
			'ADD_HOST_COMMENT': _('Add host comment')
		},
		'services': {
			'': _('Select action'),
			'SCHEDULE_SVC_DOWNTIME': _('Schedule downtime'),
			'DEL_SVC_DOWNTIME': _('Cancel Scheduled downtime'),
			'ACKNOWLEDGE_SVC_PROBLEM': _('Acknowledge'),
			'REMOVE_SVC_ACKNOWLEDGEMENT': _('Remove problem acknowledgement'),
			'DISABLE_SVC_NOTIFICATIONS': _('Disable service notifications'),
			'ENABLE_SVC_NOTIFICATIONS': _('Enable service notifications'),
			'DISABLE_SVC_CHECK': _('Disable active checks'),
			'ENABLE_SVC_CHECK': _('Enable active checks'),
			'SCHEDULE_SVC_CHECK': _('Reschedule service checks'),
			'ADD_SVC_COMMENT': _('Add service comment')
		},
		'other': {
			'': _('Table doesn\'t support multi action')
		}
};

function multi_select_refresh() {
	if( listview_current_table && listview_selection_type != listview_current_table ) {
		listview_selection_type = listview_current_table;
		listview_selection = [];
		if( multiselect_commands[listview_current_table] ) {
			populate_select($('#multi_action_select'), multiselect_commands[listview_current_table] );
		} else {
			populate_select($('#multi_action_select'), multiselect_commands['other'] );
		}
		$('#listview_multi_action_obj_type').attr('value',listview_current_table);
	}
}