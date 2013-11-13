
( function ( $, window ) {

	'use strict';

	/*
		Syntax:

			$.notify( string message [, object options ] )

		To create a notification popup you may simply state:

		$.notify( "Where this is the message you wish to prompt!" )
	*/

	var settings = {

		/* Can be info, warning, critical. Will grant the
			notification-body the classes: jq-notify-type-<type> */
		"type": "info",

		/* true/false, A sticky notification does not fade out and
			must be removed manually */
		"sticky": false,

		/* true/false, Should the notification have a remove button
			in the upper right corner */
		"removable": true,

		/* Milliseconds before a non-sticky notification fades out,
			set to string "auto" and it will fade out depending on the
			length of the message. */
		"fadetime": "auto",

		/* Should the notification be configurable, i.e. should
			it be possible to remove this type of notification for
			the "duration of the session"/"this user" */
		"configurable": false,

		/* Buttons can be added to the notification, set the buttons
			property of the options to an object where each key will
			be used as title and the value used as the callback */
		"buttons": false,

		/**/
		"signature": false,

		/**/
		"remove": false

	};

	var config_options = {
		"always_show": {
			"title": "This notification will show on every page it applies to",
			"name": "Always show"
		}
	}

	var zone,
		notifications = {},
		template = $( '<div class="jq-notify"><div class="jq-notify-body"></div></div>' ),
		tpl_buttons = $( '<div class="jq-notify-buttons"></div>' ),
		tpl_tools = $( '<div class="jq-notify-tools"></div>' ),
		tpl_remove = $( '<div class="jq-notify-remover">x</div>' ),
		tpl_config = $( '<form><select class="jq-notify-config" title="Set when this notification is shown">' +
				'<option value="always_show" title="This notification will show on every page it applies to">Always show</option>' +
				'<option value="hide_time_15" title="This notification will be hidden for 15 minutes">Hide for 15 minutes</option>' +
				'<option value="hide_time_60" title="This notification will be hidden for 1 hour">Hide for 1 hour</option>' +
				'<option value="session_hide" title="This notification will remain hidden until the end of the session">Hide for this session</option>' +
				'<option value="always_hide" title="This notification will never show for you, can be reset under My Account">Always hide</option>' +
			'</select></form>' ),
		tpl_button = $( '<button>' ),
		basepath = false;

	$("document").ready( function () {
		zone = $(".jq-notify-zone");
	} )

	function remove ( notification ) {

		if ( notification && notification.remove ) {
			notification.slideUp( 400, function () {

				notification.remove();

				if ( zone.children().length == 0 ) {
					zone.css( "display", "none" );
				}

				var note, blob = $.notify.configured;
				for ( var signature in notifications ) {

					note = notifications[ signature ];
					blob[ signature ] = {
						"display": note.display,
						"sessionid": note.sessionid,
						"timestamp": Date.now()
					}

				}

				console.log( blob );

				$.ajax( basepath + '/ajax/save_page_setting', {

					data: {
						'type': 'notifications',
						'page': 'notifications_facility',
						'setting': JSON.stringify( blob )
					},

					type: 'POST',
					complete: function ( request ) {
						console.log( request, "saved" );
					}

				});

			} );
		}
	}

	var Notification = function ( message, options ) {

		var opts = $.extend( {}, settings ),
			opt;

		this.message	= message;
		this.display	= "always_show";
		this.sessionid	= "";
		this.signature = ( options && options.signature ) ? options.signature : this.sign( );

		if ( $.notify.configured[ this.signature ] ) {

			var matches,
				cfg = $.notify.configured[ this.signature ];

			if ( cfg.display === "session_hide" && cfg.sessionid === $.notify.sessionid ) {
				return false;
			} else if ( cfg.display === "always_hide" ) {
				return false;
			} else if ( matches = cfg.display.match( /hide_time_(\d+)/ ) ) {

				var time = cfg.timestamp + ( parseInt( matches[1], 10 ) * 60 * 1000 ),
					now = Date.now();

				if ( time - now > 0) {
					return false;
				}

			}

		}

		this.wrapper	= template.clone();
		this.body		= this.wrapper.find( ".jq-notify-body" );
		this.btnbar		= tpl_buttons.clone();
		this.toolbar	= tpl_tools.clone();

		if ( typeof( options ) != "undefined" && typeof( options ) == "object" ) {
			for ( opt in opts ) {
				if ( typeof(options[ opt ]) != "undefined" )
					opts[opt] = options[ opt ];
			}
		}

		this.wrapper.attr( "data-signature", this.signature );

		this.options = opts;
		this.apply();

		this.body.append( "<p>" + this.message + "</p>" );
		this.body.append( this.toolbar );

		zone.css( "display", "block" );
		zone.prepend( this.wrapper );

		this.wrapper.slideDown( 200 );
		notifications[ this.signature ] = this;

		return this;

	}

	Notification.prototype = {

		wrapper: false,
		body: false,
		btnbar: false,
		toolbar: false,

		/* DON'T CHANGE, IT WILL INVALIDATE SETTINGS FOR
			AUTO-SIGNED NOTIFICATIONS */
		sign: function () {

			var reduce = function ( a, p ) {

				while ( a.length > 64 ) {
					p = ( a.length / 3 ) | 0;
					a[ p ] = a[ p ] + a.splice( p * 2, 1 )[0];
					a[ p ] = a[ p ] & a[ p ];
				}
				return a;

			}

			var s = this.message;
			while ( s.length < 128 ) s += s;

			var h = [], i = s.length;

			for ( i; i--; ) h.push( s.charCodeAt( i ) );
			h = reduce( h );

			for ( i = h.length; i--; ) {
				h[i] = ( (h[i] > 300) ? (h[i] / 2) | 0 : h[i] ).toString( 36 );
				h[i] = ( i % 2 == 0 ) ? h[i].toUpperCase() : h[i];
			}

			return h.join( "" ).substr( 32, 64 );

		},

		remove: function () {

			if ( basepath === false ) {
				basepath = _site_domain + _index_page;
			}

			var self = this;
			this.wrapper.slideUp( 200, function () {

				self.wrapper.remove();
				if ( self.options.remove !== false && typeof( self.options.remove ) == "function" ) {
					self.options.remove();
				}

				if ( zone.children().length == 0 ) {
					zone.css( "display", "none" );
				}

				var note, blob = $.notify.configured;
				for ( var signature in notifications ) {

					note = notifications[ signature ];
					blob[ signature ] = {
						"display": note.display,
						"sessionid": note.sessionid,
						"timestamp": Date.now()
					}

				}

				$.ajax( basepath + '/ajax/save_page_setting', {

					data: {
						'type': 'notifications',
						'page': 'notifications_facility',
						'setting': JSON.stringify( blob )
					},

					type: 'POST',
					complete: function ( request ) {
						console.log( request, "saved" );
					}

				});

			});

		},

		getAutoFadetime: function ( ) {
			var size = this.message.split( " " ).length;
			return 500 + ( size * 500 );
		},

		apply: function () {

			var button, title, self = this;

			if ( this.options && this.options.buttons && typeof(this.options.buttons) === "object" ) {

				for ( title in this.options.buttons ) {
					button = tpl_button.clone().append( title );
					button.bind( "click", this.options.buttons[ title ] );
					this.btnbar.append( button );
				}

				this.options.sticky = true;

			}

			this.body.append( this.btnbar );
			this.body.addClass( "jq-notify-type-" + this.options.type );

			var configer;
			if ( this.options.configurable !== false && this.options.removable !== false ) {

				configer  = tpl_config.clone();
				configer.find("select").bind( "change", function ( e ) {
					var value = this.value;
					self.display = value;
					self.sessionid = $.notify.sessionid;
				} );

				this.toolbar.append( configer );

			}

			if ( this.options.removable !== false ) {

				this.toolbar.append( tpl_remove.clone() );

				this.toolbar.bind( "click", function ( e ) {
					var target = $( e.target );
					if ( target.hasClass( "jq-notify-remover" ) )
						self.remove();
				} );

			}

			if ( this.options.sticky === false ) {

				if ( this.options.fadetime === "auto" )
					this.options.fadetime = this.getAutoFadetime();

				var timer = setTimeout( function () {
					self.remove();
					clearTimeout( timer );
				}, this.options.fadetime );

			}

		}

	};

	$.notify = function ( message, options ) {

		var self = new Notification( message, options );
		return self;

	}

	$.notify.sessionid = "";
	$.notify.configured = {};

})( jQuery, window );