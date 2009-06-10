var tac_problems_interval = 0;
var tac_problems_data = false;
var tac_problems_save_interval = 0;
var tac_problems_current_interval = $('input[name=tac_problems_refresh]').val();
$(document).ready(function() {
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

});

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
Sätt en timeout på 5 sekunder för att spara t databasen
kanske oxå för att verkställa själva checken

*/

function tac_problems_control_save()
{
	if (tac_problems_save_interval) {
		clearTimeout(tac_problems_save_interval);
	}
	tac_problems_save_interval = setTimeout("set_tac_problems_interval()", 5000);
}

function tac_problems_update()
{
	var widget_id = 'widget-tac_problems';
	$.ajax({
		url: _site_domain + _index_page + "/ajax/widget/tac_problems/index/",
		dataType:'json',
		success: function(data) {
			$("#" + widget_id + ' #widget-content').html(data);
		},
		error: function(obj, msg){alert(msg)}
	});
}

function save_tac_problems_setting(data)
{
	var url = _site_domain + _index_page + "/ajax/save_widget_setting/";
	$.post(url, data);
}