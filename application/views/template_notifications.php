
<div class="jq-notify-zone"></div>

<?php

	$note_config = Ninja_setting_Model::fetch_page_setting(
		"notifications", "notifications_facility"
	);

	if ($note_config) $note_config = $note_config->setting;
	else $note_config = "{}";

	echo "<script type=\"text/javascript\">" .
		"$.notify.sessionid = '" . sha1(session_id()) . "';" .
		"$.notify.configured = " . $note_config . ";" .
	"</script>";

	/* Only render notifications if logged in */
	if (Auth::instance()->logged_in()) {
		foreach ($this->notices as $notice) {
			printf(
				"<script>Notify.message('%s', {type: '%s', nag: true});</script>",
				addcslashes($notice->get_message(), "'"),
				$notice->get_typename()
			);
		}
	}

