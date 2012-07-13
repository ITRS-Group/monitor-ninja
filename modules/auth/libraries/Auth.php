<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
abstract class Auth_Core {

	protected $user = false;
	protected $backend_supports = array();
	

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		$config += Kohana::config('auth');
		$driver = $config['driver'];
		if( is_array( $driver ) ) {
			$driver = 'Multi';
		}
		$class = $driver . '_Auth';
		return new $class( $config );
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

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL) {
		return $this->get_user() !== false; /* FIXME: role */
	}

	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @return  mixed
	 */
	public function get_user() {
		if( $this->user === false ) {
			$this->user = Session::instance()->get( $this->config['session_key'] );
		}
		return $this->user;
	}
	
	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   boolean  enable auto-login
	 * @return  user	 user object or FALSE
	 */
	abstract public function login($username, $password, $auth_method = false);
	
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
	public function logout($destroy = FALSE) {
		$this->user = false;
		Session::instance()->destroy();
		return true;
	}

	/**
	 * Returns true if current session has access for a given authorization point
	 *
	 * @param   string   authorization point
     * @return  boolean  true if access
	 */
	public function authorized_for( $authorization_point ) {
		$user = $this->get_user();
		if( $user === false )
			return false;

		if( $user->authorized_for( $authorization_point ) ) {
			Kohana::log( 'debug', 'Auth::authroized_for: Using long tag' ); /* FIXME: Remove */
			return true;
		}

		/* TODO: autorized_for_: fix short names better than this... */
		if( $user->authorized_for( 'authorized_for_' . $authorization_point ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns an array of authentication methods with keys representing the
	 * internal name of the authentication mehtod, and the value is a user
	 * readable name
	 *
	 * @return  array  list of authentication methods, or false if only a single
	 *                 is avalible
	 */
	public function get_authentication_methods() {
		return false;
	}
	
	/**
	 * Returns true if the backend supports a certain task.
	 *
	 * Tasks avalible:
	 *    groups
	 *    user_administration
	 *    multiple_backends
	 *
	 * @param   string   name of task
	 * @return  boolean  if backend has the support
	 */
	public function support_for( $task ) {
		return in_array( $task, $this->supports );
	}
	
	protected function setuser( $user ) {
		$this->user = $user;
		$sess = Session::instance();
		$sess->set( $this->config['session_key'], $user );
		/* Nacoma hack */
		$sess->set( 'nacoma_user', $user->username );
		$sess->set( 'nacoma_auth', array_filter( $user->auth_data ) );
	}
} // End Auth
