<?php
require_once (__DIR__ . '/AuthDriver.php');

/**
 * User authentication and authorization library.
 */
class op5AuthDriver_Apache extends op5AuthDriver {

	protected static $metadata = array (
		'require_user_configuration' => true,
		'require_user_password_configuration' => false,
		'login_screen_dropdown' => false
	);

	private $users = null;
	private function fetch_users() {
		if ($this->users) return;
		$this->users = UserPool_Model::all();
	}

	private function resolve_groups_for_user($username) {

		$this->fetch_users();
		$groups = array();

		$user = $this->users->reduce_by('username', $username, '=')->one();
		if ($user) {
			if (in_array($this->module->get_modulename(), $user->get_modules(), true)) {
				$groups = $user->get_groups();
			}
		}

		/*
		 * Make all apache auth users members of this group, to grant privileges
		 * to all those users This is for backward compatibility. Use
		 * meta_driver_apache or meta_all_users instead
		 */
		$groups[] = 'apache_auth_user';
		return $groups;
	}

	public function get_user_count () {
		return count(UserPool_Model::all());
	}

	/**
	 * Attempt to log in a user by static configuration, or external
	 * infromation.
	 *
	 * Useful for example for HTTP-auth.
	 *
	 * @return User_Model User object, or false
	 */
	public function auto_login() {
		/*
		 * Every page contains validation information, so get_user
		 * authenticates...
		 */
		return $this->doAuth();
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

		$groups['apache_auth_user'] = 'apache_auth_user';
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
	 * Does authentication.
	 * This isn't done in the login function during apache auth,
	 * because authentication is before the login screen, and handled every page
	 * load.
	 *
	 * TODO: Cache user credentials and authorization if username doesn't
	 * change.
	 *
	 * @return mixed
	 */
	private function doAuth() {
		/*
		 * We let apache handle the authentication, so only username is relevant
		 */
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			return false;
		}

		$username = $_SERVER['PHP_AUTH_USER'];
		return new User_Model(
			array (
				'username' => $username,
				'groups' => $this->resolve_groups_for_user($username),
				'realname' => $username, /* We have no clue about realname, so call him/her their username */
				'email' => ''
			)
		);
	}

	/**
	 * Given a username, return a list of it's groups.
	 * Useful when giving permissions to a user.
	 *
	 * @param $username string
	 *        	User to search for
	 * @return array A list of groups, or false if not possible
	 */
	public function groups_for_user($username) {
		return $this->resolve_groups_for_user($username);
	}
}
