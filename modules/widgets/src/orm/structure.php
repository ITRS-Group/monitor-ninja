<?php
$tables = array(
	'ninja_widgets' => array(
		'class' => 'Ninja_Widget',
		'source' => 'MySQL',
		'table' => 'ninja_widgets',
		'writable' => true,
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
	)
);