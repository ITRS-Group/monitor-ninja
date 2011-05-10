var nacoma_base = _site_domain + '/' + _index_page + '/' + _current_uri;

// called by nacoma whenever a nacoma page is loaded
onnacomaload = function (nacomawin) {
	var nacomaurl = nacomawin.document.URL.split('/').pop();
	history.replaceState(undefined, document.title, nacoma_base + '?page=' + encodeURIComponent(nacomaurl));
}
