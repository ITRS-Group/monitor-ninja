var lsfilter_multiselect = {
	on: {
		'update_ok': function(data) {
			if (data.source == 'multiselect')
				return;
			if (!this.elem_menu)
				return;
			if (data.metadata.table && data.metadata.table != this.selection_table) {
				this.selection_table = data.metadata.table;
				this.selection = {};



				if (listview_commands[this.selection_table]) {
					this.populate_select(listview_commands[this.selection_table]);
				} else {
					this.populate_select({});
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

		this.elem_menu = elem;
		this.elem_objtype = $('#listview_multi_action_obj_type');
		$(document).on('click', '.multi-action-send-link', function(e) {
			e.preventDefault();
			self.do_send($(this));
			return false;
		});
	},

	elem_menu : false,
	elem_objtype : false,

	selection : {},
	selection_table : false,

	populate_select : function(commands) {
		this.elem_menu.empty();
		var categories = {};
		var cmd_count = 0;

		for ( var cmdname in commands) {
			var cmd = commands[cmdname];
			// Redirect commands can't be applied in multi aciton
			if(!cmd['redirect']) {
				if(!categories[cmd.category]) {
					categories[cmd.category] = {}
				}
				categories[cmd.category][cmdname] = cmd;

				cmd_count++;
			}
		}

		if(cmd_count == 0) {
			this.elem_menu.empty().text("No commands available");
		} else {
			for ( var category in categories) {
				var category_commands = categories[category];
				var cat_list = $('<ul />');
				cat_list.append($('<li />').text(category).addClass('multi-action-title'));

				for ( var cmdname in category_commands) {
					// del_downtime_by_host_name is for the API only
					if (cmdname !== "del_downtime_by_host_name") {
						var cmd = category_commands[cmdname];
						var opt = $('<a href="#"/>')

						opt.attr('data-multi-action-command', cmdname);
						opt.text(cmd.name);
						opt.prepend($('<span />').addClass('icon-16').addClass('x16-' + cmd.icon));
						opt.addClass('multi-action-send-link');

						cat_list.append($('<li />').append(opt));
					}
				}
				this.elem_menu.append(cat_list);
			}
		}
	},

	do_send : function(link) {
		var action = link.attr('data-multi-action-command');
		var selcount = $('.listview_multiselect_checkbox:checked').length;
		if (selcount == 0) {
			Notify.message('No items selected');
		} else if (!action) {
			Notify.message('No action selected');
		} else {
			$('#listview_multi_action_obj_action').attr('value', action);
			$('#listview_multi_action_form').trigger('submit');
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
