<?php
require_once (__DIR__ . '/User.php');
require_once (__DIR__ . '/Authorization.php');

class op5User_AlwaysAuth extends op5User {

	public function __construct() {
		$this->username = 'superuser';
		$this->realname = 'Super User';

		$categories = op5Authorization::get_all_auth_levels();
		$found = false;
		foreach ($categories as $levels) {
			foreach($levels as $auth_point => $value) {
				$this->auth_data[$auth_point] = true;
			}
		}
	}

	/**
	 * Sets authorization point for current user
	 *
	 * @param $type string
	 * @param $value boolean
	 * @return void
	 *
	 */
	public function set_authorized_for($type, $value) {
		if (!isset($this->auth_data[$type]))
			throw new Exception(
					"Unknown authorization type $type: are you sure everything was spelled correctly?");
		$this->auth_data[$type] = $value;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return boolean always false (never logged in)
	 */
	public function logged_in() {
		return true;
	}
}
