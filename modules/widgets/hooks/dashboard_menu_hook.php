<?php

Event::add ( 'ninja.menu.setup', function () {
	$dashboards = DashboardPool_Model::all();
	$menu = Event::$data;
	/* @var $menu Menu_Model */

	$db_menu = $menu->set('Dashboards', null, 1, 'icon-16 x16-tac', array('style' => 'padding-top: 8px'))->get('Dashboards');
	$idx = 0;

	$login_dashboard = SettingPool_Model::all()
		->reduce_by('username', op5auth::instance()->get_user()->get_username(), '=')
		->reduce_by('type', 'login_dashboard', '=')
		->one();

	foreach($dashboards->it(array('name'), array('name')) as $dashboard) {
		$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key(), $idx++)->get($dashboard->get_id());
		$item->set_label($dashboard->get_name());
		if ($login_dashboard && $login_dashboard->get_setting() == $dashboard->get_id()) {
			 $item->set_icon('icon-16 x16-tac');
		}
	}

	$db_menu->set("New dashboard", 'tac/new_dashboard_dialog', $idx++, 'icon-16 x16-sign-add', array(
		'class' => "menuitem_dashboard_option" /* Popup as fancybox */
	));

});
