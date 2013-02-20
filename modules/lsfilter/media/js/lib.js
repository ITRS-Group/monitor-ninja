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
function icon16(name, title, link)
{
	var img = $('<span />');
	img.addClass('icon-16');
	img.addClass('x16-' + name);
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
	link.click(function(evt)
	{
		lsfilter_main.update(query, false, '');
		evt.preventDefault();
		return false;
	});
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
	var get_data = "";
	var delim = "";
	for ( var key in args) {
		get_data += delim + key + "=" + encodeURIComponent(args[key]);
		delim = "&";
	}
	
	var loading_img = '/application/media/images/loading.gif';
	
	if (!_use_popups) return;
	
	$(elem).qtip(
			{
				content: {
					url: _site_domain + _index_page + "/ajax/pnp_image/",
					data: {
						param: get_data
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
