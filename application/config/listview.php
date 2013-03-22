<?php

/*
 * This defined which columns is visible in each table, as a default setting for all users in the system.
*/
if( !isset( $config['columns'] ) )
	$config['columns'] = array();

$config['columns']['hosts'] = 'all';
$config['columns']['services'] = 'all';
$config['columns']['hostgroups'] = 'all';
$config['columns']['servicegroups'] = 'all';
$config['columns']['comments'] = 'all';
$config['columns']['downtimes'] = 'all';
$config['columns']['contacts'] = 'all';
$config['columns']['notifications'] = 'all';
$config['columns']['saved_filters'] = 'all';