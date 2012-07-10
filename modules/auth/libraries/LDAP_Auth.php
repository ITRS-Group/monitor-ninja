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

	private $config;

	public function __construct( $config ) {
		$this->config = $config;
		$this->ldap_config = $this->read_ldap_config();

		print_R( $this->ldap_config );

		$this->conn = ldap_connect( $this->ldap_config['LDAP_SERVER'] );
		
		if( isset( $this->ldap_config['LDAP_VERSION'] ) ) {
			ldap_set_option( $this->conn, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_config['LDAP_VERSION'] );
		}
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL) {
		return false;
	}

	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @return  mixed
	 */
	public function get_user() {
		return new Auth_Dummy_User_Model();
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
		$user_dn = $this->get_dn_for_user( $username );
		if( $user_dn === false ) {
			Kohana::log( 'debug', 'LDAP: User not found: '.$username );
			return false;
		}

		/* Try to bind as user to authenticate */
		if( !$this->bind() ) {
			Kohana::log( 'notice', 'LDAP: Authentication failed for '.$user_dn );
			return false;
		}

		return false;
	}

	private function get_dn_for_user( $username ) {
		$filter = sprintf( '(&(%s=%s)(%s))', 'uid', $username, '' ); /* FIXME */
	}

	private function bind( $dn = false, $password = false ) {
		if( $dn === false ) {
			return ldap_bind( $this->conn ); /* FIXME: Allow non-anonymous bind */
		} else {
			return ldap_bind( $this->conn, $dn, $password );
		}
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
