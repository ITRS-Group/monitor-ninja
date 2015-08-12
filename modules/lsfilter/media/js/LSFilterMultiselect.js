var lsfilter_multiselect = {
	on: {
		'update_ok': function(data) {
			if (data.source == 'multiselect')
				return;
			if (!this.elem_select)
				return;
			if (data.metadata.table && data.metadata.table != this.selection_table) {
				this.selection_table = data.metadata.table;
				this.selection = {};



				if (listview_commands[this.selection_table]) {
					this.populate_select(this.elem_select,
							listview_commands[this.selection_table]);
				} else {
					this.populate_select(this.elem_select, {});
				}

				this.elem_objtype.attr('value', this.selection_table);
			}
		}
	},
	update: function() {
		// TODO temporary alias, it's only used in extra_objects.js,
		// which should be modified somehow, by someone who
		// understands that construct
		this.on.update_ok.apply(this, arguments);
	},
	init : function(elem) {
		var self = this; // To be able to access it from within handlers
		lsfilter_main.add_listener(self);

		this.elem_select = elem;
		this.elem_objtype = $('#listview_multi_action_obj_type');
		$(document).on('click', '.multi-action-send-link', function(e) {
			e.preventDefault();
			self.do_send($(this));
			return false;
		});


		// FIXME: make this widget-safe
		$(document).on('mouseover', '.multi-action-send-link', function(e) {
			var cmd = $(this).data('multi-action-command');
			$('tr.command_dis_' + cmd).css({ opacity: 0.3 });
			return true;
		});
		$(document).on('mouseout', '.multi-action-send-link', function(e) {
			var cmd = $(this).data('multi-action-command');
			$('tr.command_dis_' + cmd).css({ opacity: 1.0 });
			return true;
		});
	},

	elem_select : false,
	elem_objtype : false,

	selection : {},
	selection_table : false,

	populate_select : function(elem, values) {
		elem.empty();
		for ( var val in values) {
			var cmd = values[val];
			var opt = $('<a />')

			opt.data('multi-action-command', val);
			opt.append($('<span />').addClass('icon-16').addClass('x16-' + cmd.icon));
			opt.append($('<span />').text(cmd.name));
			opt.addClass('multi-action-send-link');

			elem.append($('<li />').append(opt));
		}
		if(values.length == 0) {
			var li = $('<li />')
			li.text("No commands available");

			elem.append(li);
		}
	},

	do_send : function(link) {
		var action = link.data('multi-action-command');
		var selcount = $('.listview_multiselect_checkbox:checked').length;
		if (selcount == 0) {
			Notify.message('No items selected');
		} else if (!action) {
			Notify.message('No action selected');
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
	}
};
