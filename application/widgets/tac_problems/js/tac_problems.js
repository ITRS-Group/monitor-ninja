var tac_problems_interval = 0;
var tac_problems_data = false;
var tac_problems_save_interval = 0;
var tac_problems_current_interval = 0;
$(document).ready(function() {
	tac_problems_current_interval = $('input[name=tac_problems_refresh]').val();
	set_tac_problems_interval(true);

	$("#tac_problems_slider").slider({
		value: $('input[name=tac_problems_refresh]').val(),
		min: 0,
		max: 500,
		step: 10,
		slide: function(event, ui) {
			$("#tac_problems_refresh").val(ui.value);
			tac_problems_current_interval = ui.value;
			//set_tac_problems_interval(ui.value);
			tac_problems_control_save();
		}
	});
	$("#tac_problems_refresh").val($("#tac_problems_slider").slider("value"));
	$(".tac_problems_editable").editable(function(value, settings) {
		var data = {page: $('input[name=tac_problems_page]').val(), widget:'tac_problems', tac_problems_title:value};
		save_tac_problems_setting(data);
		return value;
	}, {
		type : 'text',
		event : 'dblclick',
		width : 'auto',
		height : '14px',
		submit : 'OK',
		cancel : 'cancel',
		placeholder:'Double-click to edit'
	});
});

/*
*	Set the refresh interval to use for widget
*	and also pass this value on to be saved to db
*/
function set_tac_problems_interval(is_init)
{
	if (tac_problems_interval) {
		clearInterval(tac_problems_interval);
	}

	if (tac_problems_current_interval>0) {
		var interval = (tac_problems_current_interval * 1000);
		tac_problems_interval = setInterval("tac_problems_update()", interval);
	}

	if (!is_init) {
		// update widget settings
		tac_problems_data = {page: $('input[name=tac_problems_page]').val(), tac_problems_refresh: tac_problems_current_interval, widget: 'tac_problems'};
		save_tac_problems_setting(tac_problems_data);
	}
}

/*
*	Since the slider is possible to move rather fast,
*	we add a delay (timeout) to this before we do anything with it.
*	The timeout is cleared when a new value is selected and only saved
*	until there is no activity (new value) for 5 seconds
*/
function tac_problems_control_save()
{
	if (tac_problems_save_interval) {
		clearTimeout(tac_problems_save_interval);
	}
	tac_problems_save_interval = setTimeout("set_tac_problems_interval()", 5000);
}

/*
*	Update the widget through AJAX
*/
function tac_problems_update()
{
	var content_area = 'widget-content';
	var widget = 'tac_problems';
	update_widget(widget, content_area);
}

/*
*	Generic method to update a widget through AJAX
*/
function update_widget(widget, content_area)
{
	var widget_id = 'widget-' + widget;
	$.ajax({
		url: _site_domain + _index_page + "/ajax/widget/" + widget + "/index/",
		dataType:'json',
		success: function(data) {
			$("#" + widget_id + ' #' + content_area).html(data);
		},
		error: function(obj, msg){alert(msg)}
	});
}

/*
*	Save widget settings
*/
function save_tac_problems_setting(data)
{
	var url = _site_domain + _index_page + "/ajax/save_widget_setting/";
	$.post(url, data);
}