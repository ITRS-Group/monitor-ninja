<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Guest_User_Model extends Auth_User_Model {

	protected $fields = array(
		'username'   => 'guest',
		'commonname' => 'Guest'
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

} // End Auth Guest User Model
