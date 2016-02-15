<?php

Event::add('ninja.show_login', function () {
	if (setup::is_available()) {
		$linkprovider = LinkProvider::factory();
		url::redirect($linkprovider->get_url('setup'));
	}
});
