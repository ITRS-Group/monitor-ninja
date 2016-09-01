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

	    return (
			$login_dashboard && ($login_dashboard->get_setting() == $dashboard->get_id())
		);

	}

}
