<?php

require_once('op5/auth/User.php');

class op5User_AlwaysAuth extends op5User {
	private $authorized_for = array();

	public function __construct() {
		$this->username = 'superuser';
		$this->realname = 'Super User';
	}

	/**
	 * Sets authorization point for current user
	 *
	 * @param $type string
	 * @param $value boolean
	 * @return void
	 **/
	public function set_authorized_for($type, $value) {
		$this->authorized_for[$type] = $value;
	}

	/**
	 * Determines whether user has supplied authorization point or not
	 *
	* @param $auth_point string 		authorization point
	* @return boolean
	*/
	public function authorized_for($auth_point)
	{
		if (isset($this->authorized_for[$auth_point]))
			return $this->authorized_for[$auth_point];
		else
			return true;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return  boolean   always false (never logged in)
	 */
	public function logged_in()
	{
		return true;
	}
}
