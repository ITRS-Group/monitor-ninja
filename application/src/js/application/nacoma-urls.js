$(document).ready(function () {
	var nacoma_base = $.map(function(p) {
		return p.replace(/^\/|\/$/, '');
	}, [_site_domain, _index_page, _current_uri]).join('/');

	var nacoma_install_location = '/monitor/op5/nacoma/';

	// called by nacoma whenever a nacoma page is loaded
	onnacomaload = function (nacomawin) {
		if(!history.replaceState || "function" === history.replaceState) {
			return;
		}
		var nacomaurl = nacomawin.document.URL;
		var interesting_url = nacomaurl.substr(nacomaurl.indexOf(nacoma_install_location) + nacoma_install_location.length).replace(/^\/+/, '');
		history.replaceState(undefined, document.title, nacoma_base + '?page=' + encodeURIComponent(interesting_url));
	};
});
