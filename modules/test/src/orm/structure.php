<?php
$tables = array (
	'test_class_a' =>
	array (
		'class' => 'TestClassA',
		'table' => 'test_class_a',
		'description' => 'This is a class only used and available during testing',
		'source' => 'MySQL',
		'writable' => true,
		'key' => array('string'),

		'structure' => array(
			'string' => 'string',
			'float' => 'float',
			'int' => 'int',
			'bool' => 'bool',
			'list' => 'list',
			'dict' => 'dict',
			'time' => 'time',
			'password' => 'password',
			'relation' => array('relation', 'TestClassB'),
			'set' => array('set', 'TestClassB'),
			'flags' => array('flags', array("a", "b", "c")),
		)
	),
	'test_class_b' =>
	array (
		'class' => 'TestClassB',
		'table' => 'test_class_b',
		'description' => 'This is a class only used and available during testing',

		'source' => 'MySQL',
		'writable' => true,
		'key' => array('string'),

		'structure' => array(
			'string' => 'string'
		)
	)
);
