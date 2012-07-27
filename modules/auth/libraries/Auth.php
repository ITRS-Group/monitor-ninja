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
	
	private static $session_key = false;
	

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		$config = Op5Config::instance()->getConfig('auth');
		self::$session_key = $config->session_key;
		
		$drivers = array();
		foreach( $config as $name => $driverconf ) {
			if( isset( $driverconf->driver ) ) {
				$drivers[ $name ] = $driverconf->driver;
			}
		}

		if( count( $drivers ) == 0 ) {
			throw new Exception( 'No authentication driver specified' );
		}
		if( count( $drivers ) > 1 ) {
			return new Multi_Auth( $drivers, $config );
		}
		
		/* Only a single one left... */
		reset( $drivers );
		list( $drivername, $driver ) = each( $drivers );
		
		$class = $driver . '_Auth';
		return new $class( $config->{$drivername} );
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
	public function logged_in($role = NULL)
	{
		return $this->get_user()->logged_in(); /* FIXME: role */
	}

	/**
	 * Returns the currently logged in user, or NoAuth user.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		if( $this->user === false ) {
			$this->user = Session::instance()->get( self::$session_key );
		}
		if( $this->user === false ) {
			return new Auth_NoAuth_User_Model();
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
	public function logout($destroy = FALSE)
	{
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
	public function authorized_for( $authorization_point )
	{
		$user = $this->get_user();
		if( $user === false ) {
			return false;
		}

		if( $user->authorized_for( $authorization_point ) ) {
			Kohana::log( 'debug', 'Auth::authorized_for: Using long tag' ); /* FIXME: Remove */
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
	public function get_authentication_methods()
	{
		return false;
	}
	
	
	
	protected function setuser( $user )
	{
		/* Authorize user */
		if( !Authorization::instance()->authorize( $user ) ) {
			return false;
		}
		
		$this->user = $user;
		$sess = Session::instance();
		$sess->set( self::$session_key, $user );
		
		
		/* Nacoma hack */
		$nacoma_auth = array();
		foreach ($user->auth_data as $key => $value) {
			$nacoma_auth['authorized_for_'.$key] = $value;
		}
		$sess->set( 'nacoma_user', $user->username );
		$sess->set( 'nacoma_auth', array_filter( $nacoma_auth ) );
		
		return true;
	}
} // End Auth
