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
	$db_menu->set ( "New dashboard", 'tac/new_dashboard_dialog', $idx++, 'icon-16 x16-sign-add', array(
			'class' => "menuitem_dashboard_option" /* Popup as fancybox */
	));
});

Event::add ( 'system.post_controller', function() {
	Kohana::$instance->template->js[] = 'modules/widgets/views/js/tac.js';
});
