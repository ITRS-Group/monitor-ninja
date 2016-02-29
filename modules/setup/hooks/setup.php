<?php

Event::add('ninja.show_login', function () {
        if (setup::is_available()) {
                $linkprovider = LinkProvider::factory();
                url::redirect($linkprovider->get_url('setup'));
        }
});


Event::add('ninja.setup', function () {
	$data = Event::$data;
	Event::run('ninja.setup.user', $data);
	Event::run('ninja.setup.contact', $data);
});

