<?php
require_once (__DIR__ . '/AuthDriver.php');
require_once (__DIR__ . '/AuthException.php');
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
class op5AuthDriver_LDAP extends op5AuthDriver {
	private $conn = false;

	const LDAP_OPT_DIAGNOSTIC_MESSAGE = 0x0032;

	protected static $metadata = array (
		'require_user_configuration' => false,
		'require_user_password_configuration' => false,
		'login_screen_dropdown' => true
	);

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param $username string
	 * @param $password string
	 * @throws op5AuthException
	 * @return User_Model|null
	 */
	public function login($username, $password) {
		$this->connect();
		$config = $this->module->get_properties();

		if (isset($config['bind_with_upn']) &&
			 $config['bind_with_upn']) {
			$user_info = $this->do_upn_login($username, $password);
		} else {
			$user_info = $this->do_dn_login($username, $password);
		}

		if ($user_info === false) {
			$this->log->log('debug',
				'No User info returned. (incorrect login/connection error)');
			return null;
		}

		/**
		 * In some setups the bound user hasn't got access to search for groups.
		 * Check config if service account should bind again before group search
		 */
		if (!empty($config['resolve_with_service_account'])) {
			$this->log->log('debug', 'Resolving groups using service account');
			$this->bind_anon();
		}

		$groups = $this->groups_for_user($username, $user_info['dn']);

		if (!isset($user_info[strtolower($config['userkey'])])) {
			$this->log->log('error',
				'User hasn\'t got attribute ' . $config['userkey']);
			return null;
		}
		$username = $user_info[strtolower($config['userkey'])][0];
		if ($config['userkey_is_upn']) {
			$parts = explode('@', $username, 2);
			$username = $parts[0];
		}

		return new User_Model(
			array(
				'username' => $username,
				'groups' => $groups,
				'realname' => array_key_exists(strtolower($config['userkey_realname']), $user_info)
					? $user_info[strtolower($config['userkey_realname'])][0]
					: $username,
				'email' => array_key_exists(strtolower($config['userkey_email']), $user_info)
					? $user_info[strtolower($config['userkey_email'])][0]
					: ''
			)
		);
	}

	/**
	 * Kills connection to ldap server
	 *
	 * @return void
	 **/
	public function disconnect() {
		if ($this->conn === false) {
			return;
		}
		ldap_unbind($this->conn);
		$this->conn = false;
	}

	/**
	 * ********************** Groups ****************************************
	 */

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
		$this->connect();
		$this->bind_anon();
		$config = $this->module->get_properties();

		if ($this->conn === false) {
			return array ();
		}

		/* One list of users and one list of groups */
		$groups_user = array ();
		$groups_group = array ();

		/* split grouplist in a list of groups, and one list of users */
		foreach ($grouplist as $group) {
			if (substr($group, 0, 5) == 'user_') {
				$groups_user[] = substr($group, 5);
			} else {
				$groups_group[] = $group;
			}
		}

		/* Storage for the result */
		$result = array ();

		/* Build LDAP-query for groups */
		if (count($groups_group) > 0) {
			$filter = '(&' . $config['group_filter'] . '(|';
			foreach ($groups_group as $group) {
				$filter .= '(' . $config['groupkey'] . '=' .
					 $this->ldap_escape($group) . ')';
			}
			$filter .= '))';

			foreach ($groups_group as $group) {
				/*
				 * They are available for seaching, start with not available in
				 * LDAP, replace later
				 */
				$result[$group] = false;
			}

			$res = $this->ldap_query(array (), $config['group_base_dn'],
				$filter, array ($config['groupkey']));
			if ($res !== false) {
				$entry = ldap_first_entry($this->conn, $res);
				while ($entry !== false) {
					$attrs = ldap_get_attributes($this->conn, $entry);
					$attrs = array_change_key_case($attrs, CASE_LOWER);
					$result[$attrs[strtolower($config['groupkey'])][0]] = true;
					$entry = ldap_next_entry($this->conn, $entry);
				}
				ldap_free_result($res);
			}
		}

