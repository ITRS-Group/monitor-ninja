<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class LDAP_Auth_Core extends Auth_Core {

	public $config;

	public function __construct( $config ) {
		$this->config = array_merge( $config, $this->read_ldap_config() );
		$this->rights = $this->read_ldap_rights();

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
	 * @param   boolean  enable auto-login
	 * @return  user	 user object or FALSE
	 */
	public function login($username, $password, $remember = FALSE) {
		/* Bind with service account (or anonymously) */
		if( !$this->bind() ) {
			Kohana::log( 'error', 'LDAP: Could not do initial binding' );
			return false;
		}

		/* Lookup user */
		$user_info = $this->get_info_for_user( $username );
		if( $user_info === false ) {
			Kohana::log( 'debug', 'LDAP: User not found: '.$username );
			return false;
		}

		/* Try to bind as user to authenticate */
		if( !$this->bind( $user_info['dn'], $password ) ) {
			Kohana::log( 'debug', 'LDAP: Authentication failed for '.$user_info['dn'] );
			return false;
		}
		$groups    = $this->resolve_group_names( $user_info['dn'] );
		$auth_data = $this->get_rights_for_groups( $groups );

		$user = new Auth_LDAP_User_Model( array(
			'username'   => $user_info[ $this->config['LDAP_USERKEY'] ][0],
			'groups'     => $groups,
			'auth_data'  => $auth_data,
			'commonname' => $user_info[ $this->config['LDAP_USERKEY_PRINTABLE'] ][0]
			) );
		$this->setuser( $user );
		
		return $user;
	}

	private function get_info_for_user( $username ) {
		$res = $this->ldap_query( array(
				$this->config['LDAP_USERKEY'] => $username
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

	/************************ Authorization *********************************/
	
	/**
	 * Returns an array with keys representing the nagios autorization points
	 * with a boolean as value representing if the user has access, given a
	 * list of groups the user is member of.
	 *
	 * @param   array   list of groups to search
	 * @return  array   Array of authorization data
	 */
	private function get_rights_for_groups( $groups ) {
		$auth_data = array();
		foreach( $this->rights as $auth_point => $auth_groups ) {
			$access = false;
			foreach( $auth_groups as $auth_group ) {
				if( in_array( $auth_group, $groups ) ) {
					$access = true;
				}
			}
			$auth_data[ $auth_point ] = $access;
		}
		return $auth_data;
	}
	
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

	private function read_ldap_rights() {
		if (($raw_config = @file('/opt/op5sys/etc/ldaprights.cfg')) === false) {
			Kohana::log('error', 'Trying to perform LDAP authentication, but ldaprights.cfg is missing');
			return false;
		}
		
		$rights = array();
		foreach ($raw_config as $line)
		{
			if ($line[0] == '#')
				continue;

			$groups = array();
			
			$key = strtok(trim($line), ' ');
			while( $value = strtok(',') ) {
				$value = trim( $value );
				if( $value ) {
					$groups[] = $value;
				}
			}
			
			$rights[ $key ] = $groups;
		}
		return $rights;
	}
} // End Auth
