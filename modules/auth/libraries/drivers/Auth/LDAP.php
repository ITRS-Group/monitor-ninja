<?php defined('SYSPATH') or die('No direct script access.');

class Auth_LDAP_Driver extends Auth_ORM_Driver {

	public function login($user, $password, $remember)
	{
		if (empty($user) || empty($password))
			return false;

		if (is_object($user)) {
			$username = $user->username;
		} else {
			$username = $user;
		}

		$user = new ldap_user_Model();
		$user->username = $username;

		if (($raw_config = @file('/opt/op5sys/etc/ldapserver')) === false) {
			Kohana::log('error', 'Trying to perform LDAP authentication, but LDAP authentication is not configured');
			return false;
		}

		$ldapbindpw = @file_get_contents('/opt/op5sys/etc/ldapbindpw');
		$ldapbindpw = chop($ldapbindpw);
		
		$config = array();
		foreach ($raw_config as $line)
		{
			$key = strtok(trim($line), '=');
			$value = strtok('');
			if ($key[0] != '#')
				$config[$key] = $value;
		}

		if (!isset($config['LDAP_SERVER']) || !($ds = ldap_connect($config['LDAP_SERVER']))) {
			Kohana::log('error', 'Trying to perform LDAP authentication, but no LDAP server specified');
			return false;
		}

		ldap_set_option( $ds, LDAP_OPT_PROTOCOL_VERSION, 3 );

		if (isset($config['LDAP_IS_AD']) && $config['LDAP_IS_AD'] == '1'
			&& isset($config['LDAP_UPNSUFFIX']))
		{
			if (@ldap_bind($ds, "{$username}@{$config['LDAP_UPNSUFFIX']}", $password))
			{
				$this->complete_login($user);
				return true;
			}
			Kohana::log('error', "Couldn't authenticate, because AD server rejected bind as $username@{$config['LDAP_UPNSUFFIX']}: ".ldap_error($ds));
		}
		else
		{
			if (!isset($config['LDAP_USERS']) || !isset($config['LDAP_USERKEY'])) {
				Kohana::log('error', 'Trying to perform LDAP authentication, but missing configuration about users.');
				return false;
			}

			if (@ldap_bind($ds, "{$config['LDAP_USERKEY']}={$username},{$config['LDAP_USERS']}", $password))
			{
				$this->complete_login($user);
				return true;
			} else {
				Kohana::log('alert', "Couldn't authenticate, because LDAP server rejected bind as {$config['LDAP_USERKEY']}={$username},{$config['LDAP_USERS']}: ".ldap_error($ds)." - I'm going hunting for subtrees");
				if(!empty($ldapbindpw)) {
					if(!@ldap_bind($ds,$config['LDAP_BIND_DN'],$ldapbindpw)) {
						Kohana::log('error', "Couldn't bind to LDAP server as {$config['LDAP_BIND_DN']}: ".ldap_error($ds));
						return false;
					}
				}
				$userfilter = '(|(objectClass=Account)(objectClass=posixAccount)(objectClass=user)(objectClass=inetOrgPerson))';
				if (file_exists('/opt/op5sys/share/filters_custom.php'))
					require_once('/opt/op5sys/share/filters_custom.php');
				$search=ldap_search($ds,$config['LDAP_USERS'],"(&$userfilter({$config['LDAP_USERKEY']}={$username}))");
				if($entries = ldap_get_entries($ds,$search)) {
					unset($entries["count"]);
					foreach ($entries as $entry) {
						if(@ldap_bind($ds, $entry["dn"], $password)) {
							$this->complete_login($user);
							return true;
						} else {
							Kohana::log('alert', "Couldn't authenticate, because LDAP server rejected bind as {$config['LDAP_USERKEY']}={$username},{$config['LDAP_USERS']}: ".ldap_error($ds)." - looking for other matching usernames");
						}
					}
				}
			}
		}

		return false;
	}

	public function password($user)
	{
		return NULL;
	}

	# rfc4514 3 kind of tells us how to escape DN:s.
	# rfc4515 3 tells us how to escape search filters.
	# AD does whatever it feels like doing, which isn't related to those two at all.
	# copied from op5auth
	private function ldap_escape($str, $from_dn = false)
	{
		$dn_ccodes = array(0x5c, 0x20, 0x22, 0x23, 0x28, 0x29, 0x2a, 0x2b, 0x2c, 0x3b, 0x3c, 0x3e);
		$ccodes = array();
		foreach ($dn_ccodes as $ccode) {
			if ($from_dn)
				$ccodes['\\'.chr($ccode)] = $ccode;
			else
				$ccodes[chr($ccode)] = $ccode;
		}

		for ($i = 0; $i < 0x20; $i++) {
			if ($from_dn)
				$ccodes['\\'.chr($i)] = $i;
			else
				$ccodes[chr($i)] = $i;
		}

		foreach ($ccodes as $chr => $val) {
			if ($from_dn)
				$str = str_replace($chr, '\\'.$chr, $str);
			else
				$str = str_replace($chr, sprintf('\%02X', $val), $str);
		}

		$sf_ccodes = array(chr(0) => 0, chr(0x2a) => 0x2a, chr(0x28) => 0x28, chr(0x29) => 0x29);

		foreach ($sf_ccodes as $chr => $val)
			$str = str_replace($chr, sprintf('\%02X', $val), $str);

		return $str;
	}
}
