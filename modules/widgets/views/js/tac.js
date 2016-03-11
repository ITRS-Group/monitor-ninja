
/**
 * Static request helper function
 */
var tac_send_request = function (method, data, callbacks) {

	if (!callbacks)
		callbacks = {};
	$.ajax({
		url : _site_domain + _index_page + "/tac/" + method,
		dataType : 'json',
		type : 'POST',
		data : $.extend({
			csrf_token : _csrf_token,
			page : _current_uri,
		}, data),
		success : function(data) {
			if (callbacks.success) {
				callbacks.success(data);
			}
		},
		error : function(data) {
			if (data.readyState == 0 || data.status == 0) {
				// User aborted request (maybe by clicking the refresh button)
				return;
			}

			if (callbacks.error) {
				callbacks.error(data);
			} else {
				Notify.message(
					"Could not perform action " + method.replace(/\_/g, ' '),
					{
						type: "error"
					}
				);
			}
		}
	});
};

$(function() {
	var easywidgets_obj = $.fn.EasyWidgets({
		behaviour : {
			useCookies : false
		},
		i18n : {
			editText : '<img src="'
			+ _site_domain
			+ 'application/views/icons/12x12/box-config.png" alt="Settings" />',
			closeText : '<img src="'
			+ _site_domain
			+ 'application/views/icons/12x12/box-close.png" alt="Close widget" />',
			collapseText : '<img src="'
			+ _site_domain
			+ 'application/views/icons/12x12/box-mimimize.png" alt="Collapse" />',
			cancelEditText : '<img src="'
			+ _site_domain
			+ 'application/views/icons/12x12/box-config.png" alt="Cancel" />',
			extendText : '<img src="'
			+ _site_domain
			+ 'application/views/icons/12x12/box-maximize.png" alt="Extend" />'
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
			onChangePositions : function(str) {
				tac_send_request('on_change_positions', {
					positions : str
				}, {
					error: function (data) {
						Notify.message(
							'Could not save change in position to settings', {
								type: "error"
							}
						);
					}
				});
			},
			onClose : function(link, widget) {
				tac_send_request('on_widget_remove', {
					name : widget.attr('data-name'),
					instance_id : widget.attr('data-instance_id')
				}, {
					error: function () {
						Notify.message('Could not save removal of widget to settings');
					}
				});
			},
			onHide : function(widget) {
			},
			onAdd : function(w) {
				new widget(w.data('name'), w.data('instance_id'));
			}
		}
	});

	$('.widget').each(function() {
		$.fn.AddEasyWidget($(this), $(this).parent().id, easywidgets_obj);
	});

	$('body').on(
		"click",
		".menuitem_widget_add",
		function(e) {
			var elem = $(this);
			var widget_name = elem.attr('data-widget-name');
			e.preventDefault();

			tac_send_request('on_widget_add', {
				widget : widget_name
			}, {
				success : function(data) {
					var container = $('#widget-placeholder');
					var new_widget = $(data.widget);
					container.append(new_widget);

					$.fn.AddEasyWidget(new_widget, new_widget.parent().id,
							   easywidgets_obj);
				},
				error: function () {
					Notify.message('Could not save new widget to settings');
				}
			});
			return false;
		}
	);
});

/**
 * Ninja widget class
 */
function widget(name, instance_id) {
	var self = this;

	this.current_uri = _current_uri;
	this.is_updating = false;

	this.id = name + '-' + instance_id;
	this.element = $('#widget-' + this.id);
	this.header = this.element.find('.widget-header');

	this.name = name;
	this.instance_id = instance_id;
	this.widget_id = 'widget-' + this.id;

	this.elem = $('#widget-' + this.id);

	this.title_element = $('#' + this.id + '_title');
	this.title = this.title_element.text();
	this.form = $('#' + this.id + '_form');

	/*
	 * Save settings timer information
	 *
	 * Note: time is in milliseconds
	 */
	this.save_settings_timer = false;
	this.save_settings_time = 2000;

	this.form.on('change keyup', function() {
		self.save_settings_delayed();
	});

	this.header.on('mousedown', function () {
		$('.widget-place').addClass('widget-place-highlight');
	}).on('mouseup', function () {
		$('.widget-place').removeClass('widget-place-highlight');
	})

	/*
	 * Widget refresh timer information
	 *
	 * Note: time is in seconds
	 */
	this.refresh_element = this.elem.find('.refresh_interval');
	this.refresh_slider = this.elem.find('.refresh_slider');
	this.update_widget_timer = false;
	this.update_widget_time = this.refresh_element.val();
	this.update_widget_delayed();

	if (this.refresh_slider.length)
		this.init_slider();
	this.init_title_edit();

}

