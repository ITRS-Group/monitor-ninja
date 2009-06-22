$(document).ready(function() {
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : false
		},
		i18n : {
			editText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Settings" />',
			closeText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-close.png" alt="Close widget" />',
			collapseText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-maximize.png" alt="Collapse" />',
			cancelEditText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-config.png" alt="Cancel" />',
			extendText : '<img src="/ninja/application/views/themes/default/icons/12x12/box-mimimize.png" alt="Extend" />'
		},
		effects : {
			effectDuration : 150,
			widgetShow : 'slide',
			widgetHide : 'slide',
			widgetClose : 'slide',
			widgetExtend : 'slide',
			widgetCollapse : 'slide',
			widgetOpenEdit : 'slide',
			widgetCloseEdit : 'slide',
			widgetCancelEdit : 'slide'
		},
		callbacks : {
			onChangePositions : function(str){
				save_widget_order(str);
			},
			onRefreshPositions: function() {
				fetch_widget_order();
			},
			onClose: function(link, widget) {
				var widget_name = '#' + widget[0].id;
				$('#li_' + widget[0].id).removeClass().addClass('unselected');
				save_widget_state('hide', widget_name);
			},
			onHide: function(widget) {
				var widget_name = widget.selector;
				save_widget_state('hide', widget_name);
			},
			onShow: function(widget) {
				var widget_name = widget.selector;
				save_widget_state('show', widget_name);
			}
		}
	});
	$("#widget-placeholder").bind('click', function() {
		$("#page_settings").hide();
	});
});



var _global_save = 0; 		// timeout handler variable
var global_refresh = 60;	// keeps track of the refresh rate set by slider
/**
*	Update refresh rate for all widgets on the page
*/
function widget_page_refresh()
{
	if ($('#widget_global_slider').is('div')) {
		$('#widget_global_slider').remove();
	} else {
		var content = '<div id="widget_global_slider" style="height:10px;background-color: #ffffff;">';
		content += '<br /><input style="border:0px; display: inline; padding: 0px; margin-bottom: 7px" size="3" type="text" name="global_widget_refresh" id="global_widget_refresh" value="' + global_refresh + '" />';
		content += '</div>';
		$("#page_settings").append(content);
		$("#widget_global_slider").slider({
			value: global_refresh,
			min: 0,
			max: 500,
			step: 10,
			slide: function(event, ui) {
				$("#global_widget_refresh").val(ui.value);
				global_refresh = ui.value;
				update_save_interval();
			}
		});
	}
}

/**
*	Keep track of the timeout for when to save
*	the global refresh rate for widgets.
*	Is reset to 5 sec between all changes
*/
function update_save_interval()
{
	if (_global_save) {
		clearTimeout(_global_save);
	}
	_global_save = setTimeout("set_widget_refresh()", 5000);
}

/**
*	Save new refresh rate for all widgets to database
*	and display notification if successful or not
*/
function set_widget_refresh()
{
	var url = _site_domain + _index_page + "/ajax/set_widget_refresh/";
	var page_name = _current_uri;
	var value = global_refresh;
	var data = {page: escape(page_name), value: value, type: 'refresh_interval'};
	$.ajax({
		url: url,
		dataType:'json',
		data: data,
		type: 'POST',
		success: function(data) {
			if (data.success == true) {
				$.jGrowl('Refresh rate for all widgets has been updated to ' + value + ' sec', { header: 'Success' });
				$('#widget_global_slider').remove();
				$('.widget-editbox [name$=_refresh]').each(function() {
					$(this).attr('value', value);
				});
				window.location.reload();
			} else {
				$.jGrowl('Unable to update refresh rate for all widgets.', { header: 'ERROR' });
			}
		},
		error: function(obj, msg){$.jGrowl('An error was encountered when trying to update refresh rate for all widgets.', { header: 'ERROR' });}
	});
}

/*
*	Save widget order to database
*/
function save_widget_order(order_str)
{
	var url = _site_domain + _index_page + "/ajax/save_widgets_order/";
	var page_name = _current_uri;
	var data = {page: escape(page_name), widget_str: order_str};
	$.post(url, data);
}

/*
*	Fetch saved widget order from database
*/
function fetch_widget_order()
{
	var page_name = _current_uri;
	$.ajax({
		url: _site_domain + _index_page + "/ajax/fetch_widgets_order/?page=" + escape(page_name),
		dataType:'json',
		success: function(data) {
			if (data.widget_order) {
				$.fn.EasyWidgets({callbacks:{onRefreshPositions:function(){return data.widget_order;}}});
			}
		},
		error: function(obj, msg){$.jGrowl('Unable to fetch widget order from database.', { header: 'ERROR' });}
	});

}


function control_widgets(id,item) {
	if (item.className == 'selected') {
		$.fn.HideEasyWidget(id);
		item.className = 'unselected';
	}
	else {
		$.fn.ShowEasyWidget(id);
		item.className = 'selected';
	}
}

