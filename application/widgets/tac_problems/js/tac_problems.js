function init_tac_problems(instance) {
	$('#col_outages' + instance).live('colorpicked', function () {
		$('#id_outages' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_outages', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_outages' + instance).addClass('status-outages').css('background', '');
		}
	});

	$('#col_hosts_down' + instance).live('colorpicked', function () {
		$('#id_host_down' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_host_down', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_host_down' + instance).addClass('status-down').css('background', '');
		}
	});

	$('#col_services_critical' + instance).live('colorpicked', function () {
		$('#id_service_critical' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_critical', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_service_critical' + instance).addClass('status-critical').css('background', '');
		}
	});
	$('#col_hosts_unreachable' + instance).live('colorpicked', function () {
		$('#id_host_unreachable' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_host_unreachable', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_host_unreachable' + instance).addClass('status-unreachable').css('background', '');
		}
	});
	$('#col_services_warning' + instance).live('colorpicked', function () {
		$('#id_service_warning' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_warning', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_service_warning' + instance).addClass('status-warning').css('background', '');
		}
	});
	$('#col_services_unknown' + instance).live('colorpicked', function () {
		$('#id_service_unknown' + instance).css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_unknown', instance);
		if ($(this).val() == '#ffffff') {
			$('#id_service_unknown' + instance).addClass('status-unknown').css('background', '');
		}
	});
}

/**
*	Save new color selection for this widget instance
*/
function save_tac_disabled_colors(newval, fieldname, instance)
{
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "save_dynamic_widget_setting/";
	var data = {page: _current_uri, fieldvalue: newval, fieldname:fieldname+instance, widget: instance};
	$.post(url, data);
	$.jGrowl(sprintf(_widget_settings_msg, self.name), { header: _success_header });
}