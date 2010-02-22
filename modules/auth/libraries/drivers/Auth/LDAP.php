<?php defined('SYSPATH') or die('No direct script access.');

class Auth_LDAP_Driver extends Auth_ORM_Driver {

	public function login($user, $password, $remember)
	{
		if (empty($user) || empty($password))
			return false;

		if (!is_object($user)) {
			$username = $user;
			$user = ORM::factory('user', $username);
			// the line below is required because ORM::factory doesn't fill username for LDAP users
			$user->username = $username;
		}

		if (!($ds = ldap_connect($this->config['ldap_server'])))
			return false;

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $this->config['ldap_version']);

		if (@ldap_bind($ds, str_replace('[username]', $user->username, $this->config['ldap_dn']), $password))
		{
			$this->complete_login($user);
			return true;
		}
		else
			return false;
	}

	public function password($user)
	{
		return NULL;
	}
}
