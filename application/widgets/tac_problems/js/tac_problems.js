$(document).ready(function() {
	var tac_problems = new widget('tac_problems', 'widget-content');

	$('#col_outages').bind('colorpicked', function () {
		$('#id_outages').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_outages')
	});
	$('#col_hosts_down').bind('colorpicked', function () {
		$('#id_host_down').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_host_down')
	});
	$('#col_services_critical').bind('colorpicked', function () {
		$('#id_service_critical').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_critical')
	});
	$('#col_hosts_unreachable').bind('colorpicked', function () {
		$('#id_host_unreachable').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_host_unreachable')
	});
	$('#col_services_warning').bind('colorpicked', function () {
		$('#id_service_warning').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_warning')
	});
	$('#col_services_unknown').bind('colorpicked', function () {
		$('#id_service_unknown').css('background', $(this).val());
		save_tac_disabled_colors($(this).val(), 'col_service_unknown')
	});
});

function save_tac_disabled_colors(newval, fieldname)
{
	var ajax_url = _site_domain + _index_page + '/ajax/';
	var url = ajax_url + "save_dynamic_widget_setting/";
	var data = {page: _current_uri, fieldvalue: newval, fieldname:fieldname, widget: 'tac_problems'};
	$.post(url, data);
	$.jGrowl(sprintf(_widget_settings_msg, self.name), { header: _success_header });
}