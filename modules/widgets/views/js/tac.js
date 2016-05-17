
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
		}, data),
		complete: function(jqHXR, textStatus) {
			if(callbacks.complete) {
				callbacks.complete(jqHXR, textStatus);
			}
		},
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
			effectDuration : 0,
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
					key : widget.attr('data-key')
				}, {
					error: function () {
						Notify.message('Could not save removal of widget to settings');
					}
				});
			},
			onHide : function(widget) {
			},
			onAdd : function(w, place) {
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

			var cell_name = 'widget-placeholder0';
			tac_send_request('on_widget_add', {
				widget : widget_name,
				cell   : cell_name
			}, {
				success : function(data) {
					var new_widget = $(data.widget);
					$('#' + cell_name).prepend(new_widget);
					$.fn.AddEasyWidget(new_widget, new_widget.parent().id, easywidgets_obj);
				},
				error: function () {
					Notify.message('Could not save new widget to settings');
				}
			});
			return false;
		}
	);

	$('body').on(
		"click",
		".menuitem_change_layout",
		function(e) {
			e.preventDefault();

			// Post data about layout change.
			$('<form>')
				.hide()
				.attr({method: 'post', action: ''})
				.append($('<input>').attr(
					{type: 'hidden', name: 'csrf_token', value: _csrf_token}
				))
				.append($('<input>').attr(
					{type: 'hidden', name: 'layout', value: $(this).attr('data-layout-name')}
				))
				.appendTo('body')
				.submit();
		}
	);
});

/**
 * Ninja widget class
 */
function widget(key) {
	var self = this;

	this.current_uri = _current_uri;
	this.is_updating = false;

	this.key = key;

	this.elem = $('#widget-' + this.key);
	this.header = this.elem.find('.widget-header');

	this.title_element = this.elem.find('.widget-title');
	this.form = this.elem.find('form');

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
	this.update_widget_timer = false;
	this.update_widget_time = this.refresh_element.val();
	this.update_widget_delayed();
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
	this.set_loading(true);
	this.save_settings_timer = setTimeout(function() {
		self.save_settings();
	}, this.save_settings_time);
};

widget.prototype.set_loading = function(loading) {
	var widget_header = this.elem.find('.widget-header');
	var loadimg = widget_header.find('.widget_loadimg');
	if(loading) {
		if(loadimg.length) {
			return;
		}

		widget_header.append(
			$('<img class="widget_loadimg" />')
				.attr('src', _site_domain + 'application/media/images/loading_small.gif')
				.css({
					'opacity': 0.4,
					'padding-left': '15px',
					'width': '12px',
					'height': '12px'
				})
		);
	} else {
		loadimg.remove();
	}
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
		key : this.key,
		setting : data
	}, {
		complete : function() {
			self.set_loading(false);
			var upd_time = self.elem.find('.refresh_interval').val();
			self.update_widget_time = upd_time;
			self.update_widget();
		},
		error: function (jqXHR) {
			var msg = 'Could not save updated widget options to settings';
			try {
				var data = JSON.parse(jqXHR.responseText);
				if(data.result) {
					msg += ': ' + data.result;
				}
			} catch(e) {}
			Notify.message(msg, {
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
	if (this.elem.is(':visible')) {

		if (this.is_updating)
			return;

		self.set_loading(true);
		this.is_updating = true;

		tac_send_request('on_refresh', {
			key : self.key
		}, {
			complete : function() {
				self.set_loading(false);
				self.is_updating = false;
			},
			success : function(data) {
				self.elem.find('.widget-content').html(data.widget);
				if (data.custom_title) {
					self.title_element.text(data.custom_title);
					self.form.find('*[name="title"]').val(data.custom_title);
				} else {
					self.title_element.text(data.title);
				}
			},
			error: function (jqXHR) {
				var reason = "";
				try {
					reason = ": "+JSON.parse(jqXHR.responseText).result;
				} catch (err) {}
				Notify.message('There was an error refreshing the widget ' + self.key + reason, {
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
