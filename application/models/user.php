<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * This model uses ORM and regular db->query()
 */
class User_Model extends Auth_User_Model {

	/**
	 * Takes care of setting session variables etc
	 */
	public function complete_login($user_data)
	{
		if (!$this->session->get(Kohana::config('auth.session_key'), false)) {
			url::redirect(Kohana::config('routes._default'));
		}

		// Regenerate session (prevents session fixation attacks)
	#	$this->session->regenerate();

		# remove password from session data
		unset($user_data->password);

		# save user object data to session
		$this->session->set('user_data', $user_data);

		# set logged_in to current timestamp if db
		$auth_type = Kohana::config('auth.driver');
		if ($auth_type == 'db') {
			$this->db->query('UPDATE user SET logged_in = '.time().' WHERE id='.(int)$user_data->id);
		}

		$requested_uri = Session::instance()->get('requested_uri', false);

		# fetch nagios access rights for user
		$system = new System_Model();
		$access = $system->nagios_access(Auth::instance()->get_user()->username);
		Session::instance()->set('nagios_access', $access);

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
}
