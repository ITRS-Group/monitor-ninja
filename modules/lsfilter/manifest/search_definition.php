<?php
/**
 *	Search definition manifest
 *
 *	Format:
 *
 *		table => array(
 *			query => What LSFilter query to use when interpolated with the search query"
 *			columns => What columns to fetch from the ORM
 *			autocomplete => What href should the autocomplete link to for a result
 *											of this type, interpolating values from the object.
 *											(Only values set in columns are accessible)
 *		)
 */
$manifest = array_merge_recursive($manifest, array(

	"hosts" => array(
		"query" => 'name ~~ "{query}"',
		"columns" => array('name', 'key'),
		"autocomplete" => '/extinfo/details?host={name}',
	),

	"services" => array(
		"query" => 'description ~~ "{query}" or host.name ~ "{query}"',
		"columns" => array('host.name', 'description', 'key'),
		"autocomplete" => '/extinfo/details?host={host.name}&service={description}',
	),

	"hostgroups" => array(
		"query" => 'name ~~ "{query}"',
		"columns" => array('name', 'key'),
		"autocomplete" => '/extinfo/details?hostgroup={name}',
	),

	"servicegroups" => array(
		"query" => 'name ~~ "{query}"',
		"columns" => array('name', 'key'),
		"autocomplete" => '/extinfo/details?servicegroup={name}',
	)

));