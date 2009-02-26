<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * User Model
 * This model uses ORM and regular db->query()
 *
 */
class User_Model extends Auth_User_Model {

	/**
	 * takes care of setting session variables etc
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
	*	@name	user_validate_edit
	*	@desc 	Validate input when editing user
	*
	*/
	public function user_validate_edit(array & $array, $save = FALSE)
	{
#		echo Kohana::debug($array);
#		die();
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('realname', 'required', 'length[3,50]')
			->add_rules('email', 'required', 'length[4,127]', 'valid::email')
			#->add_rules('password', 'required', 'length[5,42]')
			#->add_rules('password_confirm', 'matches[password]')
			;

		return ORM::validate($array, $save);
	}
}
