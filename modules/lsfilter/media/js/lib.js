function helpicon(controller, key)
{
	var link = $('<a class="helptext_target" style="border: 0" href="#"/>');
	link.append(icon12('help'));
	link.attr('data-popover', 'help:' + controller + '.' + key);
	return link;
}

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
	link.attr('data-query', query);
	link.addClass('query_link');
	return link;
}
function extinfo_link(args)
{
	return link('extinfo/details', args);
}
function format_timestamp(timestamp)
{
	// remember: server's offset is local - UTC, client's offset is UTC - local,
	// so the sign should be the same.
	var timestamp_int = parseInt(timestamp);

	var dateobj = new Date((timestamp_int + _server_utc_offset) * 1000);
	dateobj = new Date(dateobj.getTime() + (dateobj.getTimezoneOffset() * 60000));
	var ret = dateobj.format(_date_format);
	dateobj = null;
	return ret;
}
function format_interval(interval)
{
	if (interval < 0) return _('N/A');
	var str = "";
	if (interval % 60 !== 0) str = (interval % 60) + "s " + str;
	interval = Math.floor(interval / 60);
	if (interval % 60 !== 0) str = (interval % 60) + "m " + str;
	interval = Math.floor(interval / 60);
	if (interval % 24 !== 0) str = (interval % 24) + "h " + str;
	interval = Math.floor(interval / 24);
	if (interval !== 0) str = (interval) + "d " + str;
	return str;
}

function comment_icon( host, service ) {
	var obj_name = service ? host+';'+service : host;
	var query = '[comments] host.name="' + host + '"' + (service?' and service.description="'+service+'"':'');
	var link_data = {};
	link_data.host = host;
	if(service)
		link_data.service = service;

	var ext_link = extinfo_link(link_data)
		.append(icon16('add-comment', _('Comments')))
		.css('border', '0px');

	ext_link.attr({
		'data-popover': 'get:' + _site_domain + _index_page + "/listview_tools/fetch_comments?host=" + obj_name
	});
	return ext_link;

}

jQuery.fn.update_text = function(text) {
	if( _escape_html_tags ) {
		return this.text(text);
	} else {
		return this.html(text);
	}
};

jQuery.fn.querylink = function(text) {
	if( _escape_html_tags ) {
		return this.text(text);
	} else {
		return this.html(text);
	}
};

jQuery.fn.replaceContent = function(new_data) {
	this.empty();
	this.append(new_data);
	return this;
};






function render_summary_state(ul, state, stats, substates)
{
	if (stats.stats[state] === 0) return;

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
