
<div class="jq-notify-zone"></div>

<?php

	/* Check to see if there are any global notifications to display */

	$notifications = array();

	$note_config = Ninja_setting_Model::fetch_page_setting( "notifications", "notifications_facility" );
	if ( $note_config ) {
		$note_config = $note_config->setting;
	} else {
		$note_config = "{}";
	}

	echo "<script>" .
			"$.notify.sessionid = '" . sha1( session_id() ) . "';" .
			"$.notify.configured = " . $note_config . ";" .
		"</script>";

	if ( isset( $global_notifications ) && is_array( $global_notifications ) && count( $global_notifications ) >= 1 ) {

		foreach ( $global_notifications as $note )
			$notifications[] = $note[0];

		if ( count( $notifications ) > 1 ) {
			$last = array_pop( $notifications );
			$message = implode( ", ", $notifications ) . " and " . $last;
		} else {
			$message = implode( ", ", $notifications );
		}

		$message .= ". You can enter the process information page to enable/disable process settings.";

	}

	/* Render PHP added notifications if logged in! */

	if ( Auth::instance()->logged_in() )
		notify::render();