var lsfilter_saved = {

	on: {
		'update_ok': function(data) {
			if (data.source == 'saved')
				return;
			this.last_query = data.query;
		}
	},
	init : function() {
		var self = this; // To be able to access it from within handlers

		$(document).on('click', '#lsfilter_save_filter', function() {
			$(this).addClass('saving').text(_('Saving...'));
			self.save($('#filter_query').val());
		});
	},

	icons : {
		hosts : '<span class="icon-menu menu-host"></span>',
		services : '<span class="icon-menu menu-service"></span>',
		hostgroups : '<span class="icon-menu menu-hostgroupsummary"></span>',
		servicegroups : '<span class="icon-menu menu-servicegroupsummary"></span>',
		other : '<span class="icon-menu menu-eventlog"></span>'
	},
	last_query : false,

	refresh_filter_list : function() {
		var self = this; // To be able to access it from within handlers
		var ajax_obj = {
			data : {
				'type' : 'lsfilters_saved',
				'page' : 'listview'
			},
			type : 'GET',
			success : function(data) {
				var list = $("#saved-filters-menu").empty();
				for ( var filter in data.data) {
					/* Someone broke the Array prototype, aaarrrrgggghhhh!!! */
					if (isNaN(parseInt(filter)))
						continue;
					(function() {
						var save = data.data[filter];
						var current_icon = self.icons.other;

						var table = 'hosts';
						if (save.table)
							table = save.table;

						if (self.icons[table])
							current_icon = self.icons[table];

						var link = link_query(save.query).addClass(
								'ninja_menu_links');

						link.append(current_icon);
						link.append($('<span class="nav-seg-span" />').text(
								save.name));

						list.append($('<li class="nav-seg" />').append(link));
					})();
				}
				var link = link_query('[saved_filters] all').addClass(
						'ninja_menu_links');
				link.append(self.icons['other']);
				link.append($('<span class="nav-seg-span" />').text(
						'Manage and view filters'));
				list.prepend($('<li class="nav-seg" />').append(link));
			}
		};
		$.ajax(_site_domain + _index_page + '/listview/fetch_saved_filters',
				ajax_obj);
	},

	save : function() {
		var self = this; // To be able to access it from within handlers

		var basepath = _site_domain + _index_page;
		var save = {
			"query" : this.last_query,
			"scope" : "user"
		};
		var name = $('#lsfilter_save_filter_name').val();

		if (!this.last_query)
			return;

		if (name) {

			if ($('#lsfilter_save_filter_global').attr('checked')) {
				save["scope"] = "global";
			}

			save['name'] = name;

			$.ajax(basepath + '/listview/save_filter', {
				data : save,
				type : 'GET',
				complete : function(xhr) {
					$('#lsfilter_save_filter').removeClass().text(_('Save'));
					self.refresh_filter_list();
					lsfilter_main.refresh();
				}
			});

		} else {
			Notify.message(_('You must give the filter a name!'));
			$('#lsfilter_save_filter').removeClass().text(_('Save'));
		}
	}
};

$(function() {
	lsfilter_saved.refresh_filter_list();
});
