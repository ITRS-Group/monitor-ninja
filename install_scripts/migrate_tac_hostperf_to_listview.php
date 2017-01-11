#!/usr/bin/env php
<?php

if(PHP_SAPI !== 'cli') {
	die("This script should never be run from a browser, execute it from commmand-line instead.");
}

/**
 * Used to migrate tac_hostperf widgets to listview widgets so that
 * tac_hostperf can be removed.
 */
if(!class_exists('Kohana')) {
	define('SKIP_KOHANA', true);
	require_once(realpath(__DIR__.'/../index.php'));
}

$user = new User_AlwaysAuth_Model();
op5auth::instance()->force_user($user);
$widgets = Dashboard_WidgetPool_Model::all()->reduce_by('name', 'tac_hostperf', '=');
$first_host = HostPool_Model::all()->one()->get_name();

foreach ($widgets as $widget) {

	$widget->set_name('listview');
	$new_settings = array(
		"columns" => "state,description,status_information",
		"limit" => "100"
	);

	$old_settings = $widget->get_setting();
	$host_name = isset($old_settings['host_name']) ? $old_settings['host_name'] : $first_host;
	$new_settings['title'] = 'Host performance - ' . $host_name;

	$hidden = isset($old_settings['hidden']) ? $old_settings['hidden'] : '';
	$hidden = explode(";", $hidden);

	$query_set = ServicePool_Model::all();
	$query_set = $query_set->reduce_by('host.name', $host_name, '=');

	if (count($hidden) > 0) {
		foreach ($hidden as $description) {
			$query_set = $query_set->reduce_by('description', $description, '!=');
		}
	}

	$new_settings['query'] = $query_set->get_query();
	$widget->set_setting($new_settings);
	$widget->save();

}

