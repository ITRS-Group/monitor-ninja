
/*******************************************************************************
 * Totals renderer
 ******************************************************************************/

listview_renderer_totals['count'] = function(cnt) {
	var container = $('<li class="extra_toolbar_category" />');
	container.append(_("Count") + ": &nbsp; ");
	container.append(icon16('shield-info', _("Matching")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["host_all"] = function(cnt) {
	var container = $('<li class="extra_toolbar_category" />');
	container.append(_("Hosts") + ": &nbsp; ");
	container.append(icon16('host', _("Hosts total")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["host_state_up"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-up',
			_("Hosts up")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["host_state_down"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-down', _("Hosts down")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["host_state_unreachable"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-unreachable', _("Hosts unreachable")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["host_pending"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-pending', _("Hosts pending")));
	container.append(cnt);
	return container;
};

listview_renderer_totals["service_all"] = function(cnt) {
	var container = $('<li class="extra_toolbar_category" />');
	container.append(_("Services") + ": &nbsp; ");
	container.append(icon16('shield-info', _("Services total")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["service_state_ok"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-ok',
			_("Services ok")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["service_state_warning"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-warning', _("Services warning")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["service_state_critical"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-critical', _("Services critical")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["service_state_unknown"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-unknown', _("Services unknown")));
	container.append(cnt);
	return container;
};
listview_renderer_totals["service_pending"] = function(cnt) {
	var container = $('<li />');
	container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
			+ '-pending', _("Services pending")));
	container.append(cnt);
	return container;
};