/*******************************************************************************
 * Save widget settings
 ******************************************************************************/
/**
 * Save settings, but after a small delay. This is the main function to use
 */
widget.prototype.save_settings_delayed = function() {
	var self = this;
	if (this.save_settings_timer)
		clearTimeout(this.save_settings_timer);
	this.save_settings_timer = setTimeout(function() {
		self.save_settings();
	}, this.save_settings_time);
};

/**
 * Save widget settings to db directly. Shouldn't be used normally. Use delayed
 */
widget.prototype.save_settings = function() {

	var self = this;
	var data = this.form.serializeArray().reduce(function (data, item) {
		if (item.name == 'csrf_token') return data;
		data[item.name] = item.value;
		return data;
	}, {});

	tac_send_request('on_widget_save_settings', {
		name : this.name,
		instance_id : this.instance_id,
		setting : data
	}, {
		success : function(data) {
			var upd_time = self.elem.find('.refresh_interval').val();
			self.update_widget_time = upd_time;
			self.update_widget();
		},
		error: function (data) {
			Notify.message('Could not save updated widget options to settings', {
				type: "error"
			});
		}
	});
};

/*******************************************************************************
 * Widget refresh
 ******************************************************************************/
/**
 * Save settings, but after a small delay. This is the main function to use
 */
widget.prototype.update_widget_delayed = function() {
	var self = this;
	if (!this.update_widget_time)
		return;
	if (this.update_widget_timer)
		clearTimeout(this.update_widget_timer);
	this.update_widget_timer = setTimeout(function() {
		self.update_widget();
	}, this.update_widget_time * 1000);
};

widget.prototype.update_widget = function() {

	var self = this;
	if (this.element.is(':visible')) {

		if (this.is_updating)
			return;

		this.is_updating = true;
		this.elem.find('.widget-header').append(
			$('<img class="widget_loadimg" />').attr(
				'src', _site_domain + 'application/media/images/loading_small.gif'
			)
		);

		this.elem.find('.widget-header .widget_loadimg').css({
			'opacity': 0.4,
			'padding-left': '15px',
			'width': '12px',
			'height': '12px'
		});

		tac_send_request('on_refresh', {
			name : self.name,
			instance_id : self.instance_id
		}, {
			success : function(data) {
				self.elem.find('.widget-content').html(data.widget);
				self.is_updating = false;
				self.elem.find('.widget-header .widget_loadimg')
					.remove();
			},
			error: function () {
				Notify.message('There was an error refreshing the widget ' + self.name, {
					type: "error"
				});
			}
		});
	}

	/*
	 * Also, schedule the next one, independent of result. If the previous isn't
	 * finished, we should continue, but just skip an update.
	 */
	this.update_widget_delayed();
};

/**
 * Initializes the refresh rate slider for this widget
 */
widget.prototype.init_slider = function() {
	var self = this;
	this.refresh_slider.slider({
		value : this.update_widget_time,
		min : 0,
		max : 500,
		step : 10,
		slide : function(event, ui) {
			self.refresh_element.val(ui.value);
			self.update_widget_time = self.refresh_element.val();
			self.update_widget_delayed();
			self.save_settings_delayed();
		}
	});
};

/**
 * Initializes title editable element for this widget, if the update
 * fails the old title is restored to avoid confusion.
 */
widget.prototype.init_title_edit = function() {
	var self = this;
	var old_title = this.title;
	this.title_element.editable(function(value, settings) {

		var data = {
			page : self.current_uri,
			widget : self.name,
			instance_id : self.instance_id,
			widget_title : value
		};

		value = $.trim(value);
		if (value.length) {
			tac_send_request('on_widget_rename', {
				name : self.name,
				instance_id : self.instance_id,
				new_name : value
			}, {
				error: function () {
					self.title_element.text(old_title);
					Notify.message('Could not update widget title', {
						type: "error"
					});
				}
			});
			self.title = value;
		} else value = self.title;
		return value;
	}, {
		type : 'text',
		style : 'margin-top: -4px',
		event : 'dblclick',
		width : 'auto',
		height : '14px',
		submit : 'OK',
		cancel : 'cancel',
		placeholder : 'Double-click to edit'
	});
};

