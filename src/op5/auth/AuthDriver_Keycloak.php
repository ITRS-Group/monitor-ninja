<?php
require_once (__DIR__ . '/AuthDriver.php');

/**
 * User authentication and authorization library.
 */
class op5AuthDriver_Keycloak extends op5AuthDriver {

	protected static $metadata = array (
		'require_user_configuration' => false,
		'require_user_password_configuration' => false,
		'login_screen_dropdown' => false
	);

	private $users = null;

	/**
	 * Log in an already authenticated Keycloak user
	 *
	 * @param
	 *        	string username to log in
	 * @param
	 *        	string password not used
	 * @return User_Model|null
	 */
	public function login($username, $password) {
		$this->fetch_users();
		$user = $this->users->reduce_by('username', $username, '=')->one();
		// TODO: Check that user has keycloak as auth module
		return $user;
	}

	private function fetch_users() {
		if ($this->users) return;
		$this->users = UserPool_Model::all();
	}

	/**
	 * Given a list of groups, return an associative array with groups as keys
	 * and a boolean
	 * if group is available in the backend.
	 * If it is unknown if the user is available, the field
	 * is unset.
	 *
	 * If driver supports multiple backends, the extra auth_method can be set to
	 * the backend.
	 * Otherwise, a superset is should given of all backends
	 *
	 * @param $grouplist List
	 *        	of groups to check
	 * @return Associative array of the groups in $grouplist as keys, boolean as
	 *         values
	 */
	public function groups_available(array $grouplist) {
		$this->fetch_users();

		$groups = array();
		foreach ($this->users as $user) {
			foreach ($user->get_groups() as $group) {
				$groups[$group] = $group;
			}
		}

		$result = array();

		foreach ($grouplist as $group) {
			if (substr($group, 0, 5) == 'user_') {
				/* Unknown if user exists */
			} else {
				$result[$group] = isset($groups[$group]);
			}
		}
		return $result;
	}


	/**
	 * Given a username, return a list of it's groups.
	 * Useful when giving permissions to a user.
	 *
	 * @param $username string
	 *        	User to search for
	 * @return array A list of groups
	 */
	public function groups_for_user($username) {
		return $this->resolve_groups_for_user($username);
	}
}
