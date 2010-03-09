<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * This model uses ORM and regular db->query()
 *
 * This model controls variables (and a few methods) for the
 * users objects (and thus also database tables).
 * Authentication stuff should *not* end up here.
 */
class User_Model extends Auth_User_Model {
	public static $auth_table = 'ninja_user_authorization';

	public function __set($key, $value)
	{
		if ($key === 'password')
		{
			// Use Auth to hash the password
			$value = ninja_auth::hash_password($value);
		}

		ORM::__set($key, $value);
	}

	/**
	 * Takes care of setting session variables etc
	 */
	public function complete_login($user_data)
	{
		if (!$this->session->get(Kohana::config('auth.session_key'), false)) {
			url::redirect(Kohana::config('routes._default'));
		}

		# save user object data to session
		#$this->session->set('user_data', $user_data);

		# set logged_in to current timestamp if db
		$auth_type = Kohana::config('auth.driver');
		if ($auth_type == 'db' || $auth_type === 'Ninja' || $auth_type === 'LDAP') {
			$user = ORM::factory('user', Auth::instance()->get_user()->id);
			$user->last_login = time();
			$user->save();
		}

		$requested_uri = Session::instance()->get('requested_uri', false);

		# check if new authorization data is available in cgi.cfg
		# this enables incremental import
		Cli_Controller::insert_user_data();

		# cache nagios_access session information
		System_Model::nagios_access();

		# make sure we don't end up in infinite loop
		# if user managed to request show_login
		if ($requested_uri == Kohana::config('routes.log_in_form')) {
			$requested_uri = Kohana::config('routes.logged_in_default');
		}
		if ($requested_uri !== false) {
			# remove 'requested_uri' from session
			$this->session->delete('requested_uri');
			url::redirect($requested_uri);
		} else {
			# we have no requested uri
			# using logged_in_default from routes config
			#die('going to default');
			url::redirect(Kohana::config('routes.logged_in_default'));
		}
	}

	public function username_available($id) {
		return ! $this->username_exists($id);
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
	public function logout_user()
	{
		$auth_type = Kohana::config('auth.driver');
		if ($auth_type == 'db') {
			$this->db->query('UPDATE user SET logged_in = 0 WHERE id='.(int)user::session('id'));

			# reset users logged_in value when they have been logged in
			# more than sesssion.expiration (default 7200 sec)
			$session_length = Kohana::config('session.expiration');
			$this->db->query('UPDATE user SET logged_in = 0 WHERE logged_in!=0 AND logged_in < '.(time()-$session_length));
		}
		$this->session->destroy();
		return true;
	}

	/**
	*	Check if user exists and if so we pass the supplied
	* 	$options data to Nninja_user_authorization_Model to let
	* 	it decide if to update or insert.
	*/
	public function user_auth_data($username=false, $options=false)
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

		$db = new Database();
		if (!$db->table_exists(self::$auth_table)) {
			# make sure we have the ninja_user_authorization table
			self::create_auth_table();
		}

		$user = ORM::factory('user')->where('username', $username)->find();
		if ($user->loaded) {
			# user found in db
			# does authorization data exist for this user?
			$result = ninja_user_authorization_Model::insert_user_auth_data($user->id, $auth_options);
		} else {
			# this should never happen
			$result = "Tried to save authorization data for a non existing user.\n";
		}
		return array($result);
	}

	/**
	* Truncate ninja_user_authentication table
	*/
	public function truncate_auth_data()
	{
		$db = new Database();
		$sql = "TRUNCATE ninja_user_authorization";
		$db->query($sql);
	}

	/**
	*	Create the ninja_user_authorization table if not exists
	*
	*/
	public static function create_auth_table()
	{
		$db = new Database();
		$sql = "CREATE TABLE IF NOT EXISTS `".self::$auth_table."` ( ".
					"`id` int(11) NOT NULL auto_increment, ".
					"`user_id` int(11) NOT NULL, ".
					"`system_information` int(11) NOT NULL default '0', ".
					"`configuration_information` int(11) NOT NULL default '0', ".
					"`system_commands` int(11) NOT NULL default '0', ".
					"`all_services` int(11) NOT NULL default '0', ".
					"`all_hosts` int(11) NOT NULL default '0', ".
					"`all_service_commands` int(11) NOT NULL default '0', ".
					"`all_host_commands` int(11) NOT NULL default '0', ".
					"PRIMARY KEY  (`id`), ".
					"KEY `user_id` (`user_id`));";
		$db->query($sql);
	}

	/**
	*	Fetch userinfo based on login
	*	Will return first user with login role found (for CLI access)
	* 	if username is set to false.
	*/
	public function get_user($username=false)
	{
		if (!empty($username)) {
			$username = trim($username);
			$user = ORM::factory('user')->where('username', $username)->find();
		} else {
			$user = ORM::factory('user')->with('role:login')->find();
		}
		return $user->loaded ? $user : false;
	}

	/**
	*	Fetch an array of all usernames in users table
	*	@return array of usernames or false on error
	*/
	public function get_all_usernames()
	{
		$user_res = ORM::factory('user')->find_all();

		if (count($user_res)==0) {
			return false;
		}
		$users = false;
		foreach ($user_res as $user) {
			$users[] = $user->username;
		}
		return $users;
	}
}
