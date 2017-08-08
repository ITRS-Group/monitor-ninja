<?php

$tables = array (
	'discovery_hosts' =>
	array(
		'class' => 'Discovery_Hosts',
		'source' => 'YAML',
		'table' => 'discovery_hosts',
		'writable' => true,
		'key' => array('id'),
		'default_sort' => array('type asc'),
		'structure' => array(
			'id' => 'int',
			'name' => 'string',
			'ip' => 'string',
			'parent' => 'string',
			'protocol' => 'string'
		),
	)
);
