<?php

Event::add('ninja.user.settings', function () {
	$usercontroller = Event::$data;
	$usercontroller->_add("config", "Gravatar", "config.gravatar", "string");
});
