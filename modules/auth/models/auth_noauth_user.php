<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_NoAuth_User_Model extends Auth_User_Model {

	protected $fields = array(
		'username'   => 'notauthenticated',
		'realname'   => 'Not Logged in'
	);
	
	public function __construct() {
	}
	

	/**
	* @param 	string 		authorization point
	* @return 	boolean 	
	*/
	public function authorized_for($auth_point)
	{
		return false;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return  boolean   always false (never logged in)
	 */
	public function logged_in()
	{
		return false;
	}

} // End Auth Guest User Model
