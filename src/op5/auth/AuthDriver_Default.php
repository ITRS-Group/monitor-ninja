<?php
require_once (__DIR__ . '/AuthDriver.php');
require_once (__DIR__ . '/../config.php');

/**
 * User authentication and authorization library.
 *
 * @package Auth
 * @author
 *
 * @copyright
 *
 * @license
 *
 */
class op5AuthDriver_Default extends op5AuthDriver {

	protected static $metadata = array (
		'require_user_configuration' => true,
		'require_user_password_configuration' => true,
		'login_screen_dropdown' => true
	);

	private $users = null;

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param $username string
	 * @param $password string
	 * @return User_Model|null
	 */
	public function login($username, $password) {

		if (empty($username) || empty($password))
			return null;

		$user = $this->authenticate_user($username, $password);
		if (!$user) {
			return null;
		}

		/*
		 * username shuold be part of the user object, but is only the key in
		 * auth_users.json
		 */
		$user->set_auth_data(array('own_user_change_password' => true));
		return $user;

	}

	/**
	 * Given a list of groups, return an associative array with groups as
	 * keys and a boolean if group is available in the backend. If it is
	 * unknown if the user is available, the field is unset.
	 *
	 * If driver supports multiple backends, the extra auth_method can be set to
	 * the backend. Otherwise, a superset is should given of all backends
	 *
	 * @param $grouplist List of groups to check
	 * @return Associative array of the groups in $grouplist as keys, boolean as values
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
				$name = substr($group, 5);
				$user = $this->users->reduce_by('username', $name, '=')->one();
				$result[$group] = (boolean) $user;
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
		$this->fetch_users();
		$user = $this->users->reduce_by('username', $username, '=')->one();
		if (!$user) {
			return array();
		}
		return $user->get_groups();
	}

	private function fetch_users() {
		if ($this->users) return;
		$this->users = UserPool_Model::all();
	}

	public function get_user_count () {
		return count(UserPool_Model::all());
	}

	private function store_users() {
		foreach ($this->users as $user) {
			$user->save();
		}
	}

	/**
	 * *************************** Authentication ***************************
	 */

	/**
	 * Authenticate user, and return it's row from the database.
	 * Return false
	 * if authentication failed or if user cannot login using this driver
	 *
	 * @param
	 *        	string username of the user
	 * @param
	 *        	string password entered by the user
	 * @return User_Model|false
	 */
	private function authenticate_user($username, $password) {

		$this->fetch_users();
		$user = $this->users->reduce_by('username', $username, '=')->one();

		if (!$user) {
			op5Log::instance('auth')->log('notice', "User '$username' not found");
			return null;
		}


		if(count($user->get_modules()) === 0) {
			op5Log::instance('auth')->log('error', "User '$username' have no 'modules' section and can therefore not be logged in. This is considered an error, did the upgrade script not add the required 'modules' section to every user?");
			return null;
		}

		// Check if user has module membership
		if (!in_array($this->module->get_modulename(), $user->get_modules(), true)) {
			op5Log::instance('auth')->log('notice',
				"User '$username' is not configured to login using the module: {$this->config['name']}");
			return null;
		}

		if (self::valid_password($password, $user->get_password(), $user->get_password_algo()) === true) {
			return $user;
		}

		op5Log::instance('auth')->log('notice', "User '$username' found but bad password provided");
		return null;
	}

	/**
	 * Validates a password using the given algorithm
	 *
	 * @param $pass string
	 * @param $hash string
	 * @param $algo string
	 * @return boolean
	 */
	public static function valid_password($pass, $hash, $algo = '') {
		if ($algo === false || !is_string($algo))
			return false;
		if (empty($pass) || empty($hash))
			return false;
		if (!is_string($pass) || !is_string($hash))
			return false;

		switch ($algo) {
		case 'sha1':
			return sha1($pass) === $hash;

		case 'b64_sha1':
			// Passwords can be one of
			// ... base64 encoded raw sha1
			return base64_encode(sha1($pass, true)) === $hash;

		case 'crypt':
			// ... crypt() encrypted
			return crypt($pass, $hash) === $hash;

		case 'plain':
			// ... plaintext (stupid, but true)
			return $pass === $hash;

		case 'apr_md5':
			// ... or a mad and weird aberration of md5
			return self::apr_md5_validate($pass, $hash);
		default:
			return false;
		}

		// not-reached
		return false;
	}

	/**
	 * Validates a password using apr's md5 hash algorithm
	 */
	private static function apr_md5_validate($pass, $hash) {
		$pass = escapeshellarg($pass);
		$hash = escapeshellarg($hash);
		$cmd = realpath(APPPATH . '/../cli-helpers') .
			 "/apr_md5_validate $pass $hash";
		$ret = $output = false;
		exec($cmd, $output, $ret);
		return $ret === 0;
	}
} // End Auth
