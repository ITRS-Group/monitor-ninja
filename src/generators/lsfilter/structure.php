<?php

$tables = array (
	'saved_filters' =>
	array(
		'class' => 'SavedFilter',
		'source' => 'MySQL',
		'table' => 'ninja_saved_filters',
		'key' => array('id'),
		'default_sort' => array('filter_name asc'),
		'structure' => array(
			'id' => 'int',
			'username' => 'string',
			'filter_name' => 'string',
			'filter_table' => 'string',
			'filter' => 'string',
			'filter_description' => 'string'
			),
		)
);
