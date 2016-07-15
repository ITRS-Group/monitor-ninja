<?php

$manifest = array_merge_recursive($manifest, array(
	"contacts" => array(
		array(
			'display_column' => 'name',
			'query' => '[contacts] name ~~ "%s"'
		)
	),
	"hosts" => array(
		array(
			'display_column' => 'name',
			'query' => '[hosts] name ~~ "%s"'
		)
	),
	"services" => array(
		array(
			'display_column' => 'description',
			'query' => '[services] description ~~ "%s"'
		)
	),
));
