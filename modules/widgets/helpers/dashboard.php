<?php

/**
 * dashboard helper class
 */
class dashboard {

	/**
	 * Returns the default dashboard for the currently authenticated user, be
	 * it through the login_dashboard setting or just an accessable one.
	 *
	 * @return Dashboard_Model Returns null/false if no dashboard is found
	 */
	public static function get_default_dashboard () {

		$dashboard = dashboard::get_login_dashboard();

		/* If login dashboard isn't found, attempt to return an accessable dashboard */
		if (!$dashboard) $dashboard = DashboardPool_Model::all()->one();
		return $dashboard;

	}

	/**
	 * Returns a Dashboard_Model if the user has set an explicit login
	 * dashboard that exists
	 *
	 * @return Dashboard_Model or null/false if no dashboard is set or exists
	 */
	public static function get_login_dashboard () {

		/* Get login dashboard */
		$user = op5auth::instance()->get_user();
		$login_dashboard = SettingPool_Model::all()
			->reduce_by('username', $user->get_username(), '=')
			->reduce_by('type', 'login_dashboard', '=')
			->one();

		if ($login_dashboard) {
			return DashboardPool_Model::fetch_by_key($login_dashboard->get_setting());
		} else return null;

	}

	/**
	 * Whether to show "Set as login dashboard" for the given Dashboard
	 *
	 * @param $dashboard Dashboard_Model
	 * @return bool
	 */
	public static function is_login_dashboard (Dashboard_Model $dashboard) {
		$login_dashboard = SettingPool_Model::all()
			->reduce_by('username', op5auth::instance()->get_user()->get_username(), '=')
			->reduce_by('type', 'login_dashboard', '=')
			->one();

		return $login_dashboard &&
			$login_dashboard->get_setting() == $dashboard->get_id();
	}

	/**
	 * Objects are always passed by reference, which means that you could
	 * modify away on $menu and still have it reflected elsewhere!
	 *
	 * @param $menu Menu_Model
	 */
	public static function set_dashboard_menu_based_on_logged_in_user(Menu_Model $menu) {
		$db_menu = $menu->set('Dashboards', null, 1, 'icon-16 x16-tac', array('style' => 'padding-top: 8px'))->get('Dashboards');
		$username = op5auth::instance()->get_user()->get_username();

		$my_dashboards = DashboardPool_Model::all()->reduce_by('username', $username, '=');
		foreach($my_dashboards->it(array('name'), array('name')) as $index => $dashboard) {
			//'Show more' and break the loop if more than 5 my-own dashboard's
			if($index >= 5){
				$db_menu->set("Show more", listview::querylink($my_dashboards->get_query()))->get('Show more')->set_label('Show more...');
				break;
			}
			$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key())->get($dashboard->get_id());
			$item->set_label($dashboard->get_name());
		}

		if(op5mayi::instance()->run("monitor.system.dashboards.shared:read")) {
			// All ORM queries are implicitly filtered with the currently
			// logged in user, so the following set does not necessarily
			// include "select * from dashboards where username != 'me'",
			// even though it looks like it.
			$shared_dashboards = DashboardPool_Model::all()
				->reduce_by('username', $username, '!=');
			// Check shared dashboard's count
			if($shared_dashboards->count() > 0) {
				$db_menu->set("shared", null);
				$separator = $db_menu->get('shared');
				$separator->set_separator('Shared with you:');
				foreach($shared_dashboards->it(array('name'), array('name')) as $index => $dashboard) {
					if($index >= 5){
						$db_menu->set("Show more shared", listview::querylink($shared_dashboards->get_query()))->get('Show more shared')->set_label('Show more...');
						break;
					}
					$item = $db_menu->set($dashboard->get_id(), 'tac/index/'.$dashboard->get_key())->get($dashboard->get_id());
					$item->set_label($dashboard->get_name());
				}
			}
		}

		$db_menu->set("actions");
		$separator = $db_menu->get('actions');
		$separator->set_separator();

		$db_menu->set("All Dashboards", listview::querylink('[dashboards] all'));
		$db_menu->set("New dashboard", 'tac/new_dashboard', null, false, array(
			'class' => "menuitem_dashboard_option" /* Popup as fancybox */
		));
	}
}
