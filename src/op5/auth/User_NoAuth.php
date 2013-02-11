<?php

require_once('op5/auth/User.php');

class op5User_NoAuth extends op5User {

	public function __construct() {
		$this->username = 'notauthenticated';
		$this->realname = 'Not Logged in';
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
