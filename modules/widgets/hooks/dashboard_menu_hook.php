<?php

Event::add ( 'ninja.menu.setup', function () {
	$dashboards = DashboardPool_Model::all();
	$menu = Event::$data;
	/* @var $menu Menu_Model */

	$db_menu = $menu->set('Dashboards', null, 1, 'icon-16 x16-tac', array('style' => 'padding-top: 8px'))->get('Dashboards');

	$idx = 0;
	foreach($dashboards->it(array('name'), array('name')) as $dashboard) {
			$db_menu->set ( $dashboard->get_name(), 'tac/index/'.$dashboard->get_key(), $idx++ );
	}
	$db_menu->set ( "New dashboard", 'tac/on_new_dashboard', $idx++, 'icon-16 x16-sign-add' );
});