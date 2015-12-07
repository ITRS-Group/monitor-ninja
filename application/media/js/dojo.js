(function (_site_domain, _index_page) {

	"use strict";

	/* QUICKLINK EXTENSION */
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
		var links = Array($('#uh_host_problems'), $('#uh_service_problems'));
		for (var i = 0; i < links.length; i += 1) {
			query_uh_objects(links[i]);
		}
	}

	setInterval(query_for_states, 10000);
	query_for_states();

	var global_quicklinks = [];

	var fix_empty_quicklink_border = function() {
		// there's a magical dot (about 1x1 pixel) hanging in the air,
		// disturbing probably just me. It's caused by having a quicklinks
		// container without any quicklinks
		var quicklinks = $('#dojo-quicklink-external');
		var tr = quicklinks.parent();
		if(quicklinks.find('li').length) {
			tr.css('borderWidth', 1);
		} else {
			tr.css('borderWidth', 0);
		}
	};

	function quicklinks_save_all () {
		$.ajax(_site_domain + _index_page + '/ajax/save_page_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'setting': JSON.stringify(global_quicklinks),
				'csrf_token': _csrf_token
			},
			type: 'POST',
			complete: function() {
				$('#dojo-add-quicklink-href').attr('value','');
				$('#dojo-add-quicklink-title').attr('value','');
				$('#dojo-add-quicklink-icon').attr('value','');
				fix_empty_quicklink_border();
			}
		});
	}

	$('#dojo-icon-container').on('click', 'span', function() {
		var span = $(this);
		$('#dojo-add-quicklink-icon').val(span.data('icon'));

		// we have to change the background of the td, since the span already
		// has the icon image as its background
		var all_tds = $('#dojo-icon-container td');
		all_tds.removeClass('highlight');
		span.parents('td').addClass('highlight');
	});

	$('#dojo-add-quicklink').fancybox({
		titleShow: false,
		overlayOpacity: 0,
		onComplete: function() {
			$('#dojo-quicklink-remove').html('');
			for (var i = 0; i < global_quicklinks.length; i += 1) {
				var l = global_quicklinks[i];
				var vid = l.title + ':'+ l.href;
				var quicklink = $('<li><label></label> (<a target="_blank" class="external"></a>)</li>');
				quicklink
					.find('label')
						.text(l.title)
						.prepend($('<span class="icon-16"></span>').addClass('x16-'+l.icon))
						.prepend($('<input type="checkbox" />')
							.attr('value', vid)
							.attr('id', vid)
							.attr('title', l.title)
						);
				quicklink
					.find('a')
						.attr('href', l.href)
						.text(l.href);
				$('#dojo-quicklink-remove').append(quicklink);
			}
		},
		onClose: function() {
			$('#dojo-add-quicklink-href').attr('value','');
			$('#dojo-add-quicklink-title').attr('value','');
			$('#dojo-add-quicklink-icon').attr('value','');
			fix_empty_quicklink_border();
		}
	});

	$('#dojo-add-quicklink-menu form').submit(function (ev) {
		ev.preventDefault();
		var href = $('#dojo-add-quicklink-href').attr('value'),
				title = $('#dojo-add-quicklink-title').attr('value'),
				icon = $('#dojo-add-quicklink-icon').attr('value'),
				target = $('#dojo-add-quicklink-target').attr('value'),
				changed = false;
		var error = '';
		if (href && title && icon) {
			var i = global_quicklinks.length;
			for (i; i--;) {
				if (global_quicklinks[i].href === href) {
					error += 'This href is already used in a quicklink. <br />';
				}
				if (global_quicklinks[i].title === title) {
					error += 'This title is already in use, titles must be unique. <br />';
				}
			}
			if (error.length === 0) {
				global_quicklinks.push({'href': href,'title': title,'icon': icon,'target': target});
				var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-' + icon + '"></span></a></li>');
				quicklink
					.find('a')
						.attr('target', target)
						.attr('href', href)
						.attr('title', title);
				$('#dojo-quicklink-external').append(quicklink);
				changed = true;
			} else {
				Notify.message(error, {type: "error"});
				return;
			}
		}
		$('#dojo-quicklink-remove input[type="checkbox"]').each(function () {
			var i = global_quicklinks.length;
			var vid = '';
			if (this.checked) {
				for (i; i--;) {
					vid = global_quicklinks[i].title + ':' + global_quicklinks[i].href;
					if (this.value === vid) {
						$('#dojo-quicklink-external li a[title="'+this.title+'"]').parent().remove();
						global_quicklinks.splice(i, 1);
						changed = true;
					}
				}
			}

		});
		if (changed)  {
			quicklinks_save_all();
		}
		if(!error) {
			$.fancybox.close();
		}
	});

	$.ajax(_site_domain + _index_page + '/ajax/get_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'csrf_token': _csrf_token
			},
			type: 'POST',
			complete: function() {
				fix_empty_quicklink_border();
			},
			success: function (obj) {

				var links = [];

				if (obj['dojo-quicklinks']) {
					links = obj['dojo-quicklinks'];
					for (var i = 0; i < links.length; i += 1) {
						var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-'+links[i].icon+'"></span></a></li>');
						quicklink
							.find('a')
								.attr('target', links[i].target)
								.attr('href', links[i].href)
								.attr('title', links[i].title);

						$('#dojo-quicklink-external').append(quicklink);
					}
				}
				global_quicklinks = links;
			}
		});

}(window._site_domain, window._index_page));

