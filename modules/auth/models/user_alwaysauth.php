<?php
/**
 * User model
 *
 * @todo: documentation
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
	 * Updates an authorization point
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
         * @return boolean always true (normal users are logged in, notauth
         *         overrides)
         */
        public function logged_in() {
                return true;
        }

}
