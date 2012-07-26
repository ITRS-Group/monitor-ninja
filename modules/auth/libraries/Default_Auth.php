<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class Default_Auth_Core extends Auth_Core {

	public $config;

	public function __construct( $config ) {
		$this->config = $config;
		$this->db     = Database::instance();
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
		if (empty($username) || empty($password))
			return false;
		
		$userdata = $this->authenticate_user( $username, $password );
		if( $userdata === false ) {
			Kohana::log( 'debug', 'Default_Auth: Authentication of '.$username.' failed' );
			return false;
		}
		
		/* username shuold be part of the user object, but is only the key in auth_users.json */
		$userdata['username'] = $username;

		$user = new Auth_Default_User_Model( $userdata );
		$this->setuser( $user );
		
		return $user;
	}
	
	/***************************** Authentication ****************************/
	
	/**
	 * Authenticate user, and return it's row from the database. Return false 
	 * if authentication failed
	 *
	 * @param   string   username of the user
	 * @param   string   password entered by the user
	 * @return  array    database result from the user table, or false
	 */
	
	private function authenticate_user( $username, $password ) {
		$users = Op5Config::instance()->getConfig('auth_users');
	
		if( !isset( $users->{$username} ) ) {
			return false;
		}
		
		$user = $users->{$username};
		if (ninja_auth::valid_password($password, $user->password, $user->password_algo) === true) { /* FIXME */
			return get_object_vars($user);
		}
		return false;
	}
} // End Auth
