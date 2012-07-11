<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_LDAP_User_Model extends Auth_User_Model {

	protected $fields = array(
		'username' => false
	);

	public function __set($key, $value)
	{
		$this->fields[$key] = $value;
	}

	public function __get($key)
	{
		return $this->fields[$key];
	}

	public function __construct( $user_info ) {
		$auth = Auth::instance(); /* Is a LDAP_Auth instance; this code runs... TODO: Nicer way to do this? */
		
		$this->username = $user_info[ $auth->config['LDAP_USERKEY'] ][0];
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
