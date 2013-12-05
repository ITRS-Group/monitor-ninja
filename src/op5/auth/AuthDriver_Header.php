<?php

require_once(__DIR__.'/AuthDriver.php');
require_once(__DIR__.'/User.php');
require_once(__DIR__.'/../config.php');

/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author
 * @copyright
 * @license
*/
class op5AuthDriver_Header extends op5AuthDriver {
	/* For testing, if headers is mocked, use the mocked headers. Normally, $mocked_headers = false */
	private $mocked_headers = false;

	/**
	 * Attempt to log in a user by static configuration, or external infromation.
	 *
	 * Useful for example for HTTP-auth.
	 *
	 * @return  op5User  User object, or false
	 */
	public function auto_login() {
		$headers = array();

		/* For testing, if headers is mocked, use the mocked headers */
		if($this->mocked_headers != false) {
			$headers = $this->mocked_headers;
		} else if(function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		}
		
		$headers = array_change_key_case($headers, CASE_LOWER);

		$params = array();

		if($this->fetch_header_if($headers, 'header_username', $value)) {
			$params['username'] = $value;
		} else {
			return false;
		}

		if($this->fetch_header_if($headers, 'header_realname', $value)) {
			$params['realname'] = $value;
		} else {
			$params['realname'] = $params['username'];
		}

		if($this->fetch_header_if($headers, 'header_email', $value)) {
			$params['email'] = $value;
		} else {
			$params['email'] = '';
		}

		$group_delimiter = ' ';
		if(isset($this->config['group_list_delimiter'])) {
			$group_delimiter = $this->config['group_list_delimiter'];
		}

		if($this->fetch_header_if($headers, 'header_groups', $value)) {
			$params['groups'] = array_filter(array_map('trim', explode($group_delimiter, $value)));
		} else {
			$params['groups'] = array();
		}

		return new op5User($params);
	}

	/**
	 * Fetches header config if the given config key is set
	 *
	 * @param $headers array
	 * @param $config_key string
	 * @param $value mixed
	 * @return boolean
	 **/
	private function fetch_header_if( $headers, $config_key, &$value ) {
		if(isset($this->config[$config_key]) && isset($headers[strtolower($this->config[$config_key])])) {
			$value = $headers[strtolower($this->config[$config_key])];
			return true;
		}
		return false;
	}

	/**
	 * Sets mocked headers for driver tests
	 *
	 * @return void
	 **/
	public function test_mock_headers($headers) {
		$this->mocked_headers = $headers;
	}
} // End Auth
