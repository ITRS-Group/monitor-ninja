<?php

Event::add ( 'ninja.menu.setup', function () {
	$menu = Event::$data;
	/* @var $menu Menu_Model */

	dashboard::set_dashboard_menu_based_on_logged_in_user($menu);
});
