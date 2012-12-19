function _(text) {
//	console.log('To translate: '+ text);
	return text;
}

function icon12(name, title, link) {
	var img = $('<span />');
	img.addClass('icon-12');
	img.addClass('x12-' + name);
	if (title)
		img.attr('title', title);
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function icon16(name, title, link) {
	var img = $('<span />');
	img.addClass('icon-16');
	img.addClass('x16-' + name);
	if (title)
		img.attr('title', title);
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function icon(url, link) {
	var img = $('<img />');
	img.attr('src', '/monitor/images/logos/' + url); // FIXME
	img.css('height', '16px');
	img.css('width', '16px');
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function link(rel_url, args) {
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
function link_fnc(fnc) {
	return $('<a />').click(fnc);
}
function link_query(query) {
	var link = $('<a />');
	link.attr('href',_site_domain + _index_page + '/listview?q='+encodeURIComponent(query));
	link.click(function(evt) {
		lsfilter_main.update(query,false);
		return false;
	});
	return link;
}
function extinfo_link(host, service) {
	var args = {};
	args['host'] = host;
	if (service)
		args['service'] = service;
	return link('extinfo/details', args);
}