		/* Build LDAP-query for users */
		if (count($groups_user) > 0) {
			$filter = '(&' . $config['user_filter'] . '(|';
			foreach ($groups_user as $user) {
				if ($config['userkey_is_upn']) {
					$user .= '@' . $config['upn_suffix'];
				}
				$filter .= '(' . $config['userkey'] . '=' .
					 $this->ldap_escape($user) . ')';
			}
			$filter .= '))';

			foreach ($groups_user as $user) {
				/*
				 * They are available for seaching, start with not available in
				 * LDAP, replace later
				 */
				$result['user_' . $user] = false;
			}

			$res = $this->ldap_query(array (), $config['user_base_dn'],
				$filter, array ($config['userkey']));
			if ($res !== false) {
				$entry = ldap_first_entry($this->conn, $res);
				while ($entry !== false) {
					$attrs = ldap_get_attributes($this->conn, $entry);
					$attrs = array_change_key_case($attrs, CASE_LOWER);
					$user = $attrs[strtolower($config['userkey'])][0];
					if ($config['userkey_is_upn']) {
						$parts = explode('@', $user, 2);
						$user = $parts[0];
					}
					$result['user_' . $user] = true;
					$entry = ldap_next_entry($this->conn, $entry);
				}
				ldap_free_result($res);
			}
		}
		return $result;
	}

	/**
	 * Given a username, return a list of it's groups.
	 * If the config entry memberof is enabled we search for the groups using the
	 * memberOf user attribute, rather than looking up by groups.
	 * Useful when giving permissions to a user.
	 *
	 * @param $username string	User to search for
	 * @return array A list of groups, or false if not possible
	 */
	public function groups_for_user($username, $dn=null) {
		$config = $this->module->get_properties();

		$this->connect();
		$this->bind_anon();

		/* Lookup user if dn is not already supplied */
		if (empty($dn)) {
			$user_info = $this->get_info_for_user($username);
			if ($user_info === false) {
				return false;
			}
			$dn = $user_info['dn'];
		}
		/* Check if we should search for groups using memberof.
		/* Some setups might not have the group_memberof attribute in their config
		/* so we first check if it exists at all. */
		if (array_key_exists('group_memberof', $config) &&
				$config['group_memberof']) {
			$this->log->log("debug", "Getting groups for user using memberOf");
			return $this->resolve_groups_memberof($username);
		} else {
			return $this->resolve_group_names($dn);
		}

	}

	/**
	 * Given a username, return a list of it's groups using the LDAP memberOf
	 * attribute. Used internally by groups_for_user
	 *
	 * @param $username string	User to search for
	 * @return array A list of groups (emtpy array if none found)
	 */
	private function resolve_groups_memberof($username){
		$config = $this->module->get_properties();
		if ($config['userkey_is_upn']) {
			$username .= '@' . $config['upn_suffix'];
		}
		$filter = array ($config['userkey'] => $username);

		/* Some LDAP implementation does not return the memberOf property by default.
		 * Therefore we ask for it specifically. */
		$res = $this->ldap_query(
			$filter,
			$config['user_base_dn'],
			$config['user_filter'],
			array('dn','memberOf')
		);

		/* make the query ofr the user */
		$entries = ldap_get_entries($this->conn, $res);
		ldap_free_result($res);

		if ( count($entries) <= 0) {
			$this->log->log("error", "resolve_groups_memberof failed to find user.");
			return false;
		}

		/* Return an empty array if there are no memberof entries */
		if (! array_key_exists('memberof',$entries[0])) {
			return array();
		}

		/* gets the memberOf entries from the user query */
		$memberof = $entries[0]['memberof'];
		/* The first element in the array is always a count. We don't need it here
		 * so we strip it away. */
		array_shift($memberof);

		/* strip DN from the results */
		foreach ($memberof as &$value) {
			$value = $this->strip_dn($value);
		}

		return $memberof;
	}

	/**
	 * Returns a list of group names:s for which contains a certain DN.
	 *
	 * Depending on config, it resolves the groups recursively
	 *
	 * @param $object_dn array Base DN to search
	 * @return array Array of group names:s
	 */
	private function resolve_group_names($object_dn) {
		$groups = $this->resolve_groups($object_dn);
		return array_map(
			function ($dn) {
				$parts = ldap_explode_dn($dn, 1);
				return $parts[0];
			}, $groups);
	}

	/**
	 * Returns a list of group DN:s for which contains a certain DN.
	 *
	 * Depending on config, it resolves the groups recursively
	 *
	 * @param
	 *        	array Base DN to search
	 * @return array Array of group DN:s
	 */
	private function resolve_groups($object_dn) {
		$tosearch = array ($object_dn);
		$groups = array ();
		$config = $this->module->get_properties();
		$recursive = $config['group_recursive'];

		while (count($tosearch) > 0) {
			$cur = array_pop($tosearch);
			foreach ($this->resolve_groups_nonrecursive($cur) as $group) {
				if (!in_array($group, $groups)) {
					$groups[] = $group;
					if ($recursive) {
						$tosearch[] = $group;
					}
				}
			}
		}
		return $groups;
	}

	/**
	 * Returns a list of group DN:s for which contains a certain DN.
	 *
	 * Used internally by resolve_groups.
	 *
	 * @param $object_dn array Base DN to search
	 * @param boolean To search recursivly
	 * @return array Array of group DN:s
	 */
	private function resolve_groups_nonrecursive($object_dn) {

		$config = $this->module->get_properties();
		$res = $this->ldap_query(
			array(
				$config['memberkey'] => $config['memberkey_is_dn']
				? $object_dn
				: $this->strip_dn($object_dn)
			),
			$config['group_base_dn'],
			$config['group_filter']
		);

		$groups = array ();
		$entry = ldap_first_entry($this->conn, $res);

		while ($entry !== false) {
			$group = ldap_get_dn($this->conn, $entry);
			if ($group) {
				$groups[] = $group;
				$this->log->log('debug', "Got group: $group");
				$entry = ldap_next_entry($this->conn, $entry);
			} else {
				$entry = false;
			}
		}

		ldap_free_result($res);
		return $groups;

	}

	/**
	 * ********************** Authenticate **********************************
	 */

	/**
	 * Attempts to login using dn (LDAP login)
	 *
	 * @param $username string
	 * @param $password string
	 * @return false or user
	 *
	 */
	private function do_dn_login($username, $password) {
		$this->bind_anon();

		/* Lookup user */
		$user_info = $this->get_info_for_user($username);
		if ($user_info === false) {
			$this->log->log('debug', 'No user found');
			return false;
		}

		/* Try to bind as user to authenticate */
		if (!$this->bind($user_info['dn'], $password)) {
			$this->log->log('debug',
				'Could not bind to user, incorrect password?: ' .
					 ldap_error($this->conn));
			return false;
		}

		return $user_info;
	}

	/**
	 * Attempts to login using upn (AD login)
	 *
	 * @param $username string
	 * @param $password string
	 * @return false or user
	 */
	private function do_upn_login($username, $password) {
		$config = $this->module->get_properties();
		$upn = $username . '@' . $config['upn_suffix'];

		/* Try to bind as user to authenticate */
		if (!$this->bind($upn, $password)) {
			$this->log->log('debug',
				'Could not bind using upn/pass: ' . ldap_error($this->conn));
			return false;
		}

		/* Lookup user */
		$user_info = $this->get_info_for_user($username);

		return $user_info;
	}
	/**
	 * Fetch user info for username
	 *
	 * @param $filter string
	 * @return object
	 *
	 */
	private function get_info_for_user($filter) {

		$config = $this->module->get_properties();
		if (!is_array($filter)) {
			$username = $filter;
			if ($config['userkey_is_upn']) {
				$username .= '@' . $config['upn_suffix'];
			}
			$filter = array ($config['userkey'] => $username);
		}

		$res = $this->ldap_query(
			$filter,
			$config['user_base_dn'],
			$config['user_filter']
		);

		$entries = ldap_get_entries($this->conn, $res);
		ldap_free_result($res);

		if ($entries['count'] != 1) {
			return false;
		}

		return $entries[0];
	}

	/**
	 * ********************** Connection ************************************
	 */

	/**
	 * Attempts to connect to LDAP/AD
	 *
	 * @return void
	 *
	 */
	private function connect() {

		if ($this->conn !== false) return;

		$config = $this->module->get_properties();

		if (!isset($config['server']))
			$this->throw_error('Server is not specified');

		$enctype = false;
		if (isset($config['encryption'])) {
			$enctype = $config['encryption'];
		}

		if ($enctype !== false && $enctype !== 'none' && $enctype !== 'start_tls' &&
			 $enctype !== 'ssl') {
			$this->throw_error(
				'Encryption is set, but is not "start_tls", "ssl" or "none"');
		}

		$port = 389;
		if ($enctype == 'ssl') {
			$port = 636;
		}

		if (isset($config['port']) && $config['port'] != false) {
			$port = $config['port'];
		}

		$urls = array ();
		foreach (preg_split('/\s+/', $config['server']) as $server) {
			$url = ($enctype == 'ssl') ? 'ldaps://' : 'ldap://';
			$url .= $server . ':' . $port;
			$urls[] = $url;
		}
		$url = implode(' ', $urls);

		$this->log->log('debug', 'Connecting to ' . $url);

		if (($this->conn = ldap_connect($url)) === false) {
			$this->throw_error('Could not connect to LDAP server: ' . $url);
		}

		if ($enctype == 'start_tls') {
			if (!ldap_start_tls($this->conn)) {
				$this->throw_error(
					'Could not use Start TLS for server: ' . $url . ': ' .
						 ldap_error($this->conn));
			}
		}

		$ldap_options = array ();
		if (isset($config['ldap_options']) &&  is_array($config['ldap_options']))
			$ldap_options = $config['ldap_options'];

		if (isset($config['protocol_version'])) {
			$ldap_options['LDAP_OPT_PROTOCOL_VERSION'] = $config['protocol_version'];
		}

		foreach ($ldap_options as $opt => $val) {
			if (defined($opt)) {
				$this->log->log('debug',
					'Setting LDAP option: ' . $opt . ' = ' .
						 var_export($val, true));
				$option = ldap_set_option($this->conn, constant($opt), $val);
				if (!$option) {
					$this->log->log('error',
						'Failed setting LDAP option: ' . $opt);
				}
			} else {
				$this->throw_error(
					'Unknown LDAP option in configuration: ' . $opt);
			}
		}
	}

	/**
	 * Attempts to bind
	 *
	 * @param $dn string
	 * @param $password string
	 * @return bool
	 *
	 */
	private function bind($dn = false, $password = false) {
		$config = $this->module->get_properties();
		if ($dn === false) {
			$this->log->log('debug', 'Bindning anonymously');
			$result = @ldap_bind($this->conn); /*
			                                    * FIXME: Allow non-anonymous bind
			                                    */
		} else {
			$this->log->log('debug',
				$this->module->get_modulename() . ': Bindning as ' . $dn .
					(empty($password) ? ', password=false' : ' with password set'));
				if (empty($password)) {
					$result = false;
                } else {
                	$result = @ldap_bind($this->conn, $dn, $password);
                }
		}
		if ($result === false) {
			/* Error, is it a real error or just invalid credentials? */
			if (empty($password)) {
				$this->throw_error('No or empty password supplied');
			}
			else if (ldap_errno($this->conn) != 0x31 /*LDAP_INVALID_CREDENTIALS*/) {
				$this->throw_error('Bind error');
			}
		}
		return $result;
	}

	/**
	 * Attempts to bind anonymously
	 *
	 * @return bool
	 *
	 */
	private function bind_anon() {
		$config = $this->module->get_properties();
		/* Bind with service account (or anonymously) */
		if (!isset($config['bind_secret']) ||
			 $config['bind_secret'] === false) {
			/* Bind anonymously */
			if (!$this->bind()) {
				$this->throw_error('Could not bind anonymously to LDAP server');
			}
		} else {
			$secret = $config['bind_secret'];

			/*
			 * If $secret is an array, it references to another file, for
			 * security
			 */
			if (is_array($secret)) {
				/* Reference to file - slow, but simple */
				if (isset($secret['file'])) {
					$secret = trim(file_get_contents($secret['file']));
				} 				/*
				 * Reference to config, can be cached... A script having access
				 * to the cache also have access to the config, meaning no major
				 * security issue with caching...
				 */
				else if (isset($secret['config'])) {
					$secret_conf = op5Config::instance()->getConfig($secret['config']);
					if ($secret_conf === false) {
						$this->throw_error(
							'secret specified as config reference, but referenced config not found');
					}
					if (!isset($secret_conf[$this->module->get_modulename()])) {
						$this->throw_error(
							'section ' . $this->modules->get_modulename() .
								 ' not found in config file ' . $secret['config']);
					}
					$secret = $secret_conf[$this->module->get_modulename()];
				}
			}

			if (!$this->bind($config['bind_dn'], $secret)) {
				$this->throw_error(
					'Could not bind using config user to LDAP server');
			}
		}
	}

	/**
	 * ********************** Helpers ***************************************
	 */
	/**
	 * Throws errors that ldap genrates
	 *
	 * @param $msg string
	 * @return void
	 *
	 */
	private function throw_error($msg) {
		$config = $this->module->get_properties();
		if ($this->conn !== false) {
			$msg .= ' (' . ldap_errno($this->conn) . ': ' .
				 ldap_error($this->conn) . ')';
			// Check if we can get the diagnostic message of the error
			if (ldap_get_option($this->conn, self::LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
				$msg .= " " . $extended_error;
			}
		}
		throw new op5AuthException(
			'op5AuthDriver_LDAP / ' . $this->module->get_modulename() . ': ' . $msg);
	}

	/**
	 * Performs ldap searches
	 *
	 * @param $matches array
	 * @param $base_dn string
	 * @param $filter string
	 * @param $attributes null
	 * @return void
	 *
	 */
	private function ldap_query($matches, $base_dn, $filter = '', $attributes = null) {
		$matchstr = $this->ldap_build_query($matches);
		if (!empty($matchstr) && !empty($filter)) {
			$filter = '(&' . $matchstr . $filter . ')';
		} else {
			$filter = $matchstr . $filter;
		}
		$this->log->log('debug', "LDAP: Searching for $filter at $base_dn");

		if ($attributes == null) {
			$result = @ldap_search($this->conn, $base_dn, $filter);
		} else {
			$result = @ldap_search($this->conn, $base_dn, $filter, $attributes);
		}

		if ($result === false) {
			$this->throw_error(
				'Error during LDAP search using query "' . $filter . '" at "' .
					 $base_dn . '"');
		}
		return $result;
	}

	/**
	 * Formats ldap queries
	 *
	 * @param $matches array
	 * @param $op string
	 * @return string
	 *
	 */
	private function ldap_build_query($matches, $op = '&') {
		$filter = '';
		if (count($matches) > 0) {
			foreach ($matches as $key => $value) {
				$filter .= sprintf('(%s=%s)', trim($key),
					$this->ldap_escape($value, true));
			}
			$filter = '(&' . $filter . ')';
		}
		return $filter;
	}

	/**
	 * Strips a DN down to the value of the first attribute.
	 *
	 * Useful for converting a dn to for example a group name.
	 *
	 * @param $dn string
	 * @return string
	 */
	private function strip_dn($dn) {
		$this->log->log('debug', 'Stripping: ' . $dn);
		$comps = ldap_explode_dn($dn, 1);
		if (count($comps) == 0)
			return false;
		$this->log->log('debug', 'Got: ' . $comps[0]);
		return $comps[0];
	}

	/**
	 * Ldap char escaping
	 *
	 * @param $str string
	 * @param $from_dn bool
	 * @return string
	 *
	 */
	private function ldap_escape($str, $from_dn = false) {
		$dn_ccodes = array (0x5c,0x20,0x22,0x23,0x28,0x29,0x2a,0x2b,0x2c,0x3b,
			0x3c,0x3e);
		$ccodes = array ();
		foreach ($dn_ccodes as $ccode) {
			if ($from_dn)
				$ccodes['\\' . chr($ccode)] = $ccode;
			else
				$ccodes[chr($ccode)] = $ccode;
		}

		for ($i = 0; $i < 0x20; $i++) {
			if ($from_dn)
				$ccodes['\\' . chr($i)] = $i;
			else
				$ccodes[chr($i)] = $i;
		}

		foreach ($ccodes as $chr => $val) {
			if ($from_dn)
				$str = str_replace($chr, '\\' . $chr, $str);
			else
				$str = str_replace($chr, sprintf('\%02X', $val), $str);
		}

		$sf_ccodes = array (chr(0) => 0,chr(0x2a) => 0x2a,chr(0x28) => 0x28,
			chr(0x29) => 0x29);

		foreach ($sf_ccodes as $chr => $val)
			$str = str_replace($chr, sprintf('\%02X', $val), $str);

		return $str;
	}
}
