var lsfilter_multiselect = {
	update : function(data) {
		if (data.source == 'multiselect')
			return;
		if (!this.elem_select)
			return;
		if (data.metadata.table && data.metadata.table != this.selection_table) {
			this.selection_table = data.metadata.table;
			this.selection = {};

			if (this.commands[this.selection_table]) {
				this.populate_select(this.elem_select,
						this.commands[this.selection_table]);
			} else {
				this.populate_select(this.elem_select, this.commands['other']);
			}

			this.elem_objtype.attr('value', this.selection_table);
		}
	},
	init : function(elem) {
		var self = this; // To be able to access it from within handlers

		this.elem_select = elem;
		this.elem_objtype = $('#listview_multi_action_obj_type');
		$(document).on('click', 'a.multi-action-send-link', function(e) {
			e.preventDefault();
			self.do_send($(this));
			return false;
		});
	},

	elem_select : false,
	elem_objtype : false,

	selection : {},
	selection_table : false,

	commands : {
		'hosts' : {
			'' : _('Select action'),
			'SCHEDULE_HOST_DOWNTIME' : _('Schedule downtime'),
			'DEL_HOST_DOWNTIME' : _('Cancel Scheduled downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM' : _('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT' : _('Remove problem acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS' : _('Disable host notifications'),
			'ENABLE_HOST_NOTIFICATIONS' : _('Enable host notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS' : _('Disable notifications for all services'),
			'DISABLE_HOST_CHECK' : _('Disable active checks'),
			'ENABLE_HOST_CHECK' : _('Enable active checks'),
			'SCHEDULE_HOST_CHECK' : _('Reschedule host checks'),
			'ADD_HOST_COMMENT' : _('Add host comment'),
			'NACOMA_DEL_HOST' : _('Delete hosts')
		},
		'services' : {
			'' : _('Select action'),
			'SCHEDULE_SVC_DOWNTIME' : _('Schedule downtime'),
			'DEL_SVC_DOWNTIME' : _('Cancel Scheduled downtime'),
			'ACKNOWLEDGE_SVC_PROBLEM' : _('Acknowledge'),
			'REMOVE_SVC_ACKNOWLEDGEMENT' : _('Remove problem acknowledgement'),
			'DISABLE_SVC_NOTIFICATIONS' : _('Disable service notifications'),
			'ENABLE_SVC_NOTIFICATIONS' : _('Enable service notifications'),
			'DISABLE_SVC_CHECK' : _('Disable active checks'),
			'ENABLE_SVC_CHECK' : _('Enable active checks'),
			'SCHEDULE_SVC_CHECK' : _('Reschedule service checks'),
			'ADD_SVC_COMMENT' : _('Add service comment'),
			'NACOMA_DEL_SERVICE' : _('Delete services')
		},
		'comments' : {
			'' : _('Select action'),
			// This is actually a macro of delete svc comments, which can handle
			// both hosts and service comments
			'DEL_COMMENT' : _('Delete comments')
		},
		'downtimes' : {
			'' : _('Select action'),
			// This is actually a macro of delete svc comments, which can handle
			// both hosts and service comments
			'DEL_DOWNTIME' : _('Delete downtimes')
		},
		'other' : {
			'' : _('Table doesn\'t support multi action')
		}
	},

	populate_select : function(elem, values) {
		elem.empty();
		for ( var val in values) {
			var tag = values[val];
			elem.append($('<li />').append($('<a href="#" />').text(tag).data('value', val).addClass('multi-action-send-link')));
		}
	},

	do_send : function(link) {
		var action = link.data('value');
		var selcount = $('.listview_multiselect_checkbox:checked').length;
		if (selcount == 0) {
			this.notice('No items selected');
		} else if (!action) {
			this.notice('No action selected');
		} else {
			$('#listview_multi_action_obj_action').attr('value', action);
			$('#listview_multi_action_form').submit();
		}
	},
	box_register : function(key, value) {
		this.selection[key] = value;
	},
	box_selected : function(key) {
		if (this.selection[key])
			return true;
		return false;
	},

	notice_timeout : false,

	notice : function(msg) {
		var notice_container = $('#multi-action-message');
		var self = this;
		if (this.notice_timeout) {
			clearTimeout(this.notice_timeout);
		}
		notice_container.text(msg);
		this.notice_timeout = setTimeout(function() {
			this.notice_timeout = false;
			notice_container.empty();
		}, 3000);
	}
};
