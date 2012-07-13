<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_User_Model {

	protected $fields = array(
		'username'  => false,
		'realname'  => false,
		'email'     => false,
		'auth_data' => array(
		    'authorized_for_system_information'        => false,
		    'authorized_for_configuration_information' => false,
		    'authorized_for_system_commands'           => false,
		    'authorized_for_all_services'              => false,
		    'authorized_for_all_hosts'                 => false,
		    'authorized_for_all_service_commands'      => false,
		    'authorized_for_all_host_commands'         => false,
		)
	);

	public function __set($key, $value)
	{
		$this->fields[$key] = $value;
	}

	public function __get($key)
	{
		return $this->fields[$key];
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

} // End Auth User Model
