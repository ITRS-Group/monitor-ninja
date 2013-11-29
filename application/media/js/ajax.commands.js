
$("document").ready( function () {

	$("button.command-button").click( function () {

		var btn = $( this ),
			command = btn.attr( "data-command" ),
			href = _site_domain + _index_page + "/ajax/command/",
			toggle = false,
			dialog;

		if ( command.match( /ENABLE/ ) ) {
			toggle = command.replace( "ENABLE", "DISABLE" );
		} else if ( command.match( /DISABLE/ ) ) {
			toggle = command.replace( "DISABLE", "ENABLE" );
		}

		if ( command.match( /START/ ) ) {
			toggle = command.replace( "START", "STOP" );
		} else if ( command.match( /STOP/ ) ) {
			toggle = command.replace( "STOP", "START" );
		}

		btn.attr( "disabled", true );

		$.ajax({
			url : href,
			type: "POST",
			data: {
				"method": "submit",
				"command": command
			},
			success : function( data ) {

				data = JSON.parse( data );

				var msg = "<strong>" + data.brief + "</strong><br />" + data.description;

				dialog = $.notify( msg, {
					"sticky": true,
					"remove": function () {
						btn.removeAttr( "disabled" );
					},
					"buttons": {

						"Submit command!": function () {

							dialog.remove();

							$.ajax({
								url : href,
								type: "POST",
								data: {
									"method": "commit",
									"command": command
								},
								success : function( data ) {

									var title = btn.html();

									$.notify( "Command has been executed!" );
									data = JSON.parse( data );

									if ( typeof( data.state ) != "undefined" ) {
										if ( data.state === 0 ) {
											title = title.replace( /Enable/, "Disable" );
											title = title.replace( /Start/, "Stop" );
										} else {
											title = title.replace( /Stop/, "Start" );
											title = title.replace( /Disable/, "Enable" );
										}

										btn.html( title );
										btn.attr( "data-state", (data.state == 1) ? 0 : 1 );

										if ( toggle )
											btn.attr( "data-command", toggle );
										toggle = command;

									}

									btn.removeAttr( "disabled" );

								}
							});
						}

					}
				} );

			}
		});

	} );

} );