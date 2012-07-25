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
	public static function factory($config = array())
	{
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
	
	
	private $db = false;
	
	public function __construct($config)
	{
		$this->db = Database::instance(); 	
	}
	
	public function authorize( $user ) {
		/* Fetch groups */
		$groups   = $user->groups;

		/* Also allow the per-user-group */
		$groups[] = 'user_' . $user->username;
		
		Kohana::log( 'debug', "Authorization: Got groups:");
		foreach( $groups as $group ) {
			Kohana::log( 'debug', "Authorization: group: " . $group);
		}
		
		/* Build IN(xxx)-string */
		$groupstring = "'" . implode( "','", $groups ) . "'"; /* FIXME: SQL Escape */
		
		/* Fetch all permissions given a list of groups. Do grouping in SQL, to make it to make it possible for the database engine to optimize */
		$perms_res = $this->db->query( 'SELECT p.name FROM auth_group_permission AS p LEFT JOIN auth_groups AS g ON g.id = p.group WHERE g.name IN ('.$groupstring.') GROUP BY p.name' );
		
		/* Fetch the name column as an array from the result */
		$auth_data = array();
		foreach( $perms_res as $perm ) {
			$auth_data[ $perm->name ] = true;
		}
		
		/* Store as auth_data */
		$user->auth_data = $auth_data;
	}
}
