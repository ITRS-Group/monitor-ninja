<?php

$manifest = array_merge_recursive($manifest, array(
	"usergroups" => array(
		array(
			'display_column' => 'name',
			'query' => '[usergroups] groupname ~~ "%s"'
		)
	),
	"users" => array(
		array(
			'display_column' => 'name',
			'query' => '[users] username ~~ "%s"'
		)
	),
));
