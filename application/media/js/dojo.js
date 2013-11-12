(function (_site_domain, _index_page) {

	"use strict";

	/* QUICKLINK EXTENSION */
	var uh_prob_title = "Unhandled Problems",
		uh_prob_state_element = null;
	function query_for_states () {

		var basepath = _site_domain + _index_page,
			link = $('#uh_problems'),
			query = link.attr('href');

		query = query.split('q=')[1];

		$.ajax({
			url : basepath + "/listview/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : query,
				"limit" : 100,
				"columns": ['description']
			},
			success : function(data) {

				if (data && data.totals.service_all[1] > 0) {

					uh_prob_title = data.totals.service_all[1] + ' unacknowledged services in Critical/Warning state!';
					link.attr('title', uh_prob_title);

					if (uh_prob_state_element) {
						uh_prob_state_element.remove();
					}
					uh_prob_state_element = $("<span style='margin: 0; position: absolute; color: #000; text-shadow: 0 0 2px #fff; font-weight: bold; font-size: 10px; padding: 1px 1px 0 0; right: 0px; bottom: 0px;' />");
					if(data.totals.service_all[1] < 100 ) {
						uh_prob_state_element.text(data.totals.service_all[1]);
					}
					link.append(uh_prob_state_element);

					if (data.totals.service_state_critical[1] > 0) {
						link.find(':first-child').removeClass().addClass('icon-16 x16-shield-critical');
					} else if (data.totals.service_state_warning[1] > 0) {
						link.find(':first-child').removeClass().addClass('icon-16 x16-shield-warning');
					}
				}
			}
		});
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
				'setting': JSON.stringify(global_quicklinks)
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

	$('#dojo-add-quicklink').hover(function () {
		this.style.opacity = '1.0';
	}, function () {
		this.style.opacity = '0.5';
	});

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
				var quicklink = $('<li><label><input type="checkbox" /><span class="icon-16 x16-'+l.icon+'"></span>' + l.title + '</label> (<a target="_blank" class="external"></a>)</li>');
				quicklink
					.find('input')
						.attr('value', vid)
						.attr('id', vid)
						.attr('title', l.title);
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
				$.jGrowl(error);
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
				'page': 'tac'
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