function save_widget_state(what, widget_name)
{
	var url = _site_domain + _index_page + "/ajax/save_widget_state/";
	var page_name = _current_uri;
	var data = {page: escape(page_name), method: what, name: widget_name};
	$.post(url, data);
}

function restore_widgets()
{
	$('li[id^=li_widget]').each(function() {
		var item_id = $(this).attr('id');
		var widget_id = item_id.replace('li_', '');
		$('#' + item_id).removeClass().addClass('selected');
		$('#' + widget_id).show();
	});
}

/**
*	Remove whitespace from string
*/
function trim(str) {
	return str.replace(/^\s+|\s+$/g,"");
}

/**
*	Ninja widget class
*/
function widget(name, content_area, no_edit)
{
	var self = this;
	var ajax_url = _site_domain + _index_page + '/ajax/';

	this._refresh_interval = 0;
	this.save_interval = 0;
	this.current_uri = _current_uri;
	this.content_area = false;
	this.no_edit = no_edit;

	/*
	*	Initialize some internal values.
	*/
	this.init_widget = function(name) {
		self.set_name(name);
		if (this.name !== false)
			this.set_widget_id(name);
		if (!$('#' + self.widget_id).text())
			return false;
		this.current_interval = $('input[name=' + name + '_refresh]').val();
		this.title =  $('#' + self.name + '_title').text();
		return true;
	}

	/*
	*	Set the name of the widget.
	*/
	this.set_name = function(name) {
		this.name = (name == null) ? false : name;
	}

	/**
	*	Set the ID of the area that should be updated through the AJAX call.
	*/
	this.set_content_area = function(area) {
		this.content_area = (area == null) ? false : area;
	}

	this.set_widget_id = function(name) {
		this.widget_id = (name == null) ? false : 'widget-' + name;
	}

	/*
	*	Fetch current widget state through AJAX call
	*/
	this.update_display = function() {
		if (this.content_area != false) {
			$.ajax({
				url: ajax_url + "widget/" + self.name + "/index/",
				dataType:'json',
				success: function(data) {
					$("#" + self.widget_id + ' #' + self.content_area).html(data);
				},
				error: function(obj, msg){$.jGrowl('Unable to update view for widget ' + self.name, { header: 'ERROR' });}
			});
		}
	}

	/*
	*	Save widget settings to db
	*/
	this.save_settings = function(data) {
		var url = ajax_url + "save_widget_setting/";
		$.post(url, data);
		$.jGrowl("Settings for widget " + self.name + " was updated", { header: 'Success' });
	}

	/*
	*	Set the refresh interval to use for widget
	*	and also pass this value on to be saved to db
	*/
	this.set_refresh_interval = function(is_init){
		if (this._refresh_interval) {
			clearInterval(this._refresh_interval);
		}

		if (this.current_interval>0) {
			var interval = (this.current_interval * 1000);
			//this._refresh_interval = setInterval("update_display()", interval);
			this._refresh_interval = setInterval(function() {self.update_display();}, interval);
		}

		if (!is_init) {
			// update widget settings
			var data = {page: this.current_uri, refresh_interval: this.current_interval, widget: this.name};
			this.save_settings(data);
		}
	}

	/*
	*	Since the slider is possible to move rather fast,
	*	we add a delay (timeout) to this before we do anything with it.
	*	The timeout is cleared when a new value is selected and only saved
	*	until there is no activity (new value) for 5 seconds
	*/
	this.control_save_interval = function() {
		if (this.save_interval) {
			clearTimeout(this.save_interval);
		}
		this.save_interval = setTimeout(function() {self.set_refresh_interval();}, 5000);
	}

	this.init_slider = function() {
		$("#" + this.name + "_slider").slider({
			value: self.current_interval,
			min: 0,
			max: 500,
			step: 10,
			slide: function(event, ui) {
				$("#" + self.name + "_refresh").val(ui.value);
				self.current_interval = ui.value;
				self.control_save_interval();
			}
		});
		$("#" + this.name + "_refresh").val($("#" + this.name + "_slider").slider("value"));
	}

	this.init_title_edit = function() {
		$("." + this.name + "_editable").editable(function(value, settings) {
			var data = {page: self.current_uri, widget:self.name, widget_title:value};
			value = trim(value);
			// don't save an ampty title
			if (value.length) {
				self.save_settings(data);
				self.title = value;
			} else {
				value = self.title;
			}
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
	}

	// initialize widget
	var widget_ok = this.init_widget(name);
	if (widget_ok == false) {
		// here we should probably notify user that
		// the widget isn't found
		$.jGrowl("Unable to find widget " + name, { header: 'ERROR' });
		if( window.console && window.console.firebug ) {
			console.log("widget " + name + " isn't found");
		}
		return false;
	}
	this.set_content_area(content_area);

	// only enable refresh and interval editing
	// if we have a content_area
	if (this.content_area) {
		this.set_refresh_interval(true);
		this.init_slider();
	}
	if (this.no_edit != null && this.no_edit != true)
		this.init_title_edit();
}