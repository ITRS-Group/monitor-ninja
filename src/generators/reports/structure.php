<?php

$tables = array (
	'saved_reports' =>
	array(
		'class' => 'SavedReport',
		'source' => 'SQL',
		'table' => 'saved_reports',
		'key' => array('id'),
		'default_sort' => array('report_name'),
		'structure' => array(
			'id' => 'int',
			'type' => 'string',
			'report_name' => 'string',
			'created_by' => 'string',
			'created_at' => 'time',
			'updated_by' => 'string',
			'updated_at' => 'time'
		),
	),
);