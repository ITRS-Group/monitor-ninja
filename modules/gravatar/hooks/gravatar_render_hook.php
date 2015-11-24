<?php

Event::add('render.header.profile:after', function () {
	$gravatar = config::get('config.gravatar', '*');
	if ($gravatar) {
		$gravatar_hash = md5($gravatar);
		echo '<div class="profile-image">';
		echo sprintf('<img src="http://www.gravatar.com/avatar/%s?s=32">', $gravatar_hash);
		echo '</div>';
	}

});
