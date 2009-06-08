$(document).ready(function() {
	var interval_sec = 60; // interval in seconds
	var interval = (interval_sec * 1000);
	setInterval("tac_problems_update()", interval);
});


function tac_problems_update()
{
	var widget_id = 'widget-tac_problems';
	$.ajax({
		url: _site_domain + _index_page + "/ajax/widget/tac_problems/index/",
		dataType:'json',
		success: function(data) {
			$("#" + widget_id).html(data);
		},
		error: function(obj, msg){alert(msg)}
	});
}