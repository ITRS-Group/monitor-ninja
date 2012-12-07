var query_timer = null;
var query_string = "";
var current_request = null;

var render = {
		"hosts": function(obj) {
			console.log(obj);
			var row = $('<tr />');
			return row.append($('<td />').text(obj.name));
		},
		"services": function(obj) {
			console.log(obj);
			var row = $('<tr />');
			return row.append($('<td />').text(obj.host.name+";"+obj.description));
		}
};

var doAjaxSearch = function() {
	if( current_request != null ) {
		current_request.abort();
	}
	console.log( "Query: "+query_string );
	current_request = $.ajax({
		url: _site_domain + _index_page + "/" + _controller_name + "/fetch_ajax",
		dataType: 'json',
		data: {
			"q": query_string
		},
		success: function(data) {
			if( data.status == 'success' ) {
				console.log( "Got "+data.data.length+" objects" );
				var tbl = $('<table />');
				for( var i=0; i<data.data.length; i++ ) {
					var obj = data.data[i];
					tbl.append( render[obj._table](obj) );
				}
				$('#filter_result').empty().append(tbl);
			}
			if( data.status == 'error' ) {
				$('#filter_result').empty().text( "Error: "+data.data );
			}
		}
	});
}

function sendAjaxSearch(query) {
	if (query_timer != null) {
		clearTimeout( query_timer );
	}
	query_string = query;
	query_timer = setTimeout( doAjaxSearch, 500 );
}