
/*******************************************************************************
 * Totals renderer
 ******************************************************************************/
var _test_icons = {
	'ok' : '⊙',
	'up' : '⊙',
	'warning' : '⊘',
	'down' : '⊗',
	'critical' : '⊗',
	'unreachable' : '⊚',
	'unknown': '⊚',
	'pending': '⊝'
};

listview_renderer_totals['count'] = function(cnt) {
	var container = $('<li class="extra_toolbar_category" />');
	container.append(
		$('<span class="main-toolbar-subtitle" />')
			.append(_("Count") + ": &nbsp; ")
	);
	container.append(
		$('<span>')
			.addClass('badge')
			.addClass('info supplementary')
			.append(cnt)
	);
	return container;
};

listview_renderer_totals["host_all"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append(
			$('<span>').addClass('badge info supplementary')
			.attr('title', 'Go to list of all hosts matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};

listview_renderer_totals["host_state_up"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge up supplementary')
			.attr('title', 'Go to list of hosts in state up matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["host_state_down"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge down supplementary')
			.attr('title', 'Go to list of hosts in state down matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["host_state_unreachable"] = function(cnt) {
	var container = $('<li />');
	if (cnt > 0) {
		container.append(
			$('<span>').addClass('badge unreachable supplementary')
			.attr('title', 'Go to list of hosts in state unreachable matching this filter')
			.append(cnt)
		);
	}
	return container;
};
listview_renderer_totals["host_pending"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge pending supplementary')
			.attr('title', 'Go to list of hosts in state pending matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};

listview_renderer_totals["service_all"] = function(cnt, table) {
	var container = $('<li class="extra_toolbar_category" />');
	if (table !== 'services') {
		container.append($('<span>').addClass('main-toolbar-subtitle').append(_("Services") + ": &nbsp; "));
	}
	if (cnt > 0) {
		container.append(
			$('<span>').addClass('badge info supplementary')
			.attr('title', 'Go to list of all services matching this filter')
			.append(cnt)
		);
	}
	return container;
};
listview_renderer_totals["service_state_ok"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge ok supplementary')
			.attr('title', 'Go to list of services in state ok matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["service_state_warning"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge warning supplementary')
			.attr('title', 'Go to list of services in state warning matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["service_state_critical"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge critical supplementary')
			.attr('title', 'Go to list of services in state critical matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["service_state_unknown"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge unknown supplementary')
			.attr('title', 'Go to list of services in state unknown matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
listview_renderer_totals["service_pending"] = function(cnt) {
	if (cnt > 0) {
		var container = $('<li />');
		container.append(
			$('<span>').addClass('badge pending supplementary')
			.attr('title', 'Go to list of services in state pending matching this filter')
			.append(cnt)
		);
		return container;
	}
	return $('');
};
