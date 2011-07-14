<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
* 	Set up a specific user to be authorized for
* 	viewing a ninja widget on an external web.
* 	Please note that this user will ONLY be able to
* 	view the external widget.
*
* 	Specifying the 'widget_name' below is not necessary
* 	but will make it possible to call the external_widget
* 	controller without specifying what widget to use.
*
* 	A simple iframe on the external pgae like the following
* 	should work if a valid user is configured:
*
* 	<iframe
* 		src="http://<SERVER_NAME>/ninja/index.php/external_widget/show_widget/<OPTIONAL WIDGET_NAME>"
* 		height="500px" frameborder=0 width="600px" scrolling='no'></iframe>
*/
$config['widget_name'] = 'netw_health';
$config['username'] = false;