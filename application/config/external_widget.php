<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
* 	Set up a specific user to be authorized for
* 	viewing a ninja widget on an external web.
* 	Please note that this user will ONLY be able to
* 	view the external widget. Set this user by editing the line at
* 	the bottom of this script to
*
* 	$config['username'] = 'your-user-here';
*
* 	(yes, the quotes are necessary :))
*
* 	Setting the 'groups' property to a non-false value configures
* 	the user's rights - the defalt goes by the user's contacts, but having
*
* 	$config['groups'] = array('op5_readonly');
*
* 	quickly gives the external user right to see everything.
*
* 	A simple iframe on the external page like the following
* 	should work if a valid user is configured:
*
* 	<iframe
* 		src="http://<SERVER_NAME>/ninja/index.php/external_widget/<WIDGET_REFERENCE>"
* 		height="500px" frameborder=0 width="600px" scrolling='no'></iframe>
*/
$config['username'] = false;
$config['groups'] = false;

$config['widgets'] = array();

/**
 * Define the widgets you want to expose, with config below.
 *
 * To expose a list view, given a filter:
 *
 * $config['widgets']['service_problems'] = array(
 *     'name' => 'listview',
 *     'friendly_name' => 'Currently unacknowledged service problems',
 *     'setting' => array(
 *         'query' => '[services] state != 0 and scheduled_downtime_depth != 0 and host.scheduled_downtime_depth != 0 and acknowledged != 0',
 *         'columns' => 'default',
 *         'limit' => 50,
 *         'order' => ''
 *     )
 * );
 *
 * The widget will be available at:
 * https://my_monitor_host/monitor/index.php/external_widget/service_problems
 */
