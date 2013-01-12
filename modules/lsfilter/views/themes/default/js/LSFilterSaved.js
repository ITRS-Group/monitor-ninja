var lsfilter_saved = {

	update : function(data) {
		if( data.source == 'saved' ) return;
		this.last_query = data.query;
	},
	init : function() {
		var self = this; // To be able to access it from within handlers

		this.refresh_filter_list();
		$('#lsfilter_save_filter').click(function() {
			$(this).addClass('saving').text(_('Saving...'));
			self.save($('#filter_query').val());
		})
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
			type : 'POST',
			success : function(data) {
				$('.filter-query-saved-hide-x').removeAttr('checked');
				var list = $("#filter-query-saved-filters").empty();
				for ( var filter in data.data) {
					(function() {
						// Crazy hack with function()... why can't I access
						// save['query'] in the callback? (and how do I fix?)
						var save = data.data[filter];
						var current_icon = self.icons.other;

						var parser = new LSFilter(new LSFilterPreprocessor(),
								new LSFilterMetadataVisitor());
						var metadata = parser.parse(save.query);
						if (self.icons[metadata.table])
							current_icon = self.icons[metadata.table];

						var link = link_query(save.query);

						link.append(current_icon);
						link.append(save.scope.toUpperCase());
						link.append(' - ');
						link.append(save.name);

						link.hover(function() {
							$('#filter-query-saved-preview')
									.text(save['query']);
						}, function() {
							$('#filter-query-saved-preview').empty();
						});

						list.append($(
								'<li class="saved-filter-' + save['scope']
										+ '" />').append(link));
					})();
				}

			}
		};
		$.ajax(_site_domain + _index_page + '/listview/fetch_saved_queries',
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

			$.ajax(basepath + '/listview/save_query', {
				data : save,
				type : 'GET',
				complete : function(xhr) {
					$('#lsfilter_save_filter').removeClass().text(_('Save'));
					self.refresh_filter_list();
				}
			});

		} else {
			$.jGrowl(_('You must give the filter a name!'));
			$('#lsfilter_save_filter').removeClass().text(_('Save'));
		}
	}
};