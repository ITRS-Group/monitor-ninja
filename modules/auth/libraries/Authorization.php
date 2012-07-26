<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Authorization
 * @author     
 * @copyright  
 * @license    
 */
class Authorization_Core {
	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory()
	{
		$config = Op5Config::instance()->getConfig('auth_groups');
		return new self( $config );
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
	
	
	private $config = false;
	
	public function __construct($config)
	{
		$this->config = $config;
	}
	
	/**
	 * Authorizes user. Fill in authorization points for the user given
	 * the users groups.
	 *
	 * Returns true if the user is a member of one or more groups in the
	 * monitor system.
	 *
	 * @param   $user   User_Model  The user to authorize
	 * @return          boolean     If the user is authorized
	 */
	public function authorize( $user ) {
		/* Fetch groups */
		$groups   = $user->groups;

		/* Also allow the per-user-group */
		$groups[] = 'user_' . $user->username;
		
		Kohana::log( 'debug', "Authorization: Got groups:");
		foreach( $groups as $group ) {
			Kohana::log( 'debug', "Authorization: group: " . $group);
		}

		$authorized = false;	

		/* Fetch the name column as an array from the result */
		$auth_data = array();
		foreach( $groups as $group ) {
			if( isset( $this->config->{$group} ) ) {
				$authorized = true;
				foreach( $this->config->{$group} as $perm ) {
					$auth_data[ $perm ] = true;
				}
			}
		}
		
		foreach( $auth_data as $perm => $val ) {
			Kohana::log( 'debug', "Authorization: permission: " . $perm);
		}
		
		/* Store as auth_data */
		$user->auth_data = $auth_data;
		
		return $authorized;
	}
}
