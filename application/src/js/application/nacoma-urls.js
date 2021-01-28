$(function () {
	var nacoma_base = '/' + $.map([_site_domain, _index_page, _current_uri], function(p) {
		return p.replace(/^\/|\/$/g, '');
	}).join('/');

	var nacoma_install_location = '/monitor/op5/nacoma/';

	// called by nacoma whenever a nacoma page is loaded
	onnacomaload = function (nacomawin) {
		if(!history.replaceState || "function" === history.replaceState) {
			return;
		}
		var nacomaurl = nacomawin.document.URL;
		var interesting_url = nacomaurl.substr(nacomaurl.indexOf(nacoma_install_location) + nacoma_install_location.length).replace(/^\/+/, '');
		var new_url = nacoma_base + '?page=' + encodeURIComponent(interesting_url);
		history.replaceState(undefined, document.title, new_url);
	};
});
