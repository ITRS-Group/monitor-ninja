(function() {

	function append_quicklink_to_dom(link) {
		var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-'+link.icon+'"></span></a></li>');
		quicklink.find('a')
			.attr('target', link.target)
			.attr('href', link.href)
			.attr('title', link.title);

		$('#dojo-add-quicklink').parent().before(quicklink);
	};

	$(document).on("click", "#dojo-add-quicklink", function(ev) {
		ev.preventDefault();
		var link = $(this);
		LightboxManager.ajax_form_from_href(link.attr("title"), link.attr("href"));
	});

	$(document).on("submit", ".nj-form[action$='/quicklink/index']", function(ev) {
		var form = $(this);
		ev.preventDefault();
		$.post(form.attr("action"), form.serialize())
			.done(function(data) {
				// render the newly created quicklink in the menu bar
				// to avoid having to reload the page
				var icon = form.find("input[name=icon]");
				var target = form.find("input[name=target]");
				var href = form.find("input[name=href]");
				var title = form.find("input[name=title]");
				append_quicklink_to_dom({
					"icon": icon.val(),
					"target": target.val(),
					"href": href.val(),
					"title": title.val()
				});
				LightboxManager.remove_topmost();
			})
			.fail(function(data) {
				var msg = JSON.parse(data.responseText).result;
				LightboxManager.alert(msg);
			});
	});

	$(document).on("click", ".remove_quicklink", function(ev) {
		ev.preventDefault();

		var a = $(this);
		var quicklink_title = a.data("title");
		var url = a.attr("href");
		var data = a.data();
		// since we are using POST, we must not forget to attach the currently
		// valid CSRF token
		data.csrf_token = _csrf_token;
		LightboxManager.confirm(
			"Are you sure you want to remove the quicklink '"+quicklink_title+"'?",
			{
				"yes": {
					"text": "Remove quicklink",
					"cb": function() {
						$.post(url, data)
							.done(function(data) {
								a.closest("tr").remove();

								// Remove the rendered
								// quicklink from the
								// main menu bar
								$("#quicklinks a")
									.filter(function() {
										// In order to compare hrefs, we cannot do hrefA == hrefB,
										// since the stored href is relative, but the one accessed
										// through the DOM is absolute. We work around this
										// issue by making the stored href absolute.
										var anchor = document.createElement("a");
										anchor.href = data.result.href;
										return this.href === anchor.href
											&& this.title === data.result.title;
									})
									.closest("li")
									.remove();
							})
							.fail(function(data) {
								var msg = data.result;
								LightboxManager.alert(msg);
							});
					}
				},
				"focus": "yes"
			}
		);
		return false;
	});

	var uh_prob_title = "Unhandled Problems";
	function query_uh_objects(link) {

		var basepath = _site_domain + _index_page,
			query = link.attr('href'),
			shield_class = 'icon-16 x16-shield-ok',
			uh_prob_title = '',
			totals = 0;
		query = query.split('q=')[1];
		var obj_type = link.attr('id').split('_')[1];

		$.ajax({
			url : basepath + "/listview/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : query,
				"limit" : 100,
				"columns": ['description']
			},
			success : function(data) {

				if (!data) {
					return;
				}

				if (obj_type === 'host') {
					totals = data.totals.host_all[1];
					uh_prob_title = totals + ' unacknowledged host(s) in Down state!';
					if (data.totals.host_state_down[1] > 0) {
						shield_class = 'icon-16 x16-shield-critical';
					} else if (data.totals.host_state_unreachable[1] > 0) {
						shield_class = 'icon-16 x16-shield-unknown';
					}
				} else if (obj_type === 'service') {
					totals = data.totals.service_all[1];
					uh_prob_title = totals + ' unacknowledged service(s) in Critical/Warning state!';
					if (data.totals.service_state_critical[1] > 0) {
						shield_class = 'icon-16 x16-shield-critical';
					} else if (data.totals.service_state_warning[1] > 0) {
						shield_class = 'icon-16 x16-shield-warning';
					} else if (data.totals.service_state_unknown[1] > 0) {
						shield_class = 'icon-16 x16-shield-unknown';
					}
				}

				var uh_prob_state_element = link.find(':nth-child(2)');

				if(totals < 100) {
					// Only set text if there are less than 100 to prevent overflow
					uh_prob_state_element.text(totals);
				}

				link.attr('title', uh_prob_title);
				link.find(':first-child').removeClass().addClass(shield_class);
			}
		});
	}

	function query_for_states() {
		var links = $('#uh_host_problems,#uh_service_problems').each(function () {
			query_uh_objects($(this));
		});
	}

	$(document).ready(function() {
		setInterval(query_for_states, 10000);
		$(window).on('load', function () {
			query_for_states();
		});

		$.ajax(_site_domain + _index_page + '/ajax/get_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'csrf_token': _csrf_token
			},
			type: 'POST',
			success: function (obj) {
				if (!obj['dojo-quicklinks']) {
					return;
				}
				obj['dojo-quicklinks'].forEach(append_quicklink_to_dom);
			}
		});
	});
})();
