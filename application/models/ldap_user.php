<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * This model uses ORM and regular db->query()
 *
 * This model controls variables (and a few methods) for the
 * users objects (and thus also database tables).
 * Authentication stuff should *not* end up here.
 */
class LDAP_User_Model extends Auth_User_Model {
	/** The name of the authorization table */
	public static $auth_table = 'ninja_user_authorization';

	/**
	 * Update a user's password
	 *
	 * This only sets the password in the htpasswd file.
	 *
	 * @param $username Name of user to have it's password updated
	 * @param $password The password to set
	 * @return Hashed password
	 */
	public function update_password($username, $password)
	{
		return false;
	}

	/**
	 * Write user obj to database.
	 *
	 * @param $user_obj The user object to save
	 */
	public function save_user($user_obj)
	{
		/* We don't write to LDAP */
	}

	/**
	 * Takes care of setting session variables etc
	 *
	 * @returns TRUE if everything was OK, or a string you might want to redirect the user to
	 */
	public static function complete_login()
	{
		if (!Session::instance()->get(Kohana::config('auth.session_key'), false)) {
			return Kohana::config('routes._default');
		}

		# cache nagios_access session information
		System_Model::nagios_access();

		# Check that user has access to view some objects
		# or logout with a message
		$access = Session::instance()->get('nagios_access', false);
		if (empty($access)) {
			# not any authorized_for_ variables set so lets check
			# if user is authorized for any objects
			$auth = new Nagios_auth_Model();
			$hosts = $auth->get_authorized_hosts();

			$redirect = false;
			if (empty($hosts)) {
				$services = $auth->get_authorized_services();
				if (empty($services)) {
					$redirect = true;
				}
			}

			if ($redirect !== false) {
				$translate = zend::instance('Registry')->get('Zend_Translate');
				Session::instance()->set_flash('error_msg',
					$translate->_("You have been denied access since you aren't authorized for any objects."));
				return 'default/show_login';
			}
		}
		return true;
	}

	/**
	 * For LDAP, no username is avalible, because LDAP doesn't support registration
	 *
	 * @param $id A username
	 * @returns Whether the username is already taken by another user
	 */
	public function username_available($id) {
		return false;
	}

	/**
	 * Validate input when editing user
	 */
	public function user_validate_edit(array & $array, $save = FALSE)
	{
#		echo Kohana::debug($array);
#		die();
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('realname', 'required', 'length[3,50]')
			#->add_rules('password', 'required', 'length[5,42]')
			#->add_rules('password_confirm', 'matches[password]')
			;

		return ORM::validate($array, $save);
	}

	/**
	 * Takes care of setting a user as logged out
	 * and destroying the session
	 */
	public static function logout_user()
	{
		Session::instance()->destroy();
		return true;
	}

	/**
	*	Check if user exists and if so we pass the supplied
	* 	$options data to Nninja_user_authorization_Model to let
	* 	it decide if to update or insert.
	*/
	public static function user_auth_data($username=false, $options=false)
	{
		if (empty($username) || empty($options))
			return false;
		$username = trim($username);
		$result = false;

		$auth_fields = Ninja_user_authorization_Model::$auth_fields;

		# authorization data fields and order
		$auth_options = false;

		# check that we have the correct number of auth options
		# return false otherwise
		if (count($options) != count($auth_fields)) {
			return false;
		}

		# merge the two arrays into one with auth_fields as key
		for ($i=0;$i<count($options);$i++) {
			$auth_options[$auth_fields[$i]] = $options[$i];
		}

		return array("No user id... What happens now? FIXME");
	}

	/**
	*	Fetch userinfo based on login
	*	Will return first user with login role found (for CLI access)
	* 	if username is set to false.
	*/
	public static function get_user($username=false)
	{
		return false;
	}

	/**
	 *	Fetch an array of all usernames in users table
	 *	@return array of usernames or false on error
	 *
	 *  FIXME: This function is faulty by design. Systems can handle thousands of users...
	 */
	public static function get_all_usernames()
	{
		return array();
	}

	/**
	 *	Fetch all users that aren't suthorized_for_all_hosts,
	 *	i.e. limited users
	 *
	 *  FIXME: This function is faulty by design. Systems can handle thousands of users...
	 */
	public function get_limited_users()
	{
		return array();
	}

	/**
	 * Add a user to db
	 * A login role will be created for new users
	 * Checks are made that the user doesn't exist
	 *
 	 * Isn't supported by LDAP
	 */
	public function add_user($data=false)
	{
		return false;
	}
}
