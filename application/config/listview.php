<?php

/*
 * This defined which columns is visible in each table, as a default setting for all users in the system.
*/
if( !isset( $config['columns'] ) )
	$config['columns'] = array();

$config['columns']['hosts'] = 'default';
$config['columns']['services'] = 'default';
$config['columns']['hostgroups'] = 'default';
$config['columns']['servicegroups'] = 'default';
$config['columns']['comments'] = 'default';
$config['columns']['downtimes'] = 'default';
$config['columns']['contacts'] = 'default';
$config['columns']['notifications'] = 'default';
$config['columns']['saved_filters'] = 'default';