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
	/**
	 * The different db fields that contains authorization info
	 */
	public static $auth_fields = array(
				'system_information',
				'configuration_information',
				'system_commands',
				'all_services',
				'all_hosts',
				'all_service_commands',
				'all_host_commands'
			);

	public function __construct( $config ) {
		$this->config = $config;
		$this->db     = Database::instance();

		Kohana::log( 'debug', var_export( $_SESSION, true ) );
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
		
		$auth_data = $this->authorize_user( $userdata );

		$user = new Auth_DB_User_Model( $userdata + array( 'auth_data' => $auth_data ) );
		$this->setuser( $user );
		
		return $user;
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
	
	/***************************** Authorization *****************************/
	
	/**
	 * Finish up the login, do authorization checks.
	 *
	 * @param   array   user data, which represents the user from the database.
	 * @return  array   list of permissions. key=authorization point,
	 *                  value = boolean, representing access
	 */
	private function authorize_user( $userdata ) {
		$sql = "SELECT * FROM ninja_user_authorization WHERE user_id=".intval($userdata['id']);
		$res = $this->db->query($sql)->result(false);
		if (count($res)!=0) {
			$auth_fields = self::$auth_fields;
			$auth = $res->current();
			foreach ($auth_fields as $field) {
				if ($auth[$field]) {
					$auth_data['authorized_for_'.$field] = (false != $auth[$field] );
				}
			}
			unset($res);
			return $auth_data;
		}
		return false;
	}
} // End Auth
