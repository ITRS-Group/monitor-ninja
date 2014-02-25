<?php
require_once (__DIR__ . '/AuthDriver.php');
require_once (__DIR__ . '/User.php');
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
	protected static $metadata = array ('require_user_configuration' => true,
		'require_user_password_configuration' => true);
	private $users = false;

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param $username string
	 * @param $password string
	 * @return object
	 */
	public function login($username, $password) {
		if (empty($username) || empty($password))
			return false;

		$userdata = $this->authenticate_user($username, $password);
		if ($userdata === false) {
			return false;
		}

		/*
		 * username shuold be part of the user object, but is only the key in
		 * auth_users.json
		 */
		$userdata['username'] = $username;
		$userdata['auth_data'] = array ('own_user_change_password' => true);

		return new op5User($userdata);
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

		$groups = array ();
		foreach ($this->users as $user => $userdata) {
			if (isset($userdata['groups'])) {
				foreach ($userdata['groups'] as $group) {
					$groups[$group] = $group;
				}
			}
		}

		$result = array ();
		foreach ($grouplist as $group) {
			if (substr($group, 0, 5) == 'user_') {
				$result[$group] = isset($this->users[substr($group, 5)]);
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
	 * @return array A list of groups, or false if not possible
	 */
	public function groups_for_user($username) {
		$this->fetch_users();
		if (!isset($this->users[$username])) {
			return false;
		}
		if (!isset($this->users[$username]['groups'])) {
			return array ();
		}
		return $this->users[$username]['groups'];
	}
	private function fetch_users() {
		if ($this->users === false) {
			$this->users = op5Config::instance()->getConfig('auth_users');
		}
	}
	private function store_users() {
		if (is_array($this->users)) {
			op5Config::instance()->setConfig('auth_users', $this->users);
		}
	}

	/**
	 * *************************** Authentication ***************************
	 */

	/**
	 * Authenticate user, and return it's row from the database.
	 * Return false
	 * if authentication failed
	 *
	 * @param
	 *        	string username of the user
	 * @param
	 *        	string password entered by the user
	 * @return false array result from the user table
	 */
	private function authenticate_user($username, $password) {
		$this->fetch_users();

		if (!isset($this->users[$username])) {
			op5Log::instance('auth')->log('notice',
				"User '$username' not found");
			return false;
		}

		$user = $this->users[$username];
		if (self::valid_password($password, $user['password'],
			$user['password_algo']) === true) { /*
			                                                                                            *
			                                                                                            * FIXME
			                                                                                            */
			return $user;
		}
		op5Log::instance('auth')->log('notice',
			"User '$username' found but bad password provided");
		return false;
	}

	/**
	 * Update password for a user.
	 *
	 * @param $user op5User
	 *        	User object
	 * @param $password string
	 *        	Password to set
	 * @return boolean True if successful, False if error
	 */
	public function update_password($user, $password) {
		$this->fetch_users();
		if (isset($this->users[$user->username])) {
			$this->users[$user->username]['password'] = crypt($password);
			$this->users[$user->username]['password_algo'] = 'crypt';
			$this->store_users();
			return true;
		}
		return false;
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
