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
	
	
	protected function setuser( $user ) {
		$this->user = $user;
		$sess = Session::instance();
		$sess->set( $this->config['session_key'], $user );
		/* Nacoma hack */
		$sess->set( 'nacoma_user', $user->username );
		$sess->set( 'nacoma_auth', array_filter( $user->auth_data ) );
	}
} // End Auth
