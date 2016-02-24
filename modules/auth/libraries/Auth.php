<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/auth/Auth.php');

/**
 * User authentication and authorization library.
 */
class Auth {
	/**
	 * Used to override instance, to break exception loop
	 */
	private static $fake_instance = false;

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory(array $config = array())
	{
		Op5Auth::factory($config);
		return new self();
	}

	/**
	 * @param $rights_to_exclude array
	 * @return array
	 */
	public static function get_groups_without_rights(array $rights_to_exclude)
	{
		$groups = Op5Config::instance()->getConfig('auth_groups');
		foreach($groups as $group => $rights) {
			if(array_intersect($rights_to_exclude, $rights)) {
				unset($groups[$group]);
			}
		}
		return $groups;
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		/*
		 * Since op5auth expectes the sesssion to be started, we should make sure
		 * we have started the session. That is done by making sure the Session
		 * object is instanced, so just load the instance, and we should be fine to
		 * use op5auth later.
		 *
		 * This is mostly a workaround since op5auth doesn't do session handling on
		 * its own (yet?)
		 */
		Session::instance();

		if (self::$fake_instance !== false) return self::$fake_instance;
		// Load the Auth instance
		try {
			$instance = new Auth($config);
		}
		catch( Exception $e ) {
			self::disable_auth_subsystem();
			throw $e;
		}
		return $instance;
	}

	/**
	 * @param $config array Optional method config
	 */
	public function __construct(array $config = array())
	{
		Op5Auth::instance($config);
	}

	/**
	 * Check if there is an user logged in
	 *
	 * @return  boolean
	 */
	public function logged_in()
	{
		return op5auth::instance()->logged_in();
	}

	/**
	 * Returns the currently logged in user, or NoAuth user.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		return op5auth::instance()->get_user();
	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   $username     string    username to log in
	 * @param   $password     string    password to check against
	 * @param   $auth_method  mixed     Which auth method to use
	 * @return  boolean                 True on success
	 */
	public function login($username, $password, $auth_method = false)
	{
		return op5auth::instance()->login( $username, $password, $auth_method );
	}

	/**
	 * Verify password for a logged in user.
	 *
	 * Usable for form validation of critical user data, for example validate a
	 * password change.
	 *
	 * This method doesn't use APC
	 *
	 * @param $user     User_Model User object to verify
	 * @param $password string  Password to test
	 * @return          boolean true if password is ok
	 */
	public function verify_password( $user, $password )
	{
		return op5auth::instance()->verify_password( $user, $password );
	}

	/**
	 * Returns true if current session has access for a given authorization point
	 *
	 * @param   $authorization_point string   authorization point
	 * @return  boolean  true if access
	 */
	public function authorized_for($authorization_point)
	{
		return op5auth::instance()->authorized_for($authorization_point);
	}

	/**
	 * Returns name of default authentication method.
	 *
	 * @return 	string 	default authentication method
	 *
	 */
	public function get_default_auth()
	{
		return op5auth::instance()->get_default_auth();
	}

	/**
	 * Take an User_Model object, and force the auth module to recognize it as the
	 * currently logged in user
	 */
	public function force_user(User_Model $user)
	{
		return op5auth::instance()->force_user($user);
	}

	/**
	 * Register noauth as auth subsystem, so we can't login, logout or anything.
	 *
	 * This effectivly reduces possibilities for auth-related errors. Because lot
	 * of things depend on auth, even when rendering, this is needed to be loaded
	 * when displaying error pages.
	 */
	public static function disable_auth_subsystem() {
		self::$fake_instance = new Auth_NoAuth();
	}
} // End Auth


/**
 * This class is just to fill in as Auth if exception if thrown in factory.
 *
 * When showing an error page (as from exception in factory method), the instance
 * needs to be set, so not a new exception will be thrown when rendering the error
 * page
 */
class Auth_NoAuth extends Auth {

	/**
	 * @param $config array Optional method config
	 */
	public function __construct($config = NULL)
	{
	}

	/**
	 * @param $role mixed This is something, remove?
	 */
	public function logged_in($role = NULL)
	{
		return false;
	}

	public function get_user()
	{
		return new User_NoAuth_Model();
	}

	/**
         * Attempt to log in a user by using an ORM object and plain-text password.
         *
         * @param   $username     string    username to log in
         * @param   $password     string    password to check against
         * @param   $auth_method  mixed     Which auth method to use
         * @return  boolean                 True on success
         */
	public function login($username, $password, $auth_method = false)
	{
		return false;
	}

	public function verify_password( $user, $password )
	{
		return false;
	}

	/**
         * Returns true if current session has access for a given authorization point
         *
         * @param   $authorization_point string   authorization point
         * @return  boolean  true if access
         */
	public function authorized_for( $authorization_point )
	{
		return false;
	}
}
