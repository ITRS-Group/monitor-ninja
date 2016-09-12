<?php
/**
 * When we want to force a user to have all rights, we use an instance of this
 * "super user" model. It is practical for tests, but also CLI scripts which
 * usually have no concept of a user executing it.
 */
class User_AlwaysAuth_Model extends User_Model {

	/**
	 * Constructs a new AlwaysAuth user
	 */
	public function __construct () {
		$this->set_username('superuser');
		$this->set_realname('Super User');

		$categories = op5Authorization::get_all_auth_levels();
		$rights = array();
		foreach ($categories as $levels) {
			foreach($levels as $auth_point => $value)
				$rights[$auth_point] = true;
		}
		$this->set_auth_data($rights);
	}

	/**
	 * If you want to start out with an "auth me for everything" but
	 * gradually deny some rights, this is the method you are looking for.
	 *
	 * @throws Exception
	 * @param $type string
	 * @param $value bool
	 */
	public function set_authorized_for ($type, $value) {
		$auth_data = $this->get_auth_data();
		if (!isset($auth_data[$type]))
			throw new Exception(
				"Unknown authorization type $type: are you sure everything was spelled correctly?");
		$auth_data[$type] = $value;
		$this->set_auth_data($auth_data);
	}

	/**
	 * Returns true if logged in
	 *
	 * @return boolean always true (normal users are logged in, notauth overrides)
	 */
	public function logged_in() {
		return true;
	}

}
