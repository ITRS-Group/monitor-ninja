<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once( 'auth/Op5Auth.php' );

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
		static $instance;
		
		// Load the Auth instance
		if (empty($instance)) {
			$instance = self::factory($config);
		}

		return $instance;
	}

	public function __construct( $config = NULL ) {
		$this->op5auth = Op5Auth::instance( $config );
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
		return $this->op5auth->logged_in( $role );
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
		return $this->op5auth->logout( $destroy );
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
	 * Returns an array of authentication methods with keys representing the
	 * internal name of the authentication mehtod, and the value is a user
	 * readable name
	 *
	 * @return  array  list of authentication methods, or false if only a single
	 *                 is avalible
	 */
	public function get_authentication_methods()
	{
		return $this->op5auth->get_authentication_methods();
	}
} // End Auth
