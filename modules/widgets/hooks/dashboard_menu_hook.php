<?php

Event::add ( 'ninja.menu.setup', function () {
	$menu = Event::$data;
	/* @var $menu Menu_Model */

	$db_menu = $menu->set('Dashboards', null, 1, 'icon-16 x16-tac', array('style' => 'padding-top: 8px'))->get('Dashboards');
	$idx = 0;
	$shared_dashboard_position = 10;
	$shared_dashboard_idx = 0;
	$username = op5auth::instance()->get_user()->get_username();

	/*
	 * my-own dashboard
	 * '$idx' value start from '0'
	*/
	$my_dashboards = DashboardPool_Model::all()->reduce_by('username', $username, '=');
	foreach($my_dashboards->it(array('name'), array('name')) as $dashboard) {
		//'Show more' and break the loop if more than 5 my-own dashboard's
		if($idx >= 5){
			//TODO: Dashboard's overview Link
			$db_menu->set("Show more", null, $idx++);
			break;
		}
		$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key(), $idx++)->get($dashboard->get_id());
		$item->set_label($dashboard->get_name());
	}

	/*
	 * shared dashboard
	 * '$shared_dashboard_idx' value start from '0'
	 * '$shared_dashboard_position' value start from '10'
	*/
	$shared_dashboards = DashboardPool_Model::all()->reduce_by('username', $username, '!=');
	//Check shared dashboard's count
	if($shared_dashboards->count() > 0) {
		$db_menu->set("SHARED WITH YOU:", null, $shared_dashboard_position++);
		foreach($shared_dashboards->it(array('name'), array('name')) as $dashboard) {
			//'Show more' and break the loop if more than 5 shared dashboard's
			if($shared_dashboard_idx >= 5){
				//TODO: Dashboard's overview Link
				$db_menu->set("Show more ", null, $shared_dashboard_position++);
				break;
			}
			$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key(), $shared_dashboard_position++)->get($dashboard->get_id());
			$item->set_label($dashboard->get_name());
			$shared_dashboard_idx++;
		}
	}

	$db_menu->set("New dashboard", 'tac/new_dashboard_dialog', $shared_dashboard_position++, 'icon-16 x16-sign-add', array(
		'class' => "menuitem_dashboard_option" /* Popup as fancybox */
	));

	//TODO: Dashboard's overview Link
	$db_menu->set("Dashboard's overview", null, $shared_dashboard_position++);
});
