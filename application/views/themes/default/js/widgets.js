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
		error: function(obj, msg){alert(msg)}
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
function widget(name, content_area)
{
	var self = this;
	var ajax_url = _site_domain + _index_page + '/ajax/';

	this._refresh_interval = 0;
	this.save_interval = 0;
	this.current_uri = _current_uri;
	this.content_area = false;

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
				error: function(obj, msg){alert(msg)}
			});
		}
	}

	/*
	*	Save widget settings to db
	*/
	this.save_settings = function(data) {
		var url = ajax_url + "save_widget_setting/";
		$.post(url, data);
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
	this.init_title_edit();
}