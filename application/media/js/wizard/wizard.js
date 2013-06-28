
(function($) {

	$.fn.populate = function( data ) {

		var group = $('<colgroup>'),
			head = $('<thead>'),
			body = $('<tbody>'),
			row, y, x;

		for ( x in data[0] ) {
			group.append('<col style="width: 50%" />');
			head.append('<th>' + x + '</th>');
		}

		for ( y = 0; y < data.length; y++ ) {
			row = $('<tr>');
			for ( x in data[y] ) {
				row.append( '<td>' + data[y][x] + '</td>' );
			}
			body.append( row );
		}

		this.append( group, head, body );
		return this;

	};

})(jQuery);

(function($) {

	$.fn.getAttributes = function() {

		var attributes = {};

		if ( this.length ) {
			$.each( this[0].attributes, function( index, attr ) {
				attributes[ attr.name ] = attr.value;
			} ); 
		}

		return attributes;
	};

})(jQuery);

(function () {

	var config = {

		height: 330,

		strings: {

			last_button_text:		'Previous Step',	// In each step, the last button will show this
			next_button_text:		'Next Step',		// In each step, the next button will show this
			proceed_button_text:	'Next Step',		// In a step chaining to another wizard, show this text as the next button
			continue_button_text:	'Add another',		// When listing completed objects, the next button will show this
			close_button_text:		'Close',			// Close wizard button
			abort_button_text:		'Abort',			// Abort current object button
			save_button_text:		'Save all',			// Save all objects button

			last_button_help:		'Return to the previous step!',
			next_button_help:		'Proceed to the next step!',
			proceed_button_help:	'Proceed to the next step!',
			continue_button_help:	'Continue adding another object!',
			close_button_help:		'Close the wizard without saving, throws away all unsaved configurations.',
			abort_button_help:		'Aborts the current object setup and sends you back to the first step.',
			save_button_help:		'Save all created objects listed here.'

		},

		selectors: {
			chain: 'input[type="hidden"].wizard-chain'
		}

	};

	var password = 'monitor',
		username = 'monitor';

	function capitalize ( s ) {
		s = s.split(' ');
		for ( var i = s.length; i--; ) {
			s[i] = s[i].substr(0, 1).toUpperCase() + s[i].substr(1);
		}
		return s.join(' ');
	} 

	function friendly ( s ) {
		s = s.replace('check_', '');
		s = s.replace('check-', '');
		s = s.replace('check ', '');
		return s.replace(/\_/g, ' ');
	}

	window.Wizard = function ( which, editing ) {

		var body = $('body'),
			self = this;

		this.original	= which || 'host';
		this.which		= which || 'host';
		this.maintitle	= which;
		this.step		= 0;
		this.steps		= [];
		this.editing	= editing || false;

		this.object		= false;
		this.chain		= false;
		this.isChained	= false;
		this.changes	= {};
		this.nextDelay	= false;

		this.overlay 		= $('<div class="wizard-overlay">');
		this.box 			= $('<div class="wizard-box">');
		this.body 			= $('<div class="wizard-body">');
		this.title 			= $('<div class="wizard-title">');
		this.buttons 		= $('<div class="wizard-buttons">');
		this.progressbar 	= $('<div class="wizard-progress">');

		this.overlay.fadeIn(300, function () {
			self.box.fadeIn(300);
			self.adjust();
		});

		this.routines();
		this.routine = false;

		this.box.append( this.title );
		this.box.append( this.body );
		this.box.append( this.progressbar );
		this.box.append( this.buttons );

		this.overlay.append( this.box );

		this.onNext		= [];
		this.onClose	= [];

		body.append(this.overlay);

		var keyupValidate = function ( e ) {
			self.validate();
		}

		body.bind( 'keyup', keyupValidate );
		this.onClose.push( function () { body.unbind( 'keyup', keyupValidate ); } );

		var changeAction = function ( e ) {
			var target = $( e.target );

			self.object = (!self.object) ? {} : self.object;
			self.validate();

			if ( target.attr('name') ) {

				if ( target.attr('name').indexOf('[]') >= 0 ) {

					var multiname = target.attr('name').replace('[]', '');

					if (!self.object[ multiname ])
						self.object[ multiname ] = [];

					self.object[ multiname ].push( target );

				} else {
					self.object[ target.attr('name') ] = target;
				}
			}

			Wizard.object = self.object;

			$('input[type="hidden"].wizard-chain').map(

				function ( ) {

					var chain = $( this ),
						name = chain.html();

					if ( name.indexOf('%') >= 0 ) {

						var match = name.match(/\%([a-zA-Z0-9]+)\%/g)[0];
						name = name.replace( match, Wizard.object[ match.replace(/\%/g, '') ].val() );

						self.chain = name;

					} else self.chain = name;

					return this;

				}

			);

			
		}

		body.bind( 'change', changeAction );
		this.onClose.push( function () {
			body.unbind( 'change', changeAction );
		} );

		var helpbox = $('<div class="wizard-helpbox">'),
			helpHover = function ( e ) {

			var target = $(e.target);

			if ( target.is('input, select, img, label, p') ) {

				if ( (help = target.attr('help')) ) {
					var offsets;

					offsets = target.offset();
					helpbox.html(help);

					helpbox.css( {
						'left': ( offsets.left + target.width() ) + 'px',
						'top': ( offsets.top + $(document).scrollTop() + target.height() ) + 'px',
					} );

					helpbox.fadeIn(400);
					self.overlay.append(helpbox);

					target.bind( 'mouseout', function (e) {

						helpbox.fadeOut(400, function () {
							helpbox.remove();
						});

					} );

				}

			}
		}

		body.bind( 'mouseover', helpHover);

		this.onClose.push( function () { body.unbind( 'mouseover', helpHover); } );

		this.wml( this.which );
		window.WizardInstance = this;

	};

	Wizard.WizardSchema = {

		"chain":		'<input type="hidden" class="wizard-chain">',
		"routine":		'<script>',

		"label":		'<label>',
		"info":			'<p>',
		"note":			'<p class="wizard-note">',
		"warning":		'<p class="wizard-warning">',
		"detail":		'<div class="wizard-detail">',
		"api":			'<span>',

		"argument":		'<label class="wizard-argument">',

		"descision":	'<label class="wizard-descision" data-wizard-type="descision">',
		"text": 		'<input type="text" data-wizard-type="text">',
		"address": 		'<input type="text" data-wizard-type="address">',
		"password":		'<input type="password" data-wizard-type="text">',
		"number":		'<input type="text" data-wizard-type="number">',
		"float":		'<input type="text" data-wizard-type="float">',
		"radio":		'<input type="radio">',
		"check":		'<input type="checkbox">',

	};

	window.WizardInstance;
	window.Wizard.all = [];

	window.Wizard.insert = function ( node ) {
		$( WizardInstance.body.find('form') ).append( node );
	};

	window.Wizard.prototype = {

		'error': (function () {

			var error_html	= '<h1>Error</h1>';
			error_html		+= '<p class="wizard-note">The wizard seems to have encountered a problem! <br />';
			error_html		+= 'Sorry for the inconvenience!</p>';

			return function ( message ) {
				var self = this;

				if ( this.nextDelay ) clearTimeout( this.nextDelay );

				setTimeout( function () {
					self.body.html(

						'<form>' +
							error_html +
							( (message) ? '<p class="wizard-warning">' + message + '</p>' : '' ) +
						'</form>'

					);
				}, 300);
			}

		}) (),

		'routines': function () {

			var self = this;

			$.ajax({

				url: _wizards_path + 'routines/' + this.which + '.js',
				dataType: "script",

				success: function ( e ) {
					if ( !Wizard.create || !Wizard.save || !Wizard.list ) {
						self.error(
							_wizards_path + 'routines/' + self.which + '.js' +
							'<br />This routine definition does not supply the ' +
							'required Wizard.save, Wizard.list and Wizard.create routines!'
						);
					}
				},

				error: function ( e ) {

					self.error(
						'This object-type has no routine defined in ' +
						_wizards_path + 'routines!'
					);
				}

			});

		},

		'save': function () {

			this.wml( 'save' );

		},

		'adjust': function ( slow ) {

			slow = slow || false;

			var form = $( this.body.find('form') ),
				margin = 0;

			if ( config.height < form.height() ) margin = 0;
			else margin = (config.height - form.height()) / 2;

			if ( slow ) form.animate({ 'margin-top': margin});
			else form.css({'margin-top': margin});

		},

		'detail': function ( summary, detail ) {

			var self = this;

			summary.click(
				function ( e ) {

					if ( detail.css('display') == 'block' ) {
						detail.fadeOut(400, function () {
							self.adjust( true  );
						});
					} else {
						detail.fadeIn(400, function () {
							self.adjust( true  );
						});
					}

				}
			);

		},

		'parseVarDefs': function ( string ) {

			function replacer ( match ) {
				var prop = match.replace(/%/gi, '');
				if ( object[prop] ) string.replace( match, object[prop] );
			}

			string.replace( /%{1}([a-zA-Z0-9]+)%{1}/gi, replacer );

			return string;
		},

		'map': function ( step, what ) {

			var mappable = step.find( what ),
				newNode, oldNode,
				tag, attributes,
				self = this;

			function addAttributes( node, attrs ) {

				var condition;

				for ( var attr in attrs ) {

					if ( attrs[attr] && attrs[attr][0] == '?' ) {

						var condition = attrs[attr].substr(1);
						condition = eval(condition);

						if (condition) {

							node.attr( attr, condition );

							var to = setTimeout( function () {
								node.change();
								clearTimeout( to );
							}, 200);

						}

					} else {

						if ( attrs[attr] ) {
							node.attr( attr, attrs[attr] );
						} else {
							node.attr( attr, attr );
						}

					}
				}

			}

			for ( var i = 0; i < mappable.length; i++ ) {

				newNode = $( Wizard.WizardSchema[ what ] );
				oldNode = $( mappable[i] );

				attributes	= oldNode.getAttributes();
				tag			= oldNode.prop('tagName').toLowerCase();

				if (attributes['if']) {
					var cond = eval( attributes['if'] );
					if ( !cond ) {
						oldNode.remove();
						continue;
					}
				} 

				if ( attributes.image ) {
					newNode.append( $('<img src="' + _wizards_path + attributes.image + '">') );
				}

				if ( tag == 'routine' ) {

					var rname = "inlineScript_" + parseInt(Math.random() * 9999, 10),
						script = "var "+rname+" = setTimeout(function () {" +
							"clearTimeout(" + rname + ");" +
							this.parseVarDefs( oldNode.html() ) + 
						"}, 200);";

					newNode.append(
						unescape( script )
					);

					oldNode.replaceWith( newNode );

				} else if ( tag == 'api' ) {

					(function ( n, o, a ) {

						self.api( n,function ( fragment ) {
							addAttributes( fragment, a );
							o.replaceWith( fragment );
						}, a );

					}) ( newNode, oldNode, attributes );

				} else if ( tag == 'detail' ) {

					var summary = $('<div class="wizard-summary">'),
						sumtext = oldNode.find('summary').html(),
						label = $( '<label class="wizard-summary-text">' ),
						check = $( '<input type="checkbox" />' );

					oldNode.find('summary').remove();
					label.append( check , (sumtext || 'Show more...') );
					summary.append(label);

					oldNode.replaceWith( newNode );
					newNode.before( summary );
					newNode.html( oldNode.html() );

					this.detail( check, newNode );

				} else if ( tag == 'descision' ) {

					var radio = $('<radio>');

					addAttributes( radio, attributes );

					newNode.append( radio );
					newNode.append( 
						$('<p>').append(
							$('<b>').html( capitalize( friendly( attributes.title ) ) )
						).append(
							oldNode.html()
						)
					);

					oldNode.replaceWith( newNode );

				} else {

					addAttributes( newNode, attributes );
					newNode.html( oldNode.html() );
					oldNode.replaceWith( newNode );

				}

			}

		},

		'api': (function () {

			var datas = {};

			var format = function ( type, old, format, fields, callback ) {

				var data = datas[ type ];

				if ( format == 'select' ) {

					var fragment = $('<select name="api-select">');
					for ( var i = 0; i < data.length; i++ ) {
						fragment.append(
							$('<option value="' + data[i].name + '">' + data[i].name + '</option>')
						);
					}

					var attrs = old.getAttributes();
					for ( var attr in attrs ) {
						fragment.attr( attr, attrs[attr] );
					}

				}

				callback( fragment );

			};

			return function ( old, callback, atts ) {

				var type,
					tag;

				if (!atts['data-type']) {
					this.error("No data-type specified to fetch from API!")
					return;
				}

				tag = atts['data-format'] || 'select';
				fields = atts['data-fields'] || '';

				fields = fields.split(',');
				type = atts['data-type'];

				if ( !datas[type] ) {

					$.ajax( '/api/status/' + type + '?format=json',
						{
							'username': username,
							'password': password,
							'complete': function ( e ) {
								datas[type] = JSON.parse(e.responseText);
								format( type , old, tag, fields, callback );
							}
						} 
					)

				} else {
					format( type , old, tag, fields, callback );
				}

			}

		}) (),

		'mapping': function ( step ) {

			var mapping;

			step.element.html(step.domstring);
			step = step.element;

			for ( mapping in Wizard.WizardSchema ) {
				this.map( step, mapping );
			}

		},

		'getTitle': function () {
			return capitalize( this.maintitle ) + ' - ' + 
					capitalize( this.steps[ this.step ].title );
		},

		'next': function ( e, novalidation ) {

			if ( !novalidation ) {
				if ( !this.validate() ) {

					alert( 'Not all required fields are filled or valid!' );
					return;

				}
			}

			var step = this.steps[ this.step ],
				self = this;

			this.body.css('opacity', '0');
			this.progress();
			this.changes = {};

			for ( var i = this.onNext.length; i--; ) {
				this.onNext.pop()();
			}

			this.nextDelay = setTimeout( function () {

				self.title.html("");
				self.body.html("");

				if ( step.element.children() ) {
					self.mapping( step );
				}

				self.body.append( step.element );
				step.title = step.element.find('step')[0].getAttribute('title');
				self.title.html( self.getTitle() );

				self.isChained = ( $( config.selectors.chain ).length > 0 );
				self.actions();

				self.body.animate('scrollTop', 200);
				self.body.css('opacity', '1');
				self.step++;

				self.adjust();

				clearTimeout( self.nextDelay );

			}, 50 );


		},

		'last': function (e) {

			this.step -= 2;
			this.next( null, true );

		},

		'close': function (e) {
			if ( Wizard.all.length > 0 ) {
				if ( confirm('Closing this window will remove all changes not saved,\nare you sure you want to close the wizard?') ) {
					this.overlay.remove();
					for ( var i = this.onClose.length; i--; ) {
						this.onClose[i]();
					}
				}
			} else {
				this.overlay.remove();
				for ( var i = this.onClose.length; i--; ) {
					this.onClose[i]();
				}
			}
		},

		'continue': function ( e, novalidation ) {

			if ( this.validate() || novalidation == true ) {

				this.step = 0;
				this.steps = [];
				this.which = this.original;

				Wizard.object = {};

				this.wml( this.which );

			} else {

				alert( 'Not all required fields are filled or valid!' );

			}

		},

		'validate': ( function () {

			function validateText ( input ) {

				var text = $.trim( input.val() );
				if (text.length > 0) return true;
				return false;

			}

			function validateNumber ( input ) {

				var number = input.val();
				if ( parseInt( number, 10 ) ) return true;
				return false;

			}

			function validateFloat ( input ) {

				var number = input.val();
				if ( parseFloat( number ) ) return true;
				return true;

			}

			function validateAddress ( input ) {

				var text = $.trim( input.val() );
				if (text.length >= 3) return true;
				return false;

			}

			return function ( input ) {

				var checks = {},
					form = this.body.find('form'),
					valid = true;

				form.find('input').map( function () {

					var input = $( this );

					if ( input.attr('required') && input.attr('name') ) {

						if ( input.attr('type') == 'radio' || input.attr('type') == 'checkbox' ) {

							if (!checks[ input.attr('name') ]) {
								checks[ input.attr('name') ] = false;
							}

							if ( input.context.checked == true ) {
								checks[ input.attr('name') ] = true;
							}

						} else {

							if ( input.attr('data-wizard-type') == 'text' ) {
								if ( !validateText( input ) ) {
									valid = false;
								}
							} else if ( input.attr('data-wizard-type') == 'number' ) {
								if ( !validateNumber( input ) ) {
									valid = false;
								}
							} else if ( input.attr('data-wizard-type') == 'float' ) {
								if ( !validateFloat( input ) ) {
									valid = false;
								}
							} else if ( input.attr('data-wizard-type') == 'address' ) {
								if ( !validateAddress( input ) ) {
									valid = false;
								}
							}

							if (valid) {
								input.addClass('valid');
							} else {
								input.removeClass('valid');
								return;
							}
							

						}

					}

				} );

				for ( var check in checks ) {
					if ( !checks[ check ] ) {
						return false;
					}
				}

				return valid;

			}

		} ) (),

		'proceed': function ( e ) {

			if ( this.validate() ) {

				if ( this.chain ) {

					var where = this.chain;

					this.chain = false;
					this.isChained = false;

					this.which = where;
					this.wml( where );

				}

			} else {

				alert( 'Not all required fields are filled or valid!' );

			}

		},

		'button': function ( action, prepend ) {

			var button = $('<button>'),
				self = this;

			if ( prepend ) pend = 'prepend';
			else pend = 'append';

			action = (this.isChained && action == 'continue') ? 'proceed' : action;

			button.html( config.strings[ action + '_button_text' ] ).on( 
				'click', function (e) { 
					self[action]( e ); 
				}
			);

			button.attr( 'title', config.strings[ action + '_button_help' ] );
			button.addClass( action + 'button' );

			if ( this.buttons.find( '.' + action + 'button' ).length == 0 ) {
				this.buttons[ pend ]( button );
			}

		},

		'actions': function () {

			this.buttons.html('');

			if ( this.step == this.steps.length - 1 ) this.button( 'continue' );
			else this.button( 'next' );
			if ( this.step > 0 ) this.button( 'abort' );
			this.button( 'close' );
			if ( this.step > 0 ) this.button( 'last' );
			this.buttons.append( $( '<div style="clear: both;"></div>' ) );

		},

		'abort': function () {
			if ( confirm("This will remove the current object you are creating and return you to the first step, it will not affect already finished objects.") ) {
				this.object = {};
				this['continue']( null, true );
			}
		},

		'progress': function () {

			this.progressbar.css(
				'width',
				parseInt(
					( (this.step + 1) / this.steps.length) * 100, 10
				) + '%'
			);

		},

		'parse': function ( fragment ) {

			var fragments = fragment.match( /\<step((.|\n|\r)*?)\<\/step\>/gi ),
				step;

			if (!fragments) {
				self.error('Malformed! Could not parse the WML into a wizard!');
			} else {

				var title = fragment.match( /\<wizard title="(.*)/gi )[0];
				title = title.replace('<wizard', '').replace('>', '').replace(/"/g,'').split('=')[1];
				this.maintitle = title;

				this.isChained = false;

				for ( var i = 0; i < fragments.length; i++ ) {

					step = $('<form>');

					this.steps.push( {
						domstring: fragments[i],
						title: "",
						element: step
					} );

				}

				this.next();

			}

		},

		'wml': function ( wml ) {

			/* 

				Loads a WML document and runs the parse function if it exists

				Can take an identifier (just the name of the WML file) if it resides
				in the ../js/wizard/wizards/ folder, otherwise, send in the full path.

				E.g. Sending in 'host' will load '../wizards/host.wml'
								'host_scanning' will load '../wizards/host_scanning.wml'

								etc.

			*/

			var self = this,
				path = _wizards_path + 'wizards/' + wml + '.wml';

			if ( wml.indexOf('/') >= 0 ||
				 wml.indexOf('.') >= 0 ||
				 wml.indexOf('\\') >= 0 ) {

				path = wml;

			}

			$.ajax(
				path, {
					dataType: 'text',

					complete: function ( e ) {
						self.parse( e.responseText );
					},

					error: function ( e ) {
						alert( 'Could not load WML file from given path: ' +  path );
						self.close();
					}

				}
			);

		}

	}

}) ();