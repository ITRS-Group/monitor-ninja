<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User authentication and authorization library.
 *
 * @package    Auth
 * @author     
 * @copyright  
 * @license    
 */
class DB_Auth_Core extends Auth_Core {

	public $config;

	public function __construct( $config ) {
		$this->config = $config;
		$this->db     = Database::instance();

		/* Say that we have user administration support */
		$this->backend_supports['user_administration'] = true;
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
		if (empty($username) || empty($password))
			return false;
		
		$userdata = $this->authenticate_user( $username, $password );
		if( $userdata === false ) {
			Kohana::log( 'debug', 'DB_Auth: Authentication of '.$username.' failed' );
			return false;
		}
		
		$groups = $this->get_groups( $userdata );
		
		/* FIXME: Resolve groups */

		$user = new Auth_DB_User_Model( $userdata + array( 'groups' => $groups ) );
		$this->setuser( $user );
		
		return $user;
	}
	
	/******************************* Groups **********************************/
	
	/**
	 * Fetch a list of groups for a given user.
	 *
	 * @param    array   an array representing the user data from the database
	 * @return   array   list of group names. Is names to be compatible with other auth modules, like LDAP
	 */
	
	private function get_groups($userdata)
	{
		$group_res = $this->db->query( 'SELECT g.name FROM user_groups ug LEFT JOIN auth_groups g ON g.id=ug.group WHERE ug.user = ?', $userdata['id'] );
		
		$groups = array();
		foreach( $group_res as $group ) {
			$groups[] = $group->name;
		}
		return $groups;
	}
	
	/***************************** Authentication ****************************/
	
	/**
	 * Authenticate user, and return it's row from the database. Return false 
	 * if authentication failed
	 *
	 * @param   string   username of the user
	 * @param   string   password entered by the user
	 * @return  array    database result from the user table, or false
	 */
	
	private function authenticate_user( $username, $password ) {
		$user_res = $this->db->query( 'SELECT * FROM users WHERE username=' . $this->db->escape( $username ) )->result(false);
		$user = $user_res->current();
		if (ninja_auth::valid_password($password, $user['password'], $user['password_algo']) === true) { /* FIXME */
			return $user;
		}
		return false;
	}
} // End Auth
