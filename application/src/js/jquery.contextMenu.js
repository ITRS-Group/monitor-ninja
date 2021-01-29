// jQuery Context Menu Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
//
// More info: http://abeautifulsite.net/2008/09/jquery-context-menu-plugin/
//
// Terms of Use
//
// This plugin is dual-licensed under the GNU General Public License
//   and the MIT License and is copyright A Beautiful Site, LLC.
//
if(jQuery)( function() {
	var show_context_menu = function(el, e, o, callback) {
		var offset = el.offset();
		// Add contextMenu class
		$('#' + o.menu).addClass('contextMenu');
		// Simulate a true right click
		var evt = e;
		evt.stopPropagation();
		el.mouseup( function(e) {
			e.stopPropagation();
			el.off('mouseup');
			if( evt.button == 2 ) {
				// Hide context menus that may be showing
				$(".contextMenu").hide();
				// Get this context menu
				var menu = $('#' + o.menu);

				if( el.hasClass('disabled') ) return false;

				// enable/disable menu items
				// depending on obj_prop value
				// start by showing all menu items
				// in case they have been previously disabled
				$('.contextMenu li').show();
				var this_td = $(this);
				var table = this_td.data('table');
				var obj = this_td.data('object');
				var obj_prop = '';

				// TODO only show transitions that are possible
				var OK = 0;
				var ACKNOWLEDGED = 1;
				var NOTIFICATIONS_ENABLED = 2;
				var CHECKS_ENABLED = 4;
				var SCHEDULED_DT = 8;
				if (obj_prop != '') {
					if (obj_prop & ACKNOWLEDGED || !(obj_prop & 16) || obj_prop & OK) {
						$('#_menu_acknowledge_host_problem').hide();
						$('#_menu_acknowledge_svc_problem').hide();
					}
					if (!(obj_prop & ACKNOWLEDGED)) { // || obj_prop & OK
						$('#_menu_remove_host_acknowledgement').hide();
						$('#_menu_remove_svc_acknowledgement').hide();
					}
					if (obj_prop & NOTIFICATIONS_ENABLED) {
						$('#_menu_disable_host_notifications').hide();
						$('#_menu_disable_svc_notifications').hide();
					}
					if (!(obj_prop & NOTIFICATIONS_ENABLED)) {
						$('#_menu_enable_host_notifications').hide();
						$('#_menu_enable_svc_notifications').hide();
					}
					if (obj_prop & CHECKS_ENABLED) {
						$('#_menu_disable_host_check').hide();
						$('#_menu_disable_svc_check').hide();
					}
					if (!(obj_prop & CHECKS_ENABLED)) {
						$('#_menu_enable_host_check').hide();
						$('#_menu_enable_svc_check').hide();
					}
					if (obj_prop & SCHEDULED_DT) {
						$('#_menu_schedule_host_downtime').hide();
						$('#_menu_schedule_svc_downtime').hide();
					}
					if (!(obj_prop & SCHEDULED_DT)) {
						$('#_menu_removeschedule_host_downtime').hide();
						$('#_menu_removeschedule_svc_downtime').hide();
					} else if(el.hasClass('svc_obj_properties')) {
						// Do not offer to cancel scheduled downtime if its host is in scheduled downtime. Look for that.

						// Traverse upwards, looking for a host-td (since it might not be to the immidiate left of this service's td)
						var tr_to_examine = el.parent();
						while(tr_to_examine.find('td').eq(1).hasClass('white')) {
							tr_to_examine = tr_to_examine.prev();
						}

						if(tr_to_examine.find('.service_hostname img[title="Scheduled downtime"]').length > 0) {
							$('#_menu_removeschedule_svc_downtime').hide();
						}
					}
				}

				// Detect mouse position
				var d = {}, x, y;
				if( self.innerHeight ) {
					d.pageYOffset = self.pageYOffset;
					d.pageXOffset = self.pageXOffset;
					d.innerHeight = self.innerHeight;
					d.innerWidth = self.innerWidth;
				} else if( document.documentElement &&
					document.documentElement.clientHeight ) {
					d.pageYOffset = document.documentElement.scrollTop;
					d.pageXOffset = document.documentElement.scrollLeft;
					d.innerHeight = document.documentElement.clientHeight;
					d.innerWidth = document.documentElement.clientWidth;
				} else if( document.body ) {
					d.pageYOffset = document.body.scrollTop;
					d.pageXOffset = document.body.scrollLeft;
					d.innerHeight = document.body.clientHeight;
					d.innerWidth = document.body.clientWidth;
				}
				x = e.clientX;
				y = e.clientY;

				// Show the menu
				$(document).off('click');
				// Make sure menu doesn't extend outside viewport
				if (y + $(menu).height() >= $(window).height()) {
					y = y - $(menu).height();
				}
				$(menu).css({ top: y, left: x , position: 'fixed'}).fadeIn(o.inSpeed);
				// Hover events
				$(menu).find('A').mouseover( function() {
					$(menu).find('LI.hover').removeClass('hover');
					$(this).parent().addClass('hover');
				}).mouseout( function() {
					$(menu).find('LI.hover').removeClass('hover');
				});

				// Keyboard
				$(document).keypress( function(e) {
					switch( e.keyCode ) {
						case 38: // up
							if( $(menu).find('LI.hover').size() == 0 ) {
								$(menu).find('LI:last').addClass('hover');
							} else {
								$(menu).find('LI.hover').removeClass('hover').prevAll('LI:not(.disabled)').eq(0).addClass('hover');
								if( $(menu).find('LI.hover').size() == 0 ) $(menu).find('LI:last').addClass('hover');
							}
						break;
						case 40: // down
							if( $(menu).find('LI.hover').size() == 0 ) {
								$(menu).find('LI:first').addClass('hover');
							} else {
								$(menu).find('LI.hover').removeClass('hover').nextAll('LI:not(.disabled)').eq(0).addClass('hover');
								if( $(menu).find('LI.hover').size() == 0 ) $(menu).find('LI:first').addClass('hover');
							}
						break;
						case 13: // enter
							$(menu).find('LI.hover A').trigger('click');
						break;
						case 27: // esc
							$(document).trigger('click');
						break
					}
				});

				// When items are selected
				$('#' + o.menu).find('A').off('click');
				$('#' + o.menu).find('LI:not(.disabled) A').on("click",  function() {
					$(document).off('click').off('keypress');
					$(".contextMenu").hide();
					// Callback
					if(typeof callback === "function") {
						var a = $(this);
						callback(a.data('cmd'), el.data('table'), el.data('object'));
					}
					return false;
				});


				// Hide bindings
				setTimeout( function() { // Delay for Mozilla
					$(document).on("click",  function() {
						$(document).off('click').off('keypress');
						$(menu).fadeOut(o.outSpeed);
						return false;
					});
				}, 0);
			}
		});

		// Disable text selection
		if( platform.mozilla ) {
			$('#' + o.menu).each( function() { $(this).css({ 'MozUserSelect' : 'none' }); });
		} else if( platform.msie ) {
			$('#' + o.menu).each( function() { $(this).on('selectstart.disableTextSelect', function() { return false; }); });
		} else {
			$('#' + o.menu).each(function() { $(this).on('mousedown.disableTextSelect', function() { return false; }); });
		}
		// Disable browser context menu (requires both selectors to work in IE/Safari + FF/Chrome)
		el.add($('UL.contextMenu')).on('contextmenu', function() { return false; });
	};
	$.extend($.fn, {
		contextMenu: function(o, callback, sel) {
			// Defaults
			if( o.menu == undefined ) return false;
			if( o.inSpeed == undefined ) o.inSpeed = 150;
			if( o.outSpeed == undefined ) o.outSpeed = 75;
			// 0 needs to be -1 for expected results (no fade)
			if( o.inSpeed == 0 ) o.inSpeed = -1;
			if( o.outSpeed == 0 ) o.outSpeed = -1;
			var el = $(this);
			el.on('mousedown', sel, function(e) {
				e.stopPropagation();
				if(3 !== e.which) {
					return false;
				}
				show_context_menu($(this), e, o, callback);
			});
			return $(this);
		},

		// Disable context menu items on the fly
		disableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Disable all
				$(this).find('LI').addClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().addClass('disabled');

					}
				}
			});
			return( $(this) );
		},

		// Enable context menu items on the fly
		enableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Enable all
				$(this).find('LI.disabled').removeClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().removeClass('disabled');

					}
				}
			});
			return( $(this) );
		},

		// Disable context menu(s)
		disableContextMenu: function() {
			$(this).each( function() {
				$(this).addClass('disabled');
			});
			return( $(this) );
		},

		// Enable context menu(s)
		enableContextMenu: function() {
			$(this).each( function() {
				$(this).removeClass('disabled');
			});
			return( $(this) );
		},

		// Destroy context menu(s)
		destroyContextMenu: function() {
			// Destroy specified context menus
			$(this).each( function() {
				// Disable action
				$(this).off('mousedown').off('mouseup');
			});
			return( $(this) );
		}

	});
})(jQuery);
