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
class op5AuthDriver_Session extends op5AuthDriver {

	/**
	 * Attempt to log in a user by static configuration, or external
	 * infromation.
	 *
	 * Useful for example for HTTP-auth.
	 *
	 * @return User_Model User object
	 */
	public function auto_login() {
		$params = array ();

		$config = $this->module->get_properties();
		if (isset($this->config['username_session_key'])) {
			$params['username'] = $_SESSION[$config['username_session_key']];
		} else {
			return null;
		}

		if (isset($this->config['groups_session_key'])) {
			$params['groups'] = $_SESSION[$config['groups_session_key']];
		}

		if (isset($config['single_shot']) && $config['single_shot']) {
			unset($_SESSION[$this->config['username_session_key']]);
			unset($_SESSION[$this->config['groups_session_key']]);
		}

		return new User_Model($params);
	}
} // End Auth
