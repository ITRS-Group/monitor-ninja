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

	public function __construct( $config )
	{
		$this->config = $config;

		$this->conn = ldap_connect( $this->config->ldap->server );
		
		if( isset( $this->config->ldap->protocol_version ) ) {
			ldap_set_option( $this->conn, LDAP_OPT_PROTOCOL_VERSION, $this->config->ldap->protocol_version );
		}
	}
	
	
	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   string   specifies the authentication method, if multiple is avalible, ignore otherwise
	 * @return  user	 user object or FALSE
	 */
	public function login($username, $password, $auth_method = false)
	{
		if( isset( $this->config->ldap->is_ad ) && $this->config->ldap->is_ad ) {
			$user_info = $this->do_ad_login( $username, $password );
		}
		else {
			$user_info = $this->do_ldap_login( $username, $password );
		}
		
		if( $user_info === false ) {
			return false;
		}
		
		$groups = $this->resolve_group_names( $user_info['dn'] );

		$username = $user_info[ strtolower( $this->config->ldap->userkey ) ][0];
		if( $this->config->ldap->userkey_is_upn ) {
			$parts = explode( '@', $username, 2 );
			$username = $parts[0];
		}

		$user = new Auth_LDAP_User_Model( array(
			'username'   => $username,
			'groups'     => $groups,
			'realname'   => array_key_exists( $this->config->ldap->userkey_realname, $user_info ) ?
								$user_info[ $this->config->ldap->userkey_realname ][0] :
								$username,
			'email   '   => array_key_exists( $this->config->ldap->userkey_email, $user_info ) ?
								$user_info[ $this->config->ldap->userkey_email ][0] :
								''
			) );
		
		$this->setuser( $user );
		return $user;
	}

	private function get_info_for_user( $filter )
	{
		$res = $this->ldap_query( $filter,
			$this->config->ldap->user_base_dn,
			$this->config->ldap->user_filter,
			array('dn')
			);
		$entries = ldap_get_entries( $this->conn, $res );
		ldap_free_result( $res );
		
		if( $entries['count'] != 1 ) {
			return false;
		}
		
		return $entries[0];
	}

	/************************ Authenticate ***************************/
	
	protected function do_ldap_login( $username, $password ) {
		/* Bind with service account (or anonymously) */
		if( !$this->bind( $this->config->ldap->bind_dn, $this->config->ldap->bind_secret ) ) {
			Kohana::log( 'error', 'LDAP: Could not do initial binding' );
			return false;
		}

		/* Lookup user */
		$user_info = $this->get_info_for_user( array(
				$this->config->ldap->userkey => $username
			) );
		if( $user_info === false ) {
			Kohana::log( 'debug', 'LDAP: User not found: '.$username );
			return false;
		}

		/* Try to bind as user to authenticate */
		if( !$this->bind( $user_info['dn'], $password ) ) {
			Kohana::log( 'debug', 'LDAP: Authentication failed for '.$user_info['dn'] . '. ' . ldap_error($this->conn));
			return false;
		}
		
		return $user_info;
		
	}
	
	protected function do_ad_login( $username, $password ) {
		$upn = $username . '@' . $this->config->ldap->upn_suffix;
	
		/* Try to bind as user to authenticate */
		if( !$this->bind( $upn, $password ) ) {
			Kohana::log( 'debug', 'LDAP: Authentication failed for ' . $upn . '. ' . ldap_error($this->conn));
			return false;
		}

		/* Lookup user */
		$user_info = $this->get_info_for_user( array(
				$this->config->ldap->userkey => $upn
			) );

		if( $user_info === false ) {
			Kohana::log( 'debug', 'LDAP: User not found: '.$upn );
			return false;
		}
		
		return $user_info;
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
	protected function resolve_group_names( $object_dn )
	{
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
	protected function resolve_groups( $object_dn )
	{
		$tosearch = array( $object_dn );
		$groups = array();
		$recursive = $this->config->ldap->group_recursive;
		
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
	protected function resolve_groups_nonrecursive( $object_dn )
	{
		$res = $this->ldap_query( array(
			$this->config->ldap->memberkey => $this->config->ldap->memberkey_is_dn ? $object_dn : $object_dn /*FIXME*/
			),
			$this->config->ldap->group_base_dn,
			$this->config->ldap->group_filter,
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

	protected function bind( $dn = false, $password = false )
	{
		if( $dn === false ) {
			return @ldap_bind( $this->conn ); /* FIXME: Allow non-anonymous bind */
		} else {
			return @ldap_bind( $this->conn, $dn, $password );
		}
	}
	
	
	protected function ldap_query( $matches, $base_dn, $filter='', $attributes=null )
	{
		if( count( $matches ) > 0 ) {
			foreach( $matches as $key => $value ) {
				$filter .= sprintf( '(%s=%s)', $key, $this->ldap_escape( $value, true ) );
			}
			$filter = '(&'.$filter.')';
		}
		
		Kohana::log( 'debug', "LDAP: Searching for $filter at $base_dn" );
		
		return ldap_search( $this->conn, $base_dn, $filter );
	}
		

	protected function ldap_escape( $str, $from_dn = false )
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
} // End Auth
