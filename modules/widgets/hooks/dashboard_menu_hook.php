<?php

Event::add ( 'ninja.menu.setup', function () {
	$menu = Event::$data;
	/* @var $menu Menu_Model */

	$db_menu = $menu->set('Dashboards', null, 1, 'icon-16 x16-tac', array('style' => 'padding-top: 8px'))->get('Dashboards');
	$username = op5auth::instance()->get_user()->get_username();

	$my_dashboards = DashboardPool_Model::all()->reduce_by('username', $username, '=');
	foreach($my_dashboards->it(array('name'), array('name')) as $index => $dashboard) {
		//'Show more' and break the loop if more than 5 my-own dashboard's
		if($index >= 5){
			//TODO: Dashboard's overview Link
			$db_menu->set("Show more", listview::querylink('[dashboards] username = "' . $username . '"'));
			break;
		}
		$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key())->get($dashboard->get_id());
		$item->set_label($dashboard->get_name());
	}

	$shared_dashboards = DashboardPool_Model::all()->reduce_by('username', $username, '!=');
	//Check shared dashboard's count
	if($shared_dashboards->count() > 0) {
		$db_menu->set("shared", null);
		$separator = $db_menu->get('shared');
		$separator->set_separator('Shared with you:');
		foreach($shared_dashboards->it(array('name'), array('name')) as $index => $dashboard) {
			if($index >= 5){
				//TODO: Dashboard's overview Link
				$db_menu->set("Show more shared", listview::querylink('[dashboards] username != "' . $username . '"'))->get('Show more shared')->set_label('Show more');
				break;
			}
			$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key())->get($dashboard->get_id());
			$item->set_label($dashboard->get_name());
		}
	}

	$db_menu->set("actions");
	$separator = $db_menu->get('actions');
	$separator->set_separator();

	//TODO: Dashboard's overview Link
	$db_menu->set("Dashboard's overview");
	$db_menu->set("New dashboard", 'tac/new_dashboard_dialog', null, false, array(
		'class' => "menuitem_dashboard_option" /* Popup as fancybox */
	));

});
