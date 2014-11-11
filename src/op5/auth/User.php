<?php

class op5User {

	/* Only to be accessed from op5Auth */
	public $fields = array(
		'username'  => false,
		'realname'  => false,
		'email'     => false,
	);

	public function __set($key, $value)
	{
		$this->fields[$key] = $value;
	}

	public function __get($key)
	{
		return $this->fields[$key];
	}
	
	public function __isset($key)
	{
		return isset( $this->fields[$key] );
	}
	
	public function __unset($key)
	{
		unset( $this->fields[$key] );
	}


	public function __construct( $fields ) {
		$this->fields    = $fields;
	}
	
	/**
	 * Returns if a user is authorized for a certain authorization point
	 *
	 * @param 	string 		authorization point
	 * @return 	boolean 	true if user has access to that authorization point
	 */
	public function authorized_for($auth_point)
	{
		return isset( $this->auth_data[ $auth_point ] ) ? $this->auth_data[ $auth_point ] : false;
	}

	/**
	 * Updates the password of the user.
	 *
	 * @param  string    new password
	 * @return boolean
	 */
	public function change_password( $password )
	{
		return false;
	}

	/**
	 * Returns true if logged in
	 *
	 * @return  boolean   always true (normal users are logged in, notauth overrides)
	 */
	public function logged_in()
	{
		return true;
	}

} // End Auth User Model
