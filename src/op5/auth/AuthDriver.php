<?php

require_once(__DIR__.'/../log.php');

/**
 * User authentication and authorization library.
 *
 * @package    Auth
 */
abstract class op5AuthDriver {

	/**
	 * Configuration for the module
	 * @var $config array
	 **/
	protected $config = array();

	/**
	 * Metadata for the module
	 * 
	 * This array contains information about the driver itself, and about its
	 * capabilities, and what it needs.
	 * 
	 * @var $metadata array
	 **/
	protected static $metadata = array();

	/**
	 * Stores a reference to the op5Log object
	 * @var $log object
	 **/
	protected $log = false;

	/**
	 * Create an instance of auth log and set config.
	 *
	 * @param $config array
	 * @return void
	 **/
	public final function __construct($config)
	{
		$this->log = op5Log::instance('auth');
		$this->config = $config;
	}

	/**
	 * Attempt to log in a user by username and password.
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @return  op5User  User object, or false
	 */
	public function login($username, $password)
	{
		return false;
	}

	/**
	 * Attempt to log in a user by static configuration, or external infromation.
	 *
	 * Useful for example for HTTP-auth.
	 *
	 * @return  op5User  User object, or false
	 */
	public function auto_login()
	{
		return false;
	}

	/**
	 * Log out a user, if
	 *
	 * @param   $user    op5User  driver-specific logout-routine, if driver requires.
	 */
	public function logout($user)
	{
	}

	/**
	 * Update password for a user.
	 *
	 * @param $user     op5User User object
	 * @param $password string  Password to set
	 * @return          boolean True if successful, False if error
	 */
	public function update_password($user, $password)
	{
		return false;
	}

	/**
	 * Given a list of groups, return an associative array with groups as keys and a boolean
	 * if group is available in the backend. If it is unknown if the user is available, the field
	 * is unset.
	 *
	 * If driver supports multiple backends, the extra auth_method can be set to the backend.
	 * Otherwise, a superset is should given of all backends
	 *
	 * @param $grouplist array	List of groups to check
	 * @return array			Associative array of the groups in $grouplist as keys, boolean as values
	 */
	public function groups_available(array $grouplist)
	{
		return array();
	}

	/**
	 * Given a username, return a list of it's groups. Useful when giving permissions to a user.
	 *
	 * @param $username string	User to search for
	 * @return array			A list of groups, or false if not possible
	 */
	public function groups_for_user($username)
	{
		return false;
	}
	
	/**
	 * Get the metadata from the driver.
	 * 
	 * If given an attribute, return only that field in the metadata array.
	 * Otherwise, the entire array
	 */
	public static function get_metadata($field = false) {
		if( $field === false ) {
			return static::$metadata;
		}
		if( !isset(static::$metadata[$field]) ) {
			return false;
		}
		return static::$metadata[$field];
	}
} // End Auth
