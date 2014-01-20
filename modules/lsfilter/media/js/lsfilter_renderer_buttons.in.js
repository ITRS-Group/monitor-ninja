listview_renderer_buttons.all = [
	$('<a href="#" data-drop-down="filter-query-multi-action"/>').attr('title', _('Send multi action')).append($('<span class="icon-16 x16-check-boxes"/>')),
	$('<a href="#" data-drop-down="filter-query-builder"/>').attr('title', _('Show/Edit Text Filter')).append($('<span class="icon-16 x16-filter"/>'))
];
listview_renderer_buttons.recurring_downtimes = [
	icon16('sign-add', 'Add new downtime', link('recurring_downtime')).append('New')
];
