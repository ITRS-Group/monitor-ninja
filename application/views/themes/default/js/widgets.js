init_easywidgets();
$(document).ready(function() {
	$(".widget-place").bind('click', function() {
		$("#page_settings").hide();
	});
});

// create array prototype to sole the lack of in_array() in javascript
Array.prototype.has = function(value) {
	var i;
	for (var i = 0, loopCnt = this.length; i < loopCnt; i++) {
		if (this[i] === value) {
			return true;
		}
	}
	return false;
};

function widget_upload()
{
	self.location.href=_site_domain + _index_page + "/upload/";
}

function init_easywidgets(){
	window.easywidgets_obj = $.fn.EasyWidgets({
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
			onClose: function(link, widget) {
				save_widget_state('hide', widget.data('name'), widget.data('instance_id'));
				var menu_siblings = $('.widget-selector').filter('[data-name=' + widget.data('name') + ']');
				var this_entry = menu_siblings.filter('#li-' + widget.data('name') + '-' + widget.data('instance_id'));
				if (menu_siblings.length > 1)
					this_entry.detach();
				else
					this_entry.removeClass('selected').addClass('unselected');
				widget.detach();
			},
			onHide: function(widget) {
				save_widget_state('hide', widget.data('name'), widget.data('instance_id'));
				var menu_siblings = $('.widget-selector').filter('[data-name=' + widget.data('name') + ']');
				var this_entry = menu_siblings.filter('#li-' + widget.data('name') + '-' + widget.data('instance_id'));
				if (menu_siblings.length > 1)
					this_entry.detach();
				else
					this_entry.removeClass('selected').addClass('unselected');
				widget.detach();
			},
			onAdd: function(w) {
				new widget(w.data('name'), w.data('instance_id'));
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
				$('.widget-editbox [name=refresh_interval]').each(function() {
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

function control_widgets(item) {
	var it = $(item);
	if (it.hasClass('selected')) {
		$.fn.HideEasyWidget('widget-' + it.data('name') + '-' + it.data('instance_id'), window.easywidgets_obj);
		it.removeClass('selected').addClass('unselected').data('instance_id', '');
	}
	else {
		copy_widget_instance(it.data('name'), it.data('instance_id'), function(new_widget) {
			if (!it.data('instance_id')) {
				it.data('instance_id', new_widget.data('instance_id'));
			}
		});
		it.removeClass('unselected').addClass('selected');
	}
}

function copy_widget_instance(name, instance_id, cb) {
	$.ajax({
		url: _site_domain + _index_page + '/ajax/copy_widget_instance',
		dataType: 'html',
		type: 'POST',
		data: {page: _current_uri, widget: name, 'instance_id': instance_id},
		success: function(data) {
			var new_widget;
			var this_widget = $('#widget-' + name + '-' + instance_id);
			if (this_widget.length) {
				this_widget.after(data);
				new_widget = this_widget.next('.widget');
			}
			else {
				var container = $('#widget-placeholder');
				container.append(data);
				new_widget = container.find('.widget:last');
			}
			$.fn.AddEasyWidget('#widget-'+new_widget.data('name')+'-'+new_widget.data('instance_id'), new_widget.parent().id, window.easywidgets_obj);
			if (cb)
				cb(new_widget);
		}
	});
}

function save_widget_state(what, widget_name, instance_id)
{
	var url = _site_domain + _index_page + "/ajax/save_widget_state/";
	var page_name = _current_uri;
	var data = {page: escape(page_name), method: what, name: widget_name, 'instance_id': instance_id};
	$.post(url, data);
}

function restore_widgets()
{
	$('li[id^=li_widget]').each(function() {
		var item_id = $(this).attr('id');
		var widget_id = item_id.replace('li_', '');
		$('#' + item_id).removeClass().addClass('selected');
		$.fn.ShowEasyWidget(widget_id, window.easywidgets_obj);
		$('#'+widget_id).addClass('movable');
	});
	fetch_widget_order(true);
}

/**
*	Remove whitespace from string
*/
function trim(str) {
	return str.replace(/^\s+|\s+$/g,"");
}

var loaded_widgets = {};

/**
*	Ninja widget class
*/
function widget(name, instance_id)
{
	var self = this;
	this.ajax_url = _site_domain + _index_page + '/ajax/';

	this._refresh_interval = 0;
	this.save_interval = 0;
	this.current_uri = _current_uri;
	this.content_area = 'widget-content';
	this.is_updating = false;

	// initialize widget
	var widget_ok = this.init_widget(name, instance_id);
	if (widget_ok == false) {
		// here we should probably notify user that
		// the widget isn't found
		$.jGrowl(sprintf(_widget_notfound_error, name), { header: _error_header });
		return;
	}

	// only enable refresh and interval editing
	// if we have a content_area and there's a sider div
	if (this.content_area && $("#" + this.widget_id + " .refresh_slider").length) {
		this.set_refresh_interval(true);
		this.init_slider();
	}
	this.init_title_edit();
	if (widget.widgets[name])
		for (var i in widget.widgets[name])
			widget.widgets[name][i].call(this);
}
widget.loadimg = new Image(16,16);
widget.loadimg.src = _site_domain + 'application/media/images/loading_small.gif';

/*
 *	Initialize some internal values.
 */
widget.prototype.init_widget = function(name, instance_id) {
	var self = this;
	this.set_id(name, instance_id);

	// check that widget isn't already loaded
	if (loaded_widgets[this.id]) {
		return;
	}

	if (!$('#' + this.widget_id).text())
		return false;
	this.current_interval = $('#' + this.widget_id + ' .refresh_interval').val();
	this.title =  $('#' + this.id + '_title').text();
	if (this.current_uri == 'external_widget/show_widget') {
		$('.widget-menu .widget-collapselink').hide();
		$('.widget-menu .widget-closelink').hide();
		$('#' + this.id + '_title').removeClass(this.id + '_editable');
	}

	$('#' + this.widget_id + '.duplicatable .widget-menu').prepend('<a class="widget-copylink" title="Copy this widget" href="#"><img alt="Copy" src="' + _site_domain + _theme_path + 'icons/12x12/copy.png"/></a>');
	$('#' + this.widget_id + ' .widget-copylink').click(function() {
		copy_widget_instance(self.name, self.instance_id, function (new_widget) {
			$('.widget-selector').filter(':last').after('<li id="li-'+new_widget.data('name')+'-'+new_widget.data('instance_id')+'" data-name="'+new_widget.data('name')+'" data-instance_id="'+new_widget.data('instance_id')+'" class="selected widget-selector" onclick="control_widgets(this)">'+new_widget.find('#'+new_widget.data('name')+'-'+new_widget.data('instance_id')+'_title').text()+'</li>');
		});
	});
	loaded_widgets[this.id] = 1;

	return true;
};

widget.prototype.set_current_uri = function(uri) {
	this.current_uri = uri;
};

widget.prototype.set_id = function(name, instance_id) {
	if (!name)
		return;
	this.id = name + '-' + instance_id;
	this.name = name;
	this.instance_id = instance_id;
	this.widget_id = 'widget-' + this.id;
};

/*
*	Fetch current widget state through AJAX call
*/
widget.prototype.update_display = function() {
	var self = this;

	if (this.content_area != false && $('#' + this.widget_id).is(':visible')) {
		if (this.is_updating || _is_refreshing) {
			/**
			* Prevent multiple instances of the same widget
			* from trying to fetch data at the same time as this
			* will possibly hog the system. Also prevent new
			* ajax calls when user has decided to reload the page
			*/
			return;
		}
		this.is_updating = true;

		// add a loading img to indicate update progress
		$("#" + this.widget_id + ' .widget-header').append('<img src="' + widget.loadimg.src + '" class="widget_loadimg" />');
		$("#" + this.widget_id + ' .widget-header .widget_loadimg').css('opacity', 0.4).css('padding-left', '15px').css('width', '12px').css('height', '12px');

		var params = {
			widget_name: this.name,
			instance_id: this.instance_id,
			page: this.current_uri
		};
		$.ajax({
			url: this.ajax_url + "widget/" + this.name + "/index/?" + jQuery.param(params),
			dataType:'json',
			success: function(data) {
				$("#" + self.widget_id + ' .' + self.content_area).html(data);
				self.is_updating = false;

				// remove load image
				$("#" + self.widget_id + ' .widget-header .widget_loadimg').remove();
			}
		});
	}
};

/*
*	Save widget settings to db
*/
widget.prototype.save_settings = function(data) {
	var url = this.ajax_url + "save_widget_setting/";
	$.post(url, data);
	$.jGrowl(sprintf(_widget_settings_msg, this.name), { header: _success_header });
};

/*
*	Save custom widget setting
*/
widget.prototype.save_custom_val = function(newval, fieldname, cb) {
	var self = this;
	var url = this.ajax_url + "save_dynamic_widget_setting/";
	var data = {page: this.current_uri, fieldvalue: newval, fieldname:fieldname, widget: this.name, instance_id: this.instance_id};
	$.post(url, data, function(data) {
		if (typeof cb == 'function')
			cb.call(self, data);
	});
	$.jGrowl(sprintf(_widget_settings_msg, this.name), { header: _success_header });
};

widget.widgets = {};
widget.register_widget_load = function(widget_name, cb) {
	if (!widget.widgets[widget_name])
		widget.widgets[widget_name] = [cb];
	else
		widget.widgets[widget_name].push(cb);
}

/*
 *	Set the refresh interval to use for widget
 *	and also pass this value on to be saved to db
*/
widget.prototype.set_refresh_interval = function(is_init){
	var self = this;
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
		var data = {page: this.current_uri, refresh_interval: this.current_interval, widget: this.name, instance_id: this.instance_id};
		this.save_settings(data);
	}
};

/*
*	Since the slider is possible to move rather fast,
*	we add a delay (timeout) to this before we do anything with it.
*	The timeout is cleared when a new value is selected and only saved
*	until there is no activity (new value) for 5 seconds
*/
widget.prototype.control_save_interval = function() {
	var self = this;
	if (this.save_interval) {
		clearTimeout(this.save_interval);
	}
	this.save_interval = setTimeout(function() {self.set_refresh_interval();}, 5000);
};

widget.prototype.init_slider = function() {
	var self = this;
	$("#" + this.widget_id + " .refresh_slider").slider({
		value: this.current_interval,
		min: 0,
		max: 500,
		step: 10,
		slide: function(event, ui) {
			$("#" + self.widget_id + " .refresh_interval").val(ui.value);
			self.current_interval = ui.value;
			self.control_save_interval();
		}
	});
	$("#" + this.widget_id + " .refresh_interval").val($("#" + this.widget_id + " .refresh_slider").slider("value")).change(function() {
		$("#" + self.widget_id + " .refresh_slider").slider("value", $(this).val());
		self.current_interval = $(this).val();
		self.control_save_interval();
	});

};

widget.prototype.init_title_edit = function() {
	var self = this;
	$("." + this.id + "_editable").editable(function(value, settings) {
		var data = {page: self.current_uri, widget:self.name, instance_id:self.instance_id, widget_title:value};
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
};
