<?php

require_once(__DIR__.'/User.php');

class op5User_NoAuth extends op5User {
	public function __construct() {
		$this->username = 'notauthenticated';
		$this->realname = 'Not Logged in';
	}

	/**
	* Returns whether user is authorized for $auth_point.
	* This user has no rights and always returns false
	*
	* @param $auth_point string
	* @return boolean
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

	/**
	 * Determines whether user is authorized for an object
	 *
	 * @param $object_type string
	 * @param $object_definition
	 * @param $case_sensitivity boolean
	 * @return boolean
	 **/
	public function authorized_for_object($object_type, $object_definition, $case_sensitivity=true)
	{
		return false;
	}

	/**
	 * Get "users" contact groups
	 *
	 * @return mixed
	 **/
	public function get_contact_groups()
	{
		return false;
	}
} // End Auth Guest User Model
