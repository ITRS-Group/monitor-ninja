<?php

Event::add('ninja.version.info', function () {

	$osversion = shell_exec("cat /etc/redhat-release");
	Event::$data->set('OS Version', $osversion);

});
