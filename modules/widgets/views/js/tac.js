function tac_send_request(method, data, callbacks) {
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
			if (callbacks.error) {
				callbacks.error(data);
			} else {
				Notify.message("Error calling " + method);
			}
		}
	});
}

$(function() {
	var easywidgets_obj = $.fn
			.EasyWidgets({
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
						});
					},
					onClose : function(link, widget) {
						tac_send_request('on_widget_remove', {
							name : widget.attr('data-name'),
							instance_id : widget.attr('data-instance_id')
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
					}
				});
				return false;
			});
});

/*******************************************************************************
 * Main widget class
 ******************************************************************************/

/**
 * Ninja widget class
 */
function widget(name, instance_id) {
	var self = this;

	this.current_uri = _current_uri;
	this.is_updating = false;

	this.id = name + '-' + instance_id;
	this.name = name;
	this.instance_id = instance_id;
	this.widget_id = 'widget-' + this.id;

	this.elem = $('#widget-' + this.id);

	this.title = $('#' + this.id + '_title').text();

	/*
	 * Save settings timer information
	 *
	 * Note: time is in milliseconds
	 */
	this.save_settings_timer = false;
	this.save_settings_time = 2000;
	$('#' + this.id + '_form').on('change keyup', function() {
		self.save_settings_delayed();
	});

	/*
	 * Widget refresh timer information
	 *
	 * Note: time is in seconds
	 */
	this.update_widget_timer = false;
	this.update_widget_time = this.elem.find('.refresh_interval').val();
	this.update_widget_delayed();

	// only enable refresh and interval editing
	// if we have a content_area and there's a sider div
	if ($("#" + this.widget_id + " .refresh_slider").length) {
		this.init_slider();
	}
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
	var form_pairs = $('#' + this.id + '_form').serializeArray();
	var form_values = {};
	for (var i = 0; i < form_pairs.length; i++) {
		// We don't want csrf_token as a saved parameter, if form helper adds
		// it.
		if (form_pairs[i].name == 'csrf_token')
			continue;
		form_values[form_pairs[i].name] = form_pairs[i].value;
	}
	tac_send_request('on_widget_save_settings', {
		name : this.name,
		instance_id : this.instance_id,
		setting : form_values
	}, {
		success : function(data) {
			/*
			 * We have already throttled the update, so update directly (thus,
			 * no _delayed)
			 */
			var upd_time = self.elem.find('.refresh_interval').val();
			self.update_widget_time = upd_time;

			self.update_widget();
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
	if ($('#' + this.widget_id).is(':visible')) {
		if (this.is_updating) {
			/**
			 * Prevent multiple instances of the same widget from trying to
			 * fetch data at the same time as this will possibly hog the system.
			 * Also prevent new ajax calls when user has decided to reload the
			 * page
			 */
			return;
		}
		this.is_updating = true;

		// add a loading img to indicate update progress
		this.elem
				.find('.widget-header')
				.append(
						$('<img class="widget_loadimg" />')
								.attr(
										'src',
										_site_domain
												+ 'application/media/images/loading_small.gif'));
		this.elem.find('.widget-header .widget_loadimg').css('opacity', 0.4)
				.css('padding-left', '15px').css('width', '12px').css('height',
						'12px');

		tac_send_request('on_refresh', {
			name : self.name,
			instance_id : self.instance_id
		}, {
			success : function(data) {
				self.elem.find('.widget-content').html(data.widget);
				self.is_updating = false;

				// remove load image
				self.elem.find('.widget-header .widget_loadimg').remove();
			}
		});
	}

	/*
	 * Also, schedule the next one, independent of result. If the previous isn't
	 * finished, we should continue, but just skip an update.
	 */
	this.update_widget_delayed();
};

widget.prototype.init_slider = function() {
	var self = this;
	this.elem.find('.refresh_slider').slider(
			{
				value : this.update_widget_time,
				min : 0,
				max : 500,
				step : 10,
				slide : function(event, ui) {
					$("#" + self.widget_id + " .refresh_interval")
							.val(ui.value);
					self.update_widget_time = $(
							'#' + self.widget_id + ' .refresh_interval').val();
					self.update_widget_delayed();
					self.save_settings_delayed(); // Duplicates is blocked,
					// and some javascript
					// updates doesn't trigger.
				}
			});
	this.elem.find('.refresh_interval').val(
			this.elem.find('.refresh_slider').slider("value")).change(
			function() {
				self.elem.find('.refresh_slider')
						.slider("value", $(this).val());
				self.update_widget_time = self.elem.find('.refresh_interval')
						.val();
				self.update_widget_delayed();
				self.save_settings_delayed(); // Duplicates is blocked, and
				// some javascript updates
				// doesn't trigger.
			});
};

/*******************************************************************************
 * Edit widget title
 ******************************************************************************/
widget.prototype.init_title_edit = function() {
	var self = this;
	$("." + this.id + "_editable").editable(function(value, settings) {
		var data = {
			page : self.current_uri,
			widget : self.name,
			instance_id : self.instance_id,
			widget_title : value
		};
		value = $.trim(value);
		// don't save an empty title
		if (value.length) {
			tac_send_request('on_widget_rename', {
				name : self.name,
				instance_id : self.instance_id,
				new_name : value
			});
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
		placeholder : 'Double-click to edit'
	});
};
