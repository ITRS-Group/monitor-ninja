<?php
$tables = array(
	'ninja_widgets' => array(
		'class' => 'Ninja_Widget',
		'source' => 'MySQL',
		'table' => 'ninja_widgets',
		'writable' => true,
		'object_custom_parent' => 'Widget',
		'key' => array(
			'id'
		),
		'structure' => array(
			'id' => 'int',
			'username' => 'string',
			'page' => 'string',
			'name' => 'string',
			'friendly_name' => 'string',
			'setting' => 'string',
			'instance_id' => 'int'
		)
	),
	'dashboards' => array(
		'class' => 'Dashboard',
		'source' => 'MySQL',
		'table' => 'dashboards',
		'writable' => true,
		'key' => array(
			'id'
		),
		'structure' => array(
			'id' => 'int',
			'name' => 'string',
			'username' => 'string', // This should change in the future
			'layout' => 'string'
		)
	),
	'dashboard_widgets' => array(
		'class' => 'Dashboard_Widget',
		'source' => 'MySQL',
		'table' => 'dashboard_widgets',
		'writable' => true,
		'object_custom_parent' => 'Widget',
		'key' => array(
			'id'
		),
		'relations' => array(
			array(array('dashboard_id'), 'dashboards', 'dashboard'),
		),
		'structure' => array(
			'id' => 'int',
			'dashboard_id' => 'int',
			'dashboard' => array('Dashboard', 'dashboard.'),
			'name' => 'string',
			'setting' => 'string',
			'position' => 'string'
		)
	)
);
