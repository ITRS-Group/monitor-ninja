widget.register_widget_load('status_totals', function() {
	this.set_current_uri('status');
	var widget = $('#' + this.widget_id);
	widget.attr('class', widget.attr('class') + ' right').css('margin-right', '1%').css('width', '500px');
	$('.widget-header', widget).attr('class', 'widget-header dark');
});
