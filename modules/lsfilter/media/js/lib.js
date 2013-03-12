function icon12(name, title, link)
{
	var img = $('<span />');
	img.addClass('icon-12');
	img.addClass('x12-' + name);
	if (title) img.attr('title', title);
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function icon16(name, title, link, base)
{
	if( !base ) base = 'x16';
	var img = $('<span />');
	img.addClass('icon-16');
	img.addClass(base + '-' + name);
	if (title) img.attr('title', title);
	if (link) {
		img = link.append(img);
		img.css('border', '0');
	}
	return img;
}
function icon(url, link)
{
	var img = $('<img />');
	img.attr('src', _logo_path + url); // FIXME
	img.css('height', '16px');
	img.css('width', '16px');
	if (link) {
		img = link.append(img);
		img.css('border', '0');
	}
	return img;
}
function link(rel_url, args)
{
	var get_data = "";
	var delim = "?";
	for ( var key in args) {
		get_data += delim + key + "=" + encodeURIComponent(args[key]);
		delim = "&";
	}
	
	var el = $('<a />');
	el.attr('href', _site_domain + _index_page + "/" + rel_url + get_data);
	return el;
}
function link_fnc(fnc)
{
	return $('<a />').click(fnc);
}
function link_query(query)
{
	var link = $('<a />');
	link.attr('href', _site_domain + _index_page + '/listview?q='
			+ encodeURIComponent(query));
	if( window.lsfilter_main ) {
		link.click(function(evt)
		{
			lsfilter_main.update(query, false, '');
			evt.preventDefault();
			return false;
		});
	}
	return link;
}
function extinfo_link(args)
{
	return link('extinfo/details', args);
}
function format_timestamp(timestamp)
{
	var dateobj = new Date(timestamp * 1000);
	var ret = dateobj.format(_date_format);
	dateobj = null;
	return ret;
}
function format_interval(interval)
{
	if (interval < 0) return _('N/A');
	var str = "";
	if (interval % 60 != 0) str = (interval % 60) + "s " + str;
	interval = Math.floor(interval / 60);
	if (interval % 60 != 0) str = (interval % 60) + "m " + str;
	interval = Math.floor(interval / 60);
	if (interval % 24 != 0) str = (interval % 24) + "h " + str;
	interval = Math.floor(interval / 24);
	if (interval != 0) str = (interval) + "d " + str;
	return str;
}

function pnp_popup(elem, args)
{
	if (!_use_popups) return;

	var get_data = [];
	for ( var key in args) {
		get_data.push(key + "=" + encodeURIComponent(args[key].replace(/[ :\/\\]/g, "_")));
	}
	
	var loading_img = '/application/media/images/loading.gif';
	
	$(elem).qtip(
			{
				content: {
					url: _site_domain + _index_page + "/ajax/pnp_image/",
					data: {
						param: get_data.join("&")
					},
					method: 'post',
					text: '<img src="' + _site_domain + loading_img + '" alt="'
							+ _loading_str + '" />'
				},
				position: {
					corner: {
						target: 'bottomMiddle', // Position the tooltip above
												// the link
						tooltip: 'topLeft'
					},
					adjust: {
						screen: true, // Keep the tooltip on-screen at all
										// times
						x: 10,
						y: -5
					}
				},
				show: {
					when: 'mouseover',
					solo: true,
					delay: _popup_delay
				},
				hide: {
					effect: 'slide',
					when: {
						event: 'mouseout',
						delay: 2000
					}
				},
				style: {
					width: 620,
					tip: true, // Apply a speech bubble tip to the tooltip at
								// the designated tooltip corner
					border: {
						width: 0,
						radius: 4
					},
					name: 'light' // Use the default light style
				}
			});
}








function render_summary_state(ul, state, stats, substates)
{
	if (stats.stats[state] == 0) return;
	
	var li = $('<li />').append(
			link_query(stats.queries[state]).append(icon16('shield-' + state))
					.append(
							$('<span />')
									.text(stats.stats[state] + " " + state)));
	
	var delim = ' ( ';
	var suffix = '';
	
	for ( var tag in substates) {
		var key = state + tag;
		var type = substates[tag];
		
		if (stats.stats[key]) {
			li.append(delim);
			li.append(link_query(stats.queries[key]).text(
					stats.stats[key] + ' ' + type));
			delim = ', ';
			suffix = ' ) ';
		}
	}
	
	li.append(suffix);
	
	ul.append(li);
}

function render_service_status_summary(stats)
{
	var ul = $('<ul class="listview-summary" />');
	
	render_summary_state(ul, 'ok', stats, {});
	render_summary_state(ul, 'warning', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
	});
	render_summary_state(ul, 'critical', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
	});
	render_summary_state(ul, 'unknown', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
	});
	render_summary_state(ul, 'pending', stats, {});
	
	return ul;
}

function render_host_status_summary(stats)
{
	var ul = $('<ul class="listview-summary" />');
	
	render_summary_state(ul, 'up', stats, {});
	render_summary_state(ul, 'down', stats, {});
	render_summary_state(ul, 'unreachable', stats, {});
	render_summary_state(ul, 'pending', stats, {});
	
	return ul;
}

var listview_multi_select_cell_renderer = function(args)
{
	var checkbox = $(
			'<input type="checkbox" name="object_select[]" class="listview_multiselect_checkbox" />')
			.attr('value', args.obj.key);
	if (false /* listview_selection[args.obj.key] */) {
		checkbox.prop('checked', true);
		if (tr.hasClass('odd'))
			tr.addClass('selected_odd');
		else
			tr.addClass('selected_even');
	}
	checkbox.change(function(evt)
	{
		var tgt = $(evt.target);
		// listview_selection[tgt.attr('value')] = tgt.prop('checked');
		var tr = tgt.closest('tr');
		var classname = ""
		if (tr.hasClass('odd'))
			classname = 'selected_odd';
		else
			classname = 'selected_even';
		if (tgt.prop('checked')) {
			tr.addClass(classname);
		}
		else {
			tr.removeClass(classname);
		}
	});
	return $('<td style="width: 1em;" />').append(checkbox);
};