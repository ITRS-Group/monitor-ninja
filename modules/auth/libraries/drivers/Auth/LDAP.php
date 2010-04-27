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

		if (($raw_config = @file('/opt/op5sys/etc/ldapserver')) === false)
			return false;

		$config = array();
		foreach ($raw_config as $line)
		{
			$key = strtok(trim($line), '=');
			$value = strtok('');
			if ($key[0] != '#')
				$config[$key] = $value;
		}

		if (!isset($config['LDAP_SERVER']) || !($ds = ldap_connect($config['LDAP_SERVER'])))
			return false;

		if (isset($config['LDAP_IS_AD']) && $config['LDAP_IS_AD'] == '1'
			&& isset($config['LDAP_UPNSUFFIX']))
		{
			if (@ldap_bind($ds, "{$user->username}@{$config['LDAP_UPNSUFFIX']}", $password))
			{
				$this->complete_login($user);
				return true;
			}
		}
		else
		{
			if (!isset($config['LDAP_USERS']) || !isset($config['LDAP_USERKEY']))
				return false;

			if (@ldap_bind($ds, "{$config['LDAP_USERKEY']}={$user->username},{$config['LDAP_USERS']}", $password))
			{
				$this->complete_login($user);
				return true;
			}
		}

		return false;
	}

	public function password($user)
	{
		return NULL;
	}
}
