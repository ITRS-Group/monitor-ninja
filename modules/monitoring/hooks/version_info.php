<?php

Event::add('ninja.version.info', function () {

	$status = Current_status_Model::instance()->program_status();
	Event::$data->set('Version', $status->program_version);

});