<?php

/*
 * This defined which columns is visible in each table, as a default setting for
 * all users in the system.
*/
if( !isset( $config['columns'] ) )
	$config['columns'] = array();

/*
 * The following configuration is what new users see as default for column
 * configuration.
 *
 * To update and propagate business-specific custom column configuration, use
 * the "default" section below
 */
$config['columns']['hosts'] = 'default';
$config['columns']['services'] = 'default';
$config['columns']['hostgroups'] = 'default';
$config['columns']['servicegroups'] = 'default';
$config['columns']['comments'] = 'default';
$config['columns']['downtimes'] = 'default';
$config['columns']['contacts'] = 'default';
$config['columns']['notifications'] = 'default';
$config['columns']['saved_filters'] = 'default';

/*
 * The default section reprecents which columns should be included when using
 * the "default" keyword in settings.
 *
 * Custom columns can be added here, without the need for a user to see the
 * definition in the My Account page
 */
if( !isset( $config['default'] ) )
	$config['default'] = array();
if( !isset( $config['default']['columns'] ) )
	$config['default']['columns'] = array();

$config['default']['columns']['hosts'] = 'all';
$config['default']['columns']['services'] = 'all';
$config['default']['columns']['hostgroups'] = 'all';
$config['default']['columns']['servicegroups'] = 'all';
$config['default']['columns']['comments'] = 'all';
$config['default']['columns']['downtimes'] = 'all';
$config['default']['columns']['contacts'] = 'all';
$config['default']['columns']['notifications'] = 'all';
$config['default']['columns']['saved_filters'] = 'all';