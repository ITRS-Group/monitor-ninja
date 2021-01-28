/**
 * Initializes autocomplete for the Global search field
 *
 * The request is handled by the controller "search" and method "ajax_auto_complete"
 */
$(function() {

	var query = $('#query');
	if(query.length) {

		query.autocomplete({
			serviceUrl: _site_domain + _index_page + '/search/ajax_auto_complete/',
			minChars: 2,
			maxHeight: 500,
			width: 'auto',
			deferRequestBy: 300,
			cacheLength: 0,
			onSelect: function (id, path) {
				window.location.href = path;
			}
		});

	}

});
