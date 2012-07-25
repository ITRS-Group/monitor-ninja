<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class AD_Auth_Core extends Auth_Core {

	public $config;

	public function __construct( $config ) {
		$defaults = array(
			'LDAP_MEMBERKEY_IS_DN'  => 1,
			'LDAP_MEMBERKEY'        => 'member',
			'LDAP_GROUP_FILTER'     => '(objectClass=group)',
			'LDAP_USER_FILTER'      => '(objectClass=person)',
			'LDAP_USERKEY_UPN'      => 'userPrincipalName',
			'LDAP_USERKEY_REALNAME' => 'cn',
			'LDAP_USERKEY_EMAIL'    => 'email',
			'LDAP_RECURSIVE_GROUPS' => 1,
			'LDAP_PROTOCOL_VERSION' => 3
			);
	
		$this->config = array_merge( $defaults, $config, $this->read_ldap_config() );
		
		/* Say that we have groups support */
		$this->backend_supports['groups'] = true;

		$this->conn = ldap_connect( $this->config['LDAP_SERVER'] );
		
		if( isset( $this->config['LDAP_PROTOCOL_VERSION'] ) ) {
			ldap_set_option( $this->conn, LDAP_OPT_PROTOCOL_VERSION, $this->config['LDAP_PROTOCOL_VERSION'] );
		}
		
		Kohana::log( 'debug', var_export( $_SESSION, true ) );
	}
	
	
	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   string   specifies the authentication method, if multiple is avalible, ignore otherwise
	 * @return  user	 user object or FALSE
	 */
	public function login($username, $password, $auth_method = false) {
		$upn = $username . '@' . $this->config['LDAP_UPNSUFFIX'];
	
		/* Try to bind as user to authenticate */
		if( !$this->bind( $upn, $password ) ) {
			Kohana::log( 'debug', 'LDAP: Authentication failed for ' . $upn . '. ' . ldap_error($this->conn));
			return false;
		}

		/* Lookup user */
		$user_info = $this->get_info_for_upn( $upn );
		if( $user_info === false ) {
			Kohana::log( 'debug', 'LDAP: User not found: '.$upn );
			return false;
		}

		$groups    = $this->resolve_group_names( $user_info['dn'] );

		$user = new Auth_LDAP_User_Model( array(
			'username'   => $user_info[ $this->config['LDAP_USERKEY'] ][0],
			'groups'     => $groups,
			'realname'   => array_key_exists( $this->config['LDAP_USERKEY_REALNAME'], $user_info ) ?
								$user_info[ $this->config['LDAP_USERKEY_REALNAME'] ][0] :
								$user_info[ $this->config['LDAP_USERKEY'] ][0],
			'email   '   => array_key_exists( $this->config['LDAP_USERKEY_EMAIL'], $user_info ) ?
								$user_info[ $this->config['LDAP_USERKEY_EMAIL'] ][0] :
								''
			) );
		
		$this->setuser( $user );
		return $user;
	}

	private function get_info_for_upn( $upn ) {
		$res = $this->ldap_query( array(
				$this->config['LDAP_USERKEY_UPN'] => $upn
			),
			$this->config['LDAP_USERS'],
			$this->config['LDAP_USER_FILTER'],
			array('dn')
			);
		$entries = ldap_get_entries( $this->conn, $res );
		ldap_free_result( $res );
		
		if( $entries['count'] != 1 ) {
			return false;
		}
		
		return $entries[0];
	}

	/************************ Groups *********************************/
	
	/**
	 * Returns a list of group names:s for which contains a certain DN.
	 *
	 * Depending on config, it resolves the groups recursively
	 *
	 * @param   array   Base DN to search
	 * @return  array   Array of group names:s
	 */
	private function resolve_group_names( $object_dn ) {
		$groups = $this->resolve_groups( $object_dn );
		return array_map( function($dn) { $parts = ldap_explode_dn($dn, 1); return $parts[0]; }, $groups );
	}
	
	/**
	 * Returns a list of group DN:s for which contains a certain DN.
	 *
	 * Depending on config, it resolves the groups recursively
	 *
	 * @param   array   Base DN to search
	 * @return  array   Array of group DN:s
	 */
	private function resolve_groups( $object_dn ) {
		$tosearch = array( $object_dn );
		$groups = array();
		$recursive = $this->config['LDAP_RECURSIVE_GROUPS'];
		
		while( count( $tosearch ) > 0 ) {
			$cur = array_pop( $tosearch );
			foreach( $this->resolve_groups_nonrecursive( $cur ) as $group ) {
				if( !in_array( $group, $groups ) ) {
					$groups[] = $group;
					if( $recursive ) {
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
	 * @param   array   Base DN to search
	 * @param   boolean To search recursivly
	 * @return  array   Array of group DN:s
	 */	
	private function resolve_groups_nonrecursive( $object_dn ) {
		$res = $this->ldap_query( array(
			$this->config['LDAP_MEMBERKEY'] => $this->config['LDAP_MEMBERKEY_IS_DN'] ? $object_dn : $object_dn /*FIXME*/
			),
			$this->config['LDAP_GROUP'],
			$this->config['LDAP_GROUP_FILTER'],
			array( 'dn' )
			);
		
		$groups = array();
		
		$entry = ldap_first_entry( $this->conn, $res );
		while( $entry !== false ) {
			$group = ldap_get_dn( $this->conn, $entry );
			$groups[] = $group;
			Kohana::log( 'debug', "Got group: $group" );
			$entry = ldap_next_entry( $this->conn, $entry );
		}
		ldap_free_result( $res );
		
		return $groups;
	}


	/************************ LDAP Access ***********************************/

	private function bind( $dn = false, $password = false ) {
		if( $dn === false ) {
			return @ldap_bind( $this->conn ); /* FIXME: Allow non-anonymous bind */
		} else {
			return @ldap_bind( $this->conn, $dn, $password );
		}
	}
	
	
	private function ldap_query( $matches, $base_dn, $filter='', $attributes=null ) {
		if( count( $matches ) > 0 ) {
			foreach( $matches as $key => $value ) {
				$filter .= sprintf( '(%s=%s)', $key, $this->ldap_escape( $value, true ) );
			}
			$filter = '(&'.$filter.')';
		}
		
		Kohana::log( 'debug', "LDAP: Searching for $filter at $base_dn" );
		
		return ldap_search( $this->conn, $base_dn, $filter );
	}
		

	private function ldap_escape( $str, $from_dn = false ) {
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
	
	/*********************************** Config *****************************/

	private function read_ldap_config() {
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
		return $config;
	}
} // End Auth
