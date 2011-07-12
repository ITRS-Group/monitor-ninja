$(document).ready(function() {
	init_easywidgets();
	$(".widget-place").bind('click', function() {
		$("#page_settings").hide();
	});
});

function widget_upload()
{
	self.location.href=_site_domain + _index_page + "/upload/";
}

function init_easywidgets(){
	$.fn.EasyWidgets({
		behaviour : {
			useCookies : false
		},
		i18n : {
			editText : '<img src="' + _site_domain + _theme_path + 'icons/12x12/box-config.png" alt="Settings" />',
			closeText : '<img src="' + _site_domain + _theme_path + 'icons/12x12/box-close.png" alt="Close widget" />',
			collapseText : '<img src="' + _site_domain + _theme_path + 'icons/12x12/box-maximize.png" alt="Collapse" />',
			cancelEditText : '<img src="' + _site_domain + _theme_path + 'icons/12x12/box-config.png" alt="Cancel" />',
			extendText : '<img src="' + _site_domain + _theme_path + 'icons/12x12/box-mimimize.png" alt="Extend" />'
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
				$(widget_name).removeClass('movable');
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
}

// Global variable to keep track of when user decides to reload the interface.
// This is needed since we want to prevent the spawning of additional ajax calls.
var _is_refreshing = false;

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
	_is_refreshing = true;

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
				$.jGrowl(sprintf(_widget_refresh_msg, value), { header: _success_header });
				$('#widget_global_slider').remove();
				$('.widget-editbox [name$=_refresh]').each(function() {
					$(this).attr('value', value);
				});
				window.location.reload();
			} else {
				$.jGrowl(_widget_refresh_error, { header: _error_header });
			}
		},
		error: function(obj, msg){$.jGrowl(_widget_global_refresh_error, { header: _error_header });}
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
function fetch_widget_order(restore)
{
	var page_name = _current_uri;
	restore = (restore == null) ? false : true;
	var restore_str = restore ? '&default=1' : '';
	$.ajax({
		url: _site_domain + _index_page + "/ajax/fetch_widgets_order/?page=" + escape(page_name) + restore_str,
		dataType:'json',
		success: function(data) {
			if (data.widget_order) {
				if (restore == true) {
					save_widget_order(data.widget_order);
				}

				$.fn.EasyWidgets({callbacks:{onRefreshPositions:function(){return data.widget_order;}}});
			}
		},
		error: function(obj, msg){
			// disable annoying error message
			//$.jGrowl(_widget_order_error, { header: _error_header });
		}
	});

}


function control_widgets(id,item) {
	if (item.className == 'selected') {
		$.fn.HideEasyWidget(id);
		$('#'+id).removeClass('movable');
		item.className = 'unselected';
	}
	else {

		$('#'+id).addClass('movable');
		$.fn.ShowEasyWidget(id);
		init_easywidgets();
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
		$.fn.ShowEasyWidget(widget_id);
		$('#'+widget_id).addClass('movable');
		init_easywidgets();
	});
	fetch_widget_order(true);
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
	this.is_updating = false;

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

	this.set_current_uri = function(uri) {
		this.current_uri = uri;
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
		if (this.content_area != false && $('#widget-' + self.name).is(':visible')) {
			if (self.is_updating || _is_refreshing) {
				/**
				* Prevent multiple instances of the same widget
				* from trying to fetch data at the same time as this
				* will possibly hog the system. Also prevent new
				* ajax calls when user has decided to reload the page
				*/
				return;
			}
			self.is_updating = true;
			$.ajax({
				url: ajax_url + "widget/" + self.name + "/index/",
				dataType:'json',
				success: function(data) {
					$("#" + self.widget_id + ' .' + self.content_area).html(data);
					self.is_updating = false;
				}
			});
		}
	}

	/*
	*	Save widget settings to db
	*/
	this.save_settings = function(data) {
		var url = ajax_url + "save_widget_setting/";
		$.post(url, data);
		$.jGrowl(sprintf(_widget_settings_msg, self.name), { header: _success_header });
	};

	/*
	*	Save custom widget setting
	*/
	this.save_custom_val = function(newval, fieldname) {
		var ajax_url = _site_domain + _index_page + '/ajax/';
		var url = ajax_url + "save_dynamic_widget_setting/";
		var data = {page: _current_uri, fieldvalue: newval, fieldname:fieldname, widget: self.name};
		$.post(url, data);
		$.jGrowl(sprintf(_widget_settings_msg, self.name), { header: _success_header });
	};

	/**
	*	Get saved settings for widget
	*	So far only refresh_interval is handled
	*/
	this.get_settings = function() {
		$.ajax({
			url: ajax_url + "get_widget_setting/",
			dataType:'json',
			data: {page: self.current_uri, widget:self.name},
			type: 'POST',
			success: function(data) {
				if (data.refresh_interval) {
					self.current_interval = data.refresh_interval;
					self.set_refresh_interval(true);
					$('input[name=' + self.name + '_refresh]').val(self.current_interval);
					$("#" + self.name + "_slider").slider("value", self.current_interval);
				}
			},
			error: function(obj, msg){
				// disable annoying error message
				//$.jGrowl(sprintf(_widget_settings_error, self.name), { header: _error_header });
			}
		});

	};

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
			// don't save an empty title
			if (value.length) {
				self.save_settings(data);
				self.title = value;
			} else {
				value = self.title;
			}
			return value;
		}, {
			type : 'text',
			style : 'margin-top: -4px',
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
		$.jGrowl(sprintf(_widget_notfound_error, name), { header: _error_header });
		return false;
	}
	this.set_content_area(content_area);

	// only enable refresh and interval editing
	// if we have a content_area
	if (this.content_area) {
		this.set_refresh_interval(true);
		this.init_slider();
	}
	if (this.no_edit != true)
		this.init_title_edit();
}