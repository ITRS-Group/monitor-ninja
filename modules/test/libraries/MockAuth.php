<?php
require_once("op5/auth/Authorization.php");
/**
 * A mock implementation of op5Auth, which defines a hard coded user
 * "mockeduser", which is authorized to do anything.
 */
class MockAuth extends op5auth
{
	/**
	 * Create and return a new MockAuth instance.
	 *
	 * The $config paramater can contain an element "denied_authpoints" which
	 * contains a list of authpoints which this instance will deny on invocation
	 * of authorized_for()
	 *
	 * @param $config array configuration for the new instance
	 * @return MockAuth the constructed instance
	 */
	public function __construct($config) {
		$this->denied_authpoints = array();
		if (array_key_exists('denied_authpoints', $config)) {
			$this->denied_authpoints = $config['denied_authpoints'];
		}
	}
	/**
	 * Returns true if current session has access for a given authorization
	 * point, which is always for this implementation.
	 *
	 * @param $authpoint string
	 * @return boolean true if access
	 */
	public function authorized_for($authpoint) {
		$log = op5log::instance('test');
		$ret = !in_array($authpoint, $this->denied_authpoints, true);
		$log->log('debug', ($ret ? "Authorizing " : "Not authorizing ") . "access to authpoint $authpoint");
		return $ret;
	}

	/**
	 * Returns the currently logged in user, which is a fixture "mockeduser"
	 * for this implementation
	 *
	 * @return  mixed
	 */
	public function get_user() {
		$auth_data = array();
		foreach(op5Authorization::get_all_auth_levels() as $category => $cat_auth_data) {
			foreach($cat_auth_data as $lvl => $desc) {
				$auth_data[$lvl] = true;
			}
		}
		foreach($this->denied_authpoints as $lvl) {
			$auth_data[$lvl] = false;
		}
		$user = new User_Model(array(
			"username" => "mockeduser",
			"realname" => "Mocke D. User",
			"email" => "mockeduser@op5.com",
			"auth_data" => $auth_data
		));
		return $user;
	}
}
