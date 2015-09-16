<?php
$manifest = array(

	"queries" => array(
		"hosts" => 'name ~~ "{query}"',
		"services" => 'description ~~ "{query}" or host.name ~ "{query}"',
		"hostgroups" => 'name ~~ "{query}"',
		"servicegroups" => 'name ~~ "{query}"'
	),

	"columns" => array(
		"hosts" => array('name', 'key'),
		"services" => array('host.name', 'description', 'key'),
		"hostgroups" => array('name', 'key'),
		"servicegroups" => array('name', 'key')
	),

	"autocomplete" => array(
		"hosts" => '/extinfo/details?host={name}',
		"services" => '/extinfo/details?host={host.name}&service={description}',
		"hostgroups" => '/extinfo/details?hostgroup={name}',
		"servicegroups" => '/extinfo/details?servicegroup={name}'
	)

);