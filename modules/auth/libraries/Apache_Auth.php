<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class Apache_Auth_Core extends Auth_Core {

	public $config;

	public function __construct( $config ) {
		$this->config = $config;
	}
	
	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @return  mixed
	 */
	public function get_user() {
		return $this->doAuth();
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
		/* Every page contains validation information, so get_user authenticates... */
		return $this->doAuth();
	}
	
	/**
	 * Does authentication. This isn't done in the login function during apache auth,
	 * because authentication is before the login screen, and handled every page load.
	 *
	 * TODO: Cache user credentials and authorization if username doesn't change.
	 *
	 * @return  mixed
	 */
	private function doAuth() {
		/* We let apache handle the authentication, so only username is relevant */
		if( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			return new Auth_NoAuth_User_Model();
		}

		$username = $_SERVER['PHP_AUTH_USER'];
		
		$groups = array(
			/* Make all apache auth users memeber of this group, to grant privileges to all those users */
			'apache_auth_user'
			);

		$user = new Auth_Apache_User_Model( array(
				'username' => $username,
				'groups'   => $groups,
				'realname' => $username, /* We have no clue about realname, so call him/her their username */
				'email'    => ''
			) );
		
		/* Authorize, fix nacoma, and so on... */
		$this->setuser( $user );
			
		return $user;
	}
} // End Auth
