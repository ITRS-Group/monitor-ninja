<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once( 'auth/Op5Auth.php' );
require_once( 'auth/Op5User_NoAuth.php' );
require_once( 'auth/Op5User_AlwaysAuth.php' );

/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class Auth_Core {
	private $op5auth = false; /* Op5Auth object */

	private static $instance = false;

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		return new self();
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		// Load the Auth instance
		if (empty(self::$instance)) {
			try {
				self::$instance = self::factory($config);
			}
			catch( Exception $e ) {
				self::$instance = new Auth_NoAuth_Core();
				throw $e;
			}
		}

		return self::$instance;
	}

	public function __construct( $config = NULL )
	{
		$this->op5auth = Op5Auth::factory( $config );
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		return $this->op5auth->logged_in();
	}

	/**
	 * Returns the currently logged in user, or NoAuth user.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		$user = $this->op5auth->get_user();
		Kohana::log( 'debug', 'User: ' . var_export( $user, true ) );
		return $user;
	}
	
	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   boolean  enable auto-login
	 * @return  boolean	True on success
	 */
	public function login($username, $password, $auth_method = false)
	{
		$res = $this->op5auth->login( $username, $password, $auth_method );
		Kohana::log( 'debug', 'Login: ' . var_export( $res, true ) );
		return $res;
	}
	
	/**
	 * Attempt to automatically log a user in.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		return false;
	}

	/**
	 * Force a login for a specific username.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($username)
	{
		return false;
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  completely destroy the session
	 * @return  boolean
	 */
	public function logout($destroy = FALSE)
	{
		return $this->op5auth->logout();
	}

	/**
	 * Verify password for a logged in user.
	 *
	 * Usable for form validation of critical user data, for example validate a
	 * password change.
	 *
	 * This method doesn't use APC
	 *
	 * @param $user     Op5User User object to verify
	 * @param $password string  Password to test
	 * @return          boolean true if password is ok
	 */
	public function verify_password( $user, $password )
	{
		return $this->op5auth->verify_password( $user, $password );
	}

	/**
	 * Update password for a given user.
	 *
	 * @param $user     Op5User User object to verify
	 * @param $password string  New password
	 * @return          boolean true if password is ok
	 */
	public function update_password( $user, $password )
	{
		return $this->op5auth->update_password( $user, $password );
	}

	/**
	 * Returns true if current session has access for a given authorization point
	 *
	 * @param   string   authorization point
     * @return  boolean  true if access
	 */
	public function authorized_for( $authorization_point )
	{
		return $this->op5auth->authorized_for( $authorization_point );
	}

	/**
	 * Returns an array of authentication methods.
	 *
	 * @return  array  list of authentication methods, or false if only a single
	 *                 is avalible
	 */
	public function get_authentication_methods()
	{
		return $this->op5auth->get_authentication_methods();
	}

	/**
	 * Returns name of default authentication method.
	 *
	 * @return 	string 	default authentication method
	 *
	 */
	public function get_default_auth()
	{
		return $this->op5auth->get_default_auth();
	}

	/**
	 * Take an op5User object, and force the auth module to recognize it as the
	 * currently logged in user
	 */
	public function force_user($user)
	{
		return $this->op5auth->force_user($user);
	}
} // End Auth


/**
 * This class is just to fill in as Auth_Core if exception if thrown in factory.
 *
 * When showing an error page (as from exception in factory method), the instance
 * needs to be set, so not a new exception will be thrown when rendering the error
 * page
 */
class Auth_NoAuth_Core extends Auth_Core {

	public function __construct($config = NULL)
	{
	}
	
	public function logged_in($role = NULL)
	{
		return false;
	}
	
	public function get_user()
	{
		return new Op5User_NoAuth();
	}
	
	public function login($username, $password, $auth_method = false)
	{
		return false;
	}
	
	public function logout()
	{
		return false;
	}

	public function verify_password( $user, $password )
	{
		return false;
	}

	public function update_password( $user, $password )
	{
		return false;
	}
	
	public function authorized_for( $authorization_point )
	{
		return false;
	}
	
	public function get_authentication_methods()
	{
		return false;
	}
}
