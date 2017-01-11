<?php

Event::add('ninja.quicklinks', function (){

	$linkprovider = LinkProvider::factory();
	$quicklinks = &Event::$data;

	if (!is_array($quicklinks)) {
		return;
	}

	$quicklinks['internal'][] = new Quicklink_Model(
		'shield-pending',
		$linkprovider->get_url('listview', null, array(
			'q' => '[hosts] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0'
		)),
		array(
			'id' => 'uh_service_problems',
			'title' => 'Unhandled Service Problems'
		)
	);

	$quicklinks['internal'][] = new Quicklink_Model(
		'shield-pending',
		$linkprovider->get_url('listview', null, array(
			'q' => '[services] state != 0 and acknowledged = 0 and scheduled_downtime_depth = 0 and host.scheduled_downtime_depth = 0'
		)),
		array(
			'id' => 'uh_service_problems',
			'title' => 'Unhandled Service Problems'
		)
	);

});
