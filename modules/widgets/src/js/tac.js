
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
			dashboard_id : _dashboard_id
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
	/* If we can't change the dashboard, don't load handling of widgets */
	if (typeof _dashboard_can_write === 'undefined' || !_dashboard_can_write)
		return;
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
			onDragStop: function () {
				$('.widget-place').removeClass('widget-place-highlight');
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
				// This callback is used when changing settings on a widget.
				new widget(w.data('key'));
			}
		}
	});

	$('.content.dashboard').on('mouseup', function () {
		$('.widget-place').removeClass('widget-place-highlight');
	});

	$('.widget').each(function() {
		$.fn.AddEasyWidget($(this), $(this).parent().id, easywidgets_obj);
		FormModule.add_form($(this));
	});

	// Fade widget titles if they are too long to fit.
	$('.widget-title').each(function() {
		if (this.offsetWidth < this.scrollWidth) {
			$(this).append($('<div class="fade-widget-title">'));
		}
	});

	$('body').on( "click", ".menuitem_widget_add", function(e) {
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
				FormModule.add_form(new_widget);
			},
			error: function () {
				Notify.message('Could not save new widget to settings');
			}
		});
		return false;
	});

	$('body').on(
		"click",
		".menuitem_change_layout",
		function(e) {
			e.preventDefault();

			// In Chrome we need to remove shadow and border.
			$('.menu ul li ul').css({"box-shadow": 0, "border": 0});

			// Hide menu after click.
			$('.menuitem_change_layout').hide();

			// Post data about layout change.
			$('<form>')
				.hide()
				.attr({method: 'post', action: _site_domain + _index_page + '/tac/change_layout'})
				.append($('<input>').attr(
					{type: 'hidden', name: 'csrf_token', value: _csrf_token}
				))
				.append($('<input>').attr(
						{type: 'hidden', name: 'layout', value: $(this).attr('data-layout-name')}
					))
				.append($('<input>').attr(
						{type: 'hidden', name: 'dashboard_id', value: _dashboard_id}
					))
				.appendTo('body')
				.submit();
		}
	);

});

$(function() {
	$(".menuitem_dashboard_option").fancybox({
		showCloseButton: false
	});
	$(document).on('click', ".dashboard-form-cancel", function(e) {
		e.preventDefault();
		$.fancybox.close();
		return false;
	});
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

	this.form.on('submit', function(ev) {
		ev.preventDefault();
		self.save_settings_delayed();
	});

	this.header.on('mousedown', function () {
		$('.widget-place').addClass('widget-place-highlight');
	});


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
	var submit_button = this.form.find('input[type=submit]');
	if(loading) {
		submit_button.prop('disabled', true);
		var original_title = submit_button.data('nonloading_value');
		if(typeof original_title !== "undefined") {
			// just make sure that we are not ending up with
			// "lala (loading) (loading)..."
			submit_button.val(original_title);
		}
		submit_button.data('nonloading_value', submit_button.val());
		submit_button.val(submit_button.val() + " (loading)");
		if(loadimg.length) {
			return;
		}

		widget_header.append($('<img class="widget_loadimg" />')
												 .attr('src', _site_domain + 'application/media/images/loading_small.gif')
												);
	} else {
		loadimg.remove();
		submit_button.prop('disabled', false);
		submit_button.val(submit_button.data('nonloading_value'));
	}
};

/**
 * Save widget settings to db. Shouldn't be used directly,
 * use widget.update_widget_delayed() instead.
 */
widget.prototype.save_settings = function() {

	var self = this;
	var data = this.form.serializeArray().reduce(function (data, item) {
		if (item.name == 'csrf_token') {
			return data;
		}

		var temp_match;
		// we want to send
		// setting[host][name]
		// instead of
		// setting[host[name]]
		// because the latter is invalid and will not get parsed by php
		//
		// NOTE: this only handles one dimension.. foo[bar] = correct match, foo[bar][baz] = bad match
		if(temp_match = item.name.match(/(.+)\[([^\]]+)\]/)) {
			if(typeof data[temp_match[1]] === "undefined") {
				data[temp_match[1]] = {};
			}
			data[temp_match[1]][temp_match[2]] = item.value;
		} else {
			data[item.name] = item.value;
		}
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
				var title = self.title_element;
				if (data.custom_title) {
					title.text(data.custom_title);
					self.form.find('*[name="title"]').val(data.custom_title);
				} else {
					title.text(data.title);
				}

				// Fade out title if it is too long to fit.
				if (title.prop('offsetWidth') < title.prop('scrollWidth')) {
					title.append($('<div class="fade-widget-title">'));
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
