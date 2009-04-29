$(document).ready(function() {
	var interval_sec = 60; // interval in seconds
	var interval = (interval_sec * 1000);
	setInterval("status_update()", interval);
});

/**
*	Update status_totals widget by using ajax call.
*	NOTE: If the classnames in the widget view is changed,
*	this script will nedd to be updated to reflect those changes.
*/
function status_update()
{
	/**
	*	First we need to figure out what data to fetch.
	*	We do this by checking the href of the first link and remove
	*	the _site_domain, _index_page and /status/ and then split the rest
	*	on '/' and then finally joining it with '|' for the receiving script
	*	to handle.
	*/
	var pathname = $("#widget-host_totals a:first").attr('pathname');

	var replace_str = _site_domain + _index_page + '/status/';
	var r = new RegExp(replace_str, 'g');
	var args = pathname.replace(r, '');
	var arguments_arr = args.split('/');
	var arguments = arguments_arr.join('|');

	$.ajax({
		/* use the _site_domain and _index_page from master
		* controller to allow javascript to know where
		* to find things
		*
		* This is also an example of how to use the widget_callback controller.
		* By calling widget_callback/ajax and add the name of the widget and the desired
		* method (status_totals/ajax_test in this case) with whatever arguments needed,
		* we can add our ajax methods to our widget class just as any other methods
		*/
		url: _site_domain + _index_page + "/widget_callback/ajax/status_totals/status/" + arguments,
		dataType:'json',
		success: function(data) {

			// == HOSTS ==
			var state = '';
			var value = '';
			var host_base_str = 'hostTotals';
			var service_base_str = 'serviceTotals';
			for (key in data.host) {
				state = data.host[key]['state'];
				value = data.host[key]['cnt'];
				$("#" + host_base_str + state).html(value);
				if (value!=0) {
					$("#" + host_base_str + state).removeClass().addClass(host_base_str + state);
				} else {
					$("#" + host_base_str + state).removeClass().addClass(host_base_str);
				}
			}
			var total_host_problems = 0;
			var total_hosts = 0;
			total_host_problems = data.total_host_problems;
			total_hosts  = data.total_hosts;

			$("#" + host_base_str + 'PROBLEMS').html(total_host_problems);
			if (total_host_problems!=0) {
				$("#" + host_base_str + 'PROBLEMS').removeClass().addClass(host_base_str + 'PROBLEMS');
			} else {
				$("#" + host_base_str + 'PROBLEMS').removeClass().addClass(host_base_str);
			}

			// == SERVICES ==
			state = '';
			value = '';
			for (key in data.service) {
				state = data.service[key]['state'];
				value = data.service[key]['cnt'];
				$("#" + service_base_str + state).html(value);
				if (value!=0) {
					$("#" + service_base_str + state).removeClass().addClass(service_base_str + state);
				} else {
					$("#" + service_base_str + state).removeClass().addClass(service_base_str);
				}
			}

			var total_service_problems = 0;
			total_service_problems = data.total_service_problems;
			var total_services = 0;
			total_services = data.total_services;

			$("#" + service_base_str + 'PROBLEMS').html(total_service_problems);
			if (total_service_problems!=0) {
				$("#" + service_base_str + 'PROBLEMS').removeClass().addClass(service_base_str + 'PROBLEMS');
			} else {
				$("#" + service_base_str + 'PROBLEMS').removeClass().addClass(service_base_str);
			}
		},
		error: function(obj, msg){alert(msg)}
	});
}
