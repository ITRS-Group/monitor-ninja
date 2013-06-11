
Wizard.create = function ( object ) {

	var errors = [],
		services = [],
		sets = [],
		o;

	for (var service in object.services) {

		o = {};

		service			= $( object.services[service] );

		o.description	= service.val().split('_').join(' ');
		o.check			= service.val();
		o.args			= service.attr('args');

		var auxargs = [], extras = [];

		if ( service.attr('type') == 'checkbox' ) {
			extras = service.siblings('.argument');
		}

		for ( var i = 0; i < extras.length; i++ ) {
			o.args += '!' + $(extras[i]).find('input, select')[0].value;
		}

		if ( o.args ) {
			for ( var prop in object ) {
				if ( object[prop].length == 1 ) {
					o.args = o.args.replace( new RegExp("\%" + prop + "\%", "gi"), object[prop].val() );
				}
			}
		}

		services.push( o );

	}

	for (var set = 0; set < object.sets.length; set++ ) {
		sets.push( object.sets[set].val() );
	}

	var host = {

		"host": {
			"host_name": object.HOSTNAME.val() || object.HOSTADDRESS.val(),
			"alias": (object.ALIAS) ? object.ALIAS.val() : object.HOSTNAME.val().split('_').join(' '),
			"address": object.HOSTADDRESS.val(),
			"hostgroups": sets
		},

		"os": object.OS.val(),
		"poller": (object.POLLER) ? object.POLLER.val() : 'monitor',
		"services": services

	};

	if ( object.CHECK_PERIOD && object.CHECK_PERIOD.val() ) {
		host.host.check_period = object.CHECK_PERIOD;
	}

	if ( object.NOTIFICATION_PERIOD && object.NOTIFICATION_PERIOD.val() ) {
		host.host.check_period = object.NOTIFICATION_PERIOD;
	}

	Wizard.all.push(
		host
	);

}

Wizard.save = function (  ) {

	var password = 'monitor',
		username = 'monitor',
		o, object;

	var count = Wizard.all.length,
		created = 0;

	function save_all () {

		$.ajax(
			'/api/config/change',
			{
				'type': "POST",
				'username': username,
				'password': password,
				'complete': function ( e ) {

					console.log( e );
					console.log( 'saved' );

				}
			}
		)

		finish();

	}

	function add_services ( services, host ) {

		function add_service ( service ) {

			$.ajax(
				'/api/config/service',
				{
					'type': "POST",
					'data': {
						check_command: service.check,
						service_description: service.description,
						check_command_args: service.args || '',
						host_name: host
					},
					'username': username,
					'password': password,
					'complete': function ( e ) {

						screated++;
						if ( count == created && scount == screated ) {
							save_all();
						} else if ( scount == screated ) {
							next();
						}

					}
				}
			);

		}

		var scount = services.length,
			screated = 0;

		for ( var i = 0; i < services.length; i++ ) {
			add_service( services[i] );
		}

		if ( count == created && scount == screated ) {
			save_all();
		} else if ( scount == screated ) {
			next();
		}

	}

	function add_host (  ) {

		var host = object.host,
			services = object.services;

		$.ajax(
			'/api/config/host',
			{
				'type': "POST",
				'data': host,
				'username': username,
				'password': password,
				'complete': function ( e ) {

					created++;

					add_services( services, host.host_name );

				}
			}
		)
	}

	function next () {

		object = Wizard.all.shift();
		add_host( object );

		$('.wizard-scan-progress').css(
			'width', parseInt( (created / count) * 100 , 10) + '%'
		)

		$('.wizard-scan-status').html( 'Saving: ' + object.host.host_name );

	};

	function finish () {

		$('.wizard-scan-progress').css(
			'width', '100%'
		);

		$('.wizard-scan-status').html( 'Finished saving all objects, you may now close this window!' );
		$('.closebutton').removeAttr('disabled');

	}

	next();
}

Wizard.list = function () {

	function generate_alias ( ss, s, name ) {

		ss = ss.join(', ').toLowerCase();
		s = s.toLowerCase();
		s += ',' + ss;

		var flags = [],
			virtual = false,
			alias = "";

		if ( s.indexOf('kvm') >= 0 || s.indexOf('xen') >= 0 ) {
			alias = "Virtual ";
		}

		if ( ss.indexOf('http') >= 0 ) {
			flags.push("Web");
		}

		if ( s.indexOf('mysql') >= 0 || s.indexOf('mssql') >= 0 ) {
			flags.push("Database");
		}

		if ( s.indexOf('imap') >= 0 || s.indexOf('pop3') >= 0  || s.indexOf('smtp') >= 0 ) {
			flags.push("Mail");
		}

		if ( flags.length > 2 ) {
			var last = flags.pop();
			alias += flags.join(', ');
			alias += " and " + last;
		} else {
			alias += flags.join(' and ');
		}

		alias += " host";
		return alias;

	}

	var form = WizardInstance.body.find('form'),
		table = $('<table>');

	table.append( $('<colgroup>').html(
		'<col style="width: 50%" /><col style="width: 50%" /><col style="width: 50%" />'
	) );

	table.append( $('<thead>').html( 
		'<th colspan="3">' + 'Added objects: ' + '</th>'
	) );

	for ( var i = 0; i < Wizard.all.length; i++ ) {

		var servicenames = [];

		for ( var sc = 0; sc < Wizard.all[i].services.length; sc++ ) {
			servicenames.push( Wizard.all[i].services[sc].description );
		}

		servicenames = servicenames.join(', ');
		var alias = generate_alias( Wizard.all[i].host.hostgroups, servicenames, Wizard.all[i].host.host_name);

		table.append(
			$('<tr>').html(
				'<td>' + Wizard.all[i].host.host_name + '</td>' + 
				'<td>' + Wizard.all[i].host.address + '</td>' + 
				'<td>' + 
					'<img src="/ninja/application/views/icons/16x16/service-details.png" help="' + 
						"Service-sets: " + Wizard.all[i].host.hostgroups.join(', ') + ". " + 
						"Services: " + servicenames + 
					'" />' + 
					'<img src="/ninja/application/views/icons/16x16/host-notes.png" help="' + 
						'Operating-system: ' + Wizard.all[i].os + '<br />' +
						'Poller: ' + Wizard.all[i].poller + '<br />' + 
						'Alias: ' + alias +
					'" />' + 
					'<img data-edit="' + i +'" help="Edit entry" src="/ninja/application/views/icons/16x16/edit.png">' +
					'<img data-delete="' + i +'" help="Delete entry" src="/ninja/application/views/icons/16x16/disable-active-checks.png">' +
				'</td>'
			)
		);

	}

	var deletes = table.find('img[data-delete]').click(
		function ( e ) {

			var index = parseInt( $(e.target).attr('data-delete') , 10 );
			if ( confirm("Are you sure you want to remove the host " + Wizard.all[ index ].host.host_name + " from being setup?") ) {
				Wizard.all.splice( index, 1 );
				if ( Wizard.all.length == 0 ) {
					WizardInstance.continue(null, true);
					return;
				}
				Wizard.list();
			}

		}
	);

	WizardInstance.button( 'save', 'Write all objects into the configuration and save.', 'savebutton' );

	form.html("");
	form.append( table );
	WizardInstance.adjust();

};