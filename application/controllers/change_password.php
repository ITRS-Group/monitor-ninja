<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Password change controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Change_Password_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->template->content = $this->add_view('change_password/change_password');
		$this->template->disable_refresh = true;
		$this->template->title = _('Configuration » Change password');
	}

	public function index()
	{
		$this->template->content->status_msg = '';
	}

	public function change_password()
	{
		$post = Validation::factory($_POST);
		$post->add_rules('*', 'required');
		$current_password = $this->input->post('current_password', false);
		$new_password = $this->input->post('new_password', false);
		$new_password2 = $this->input->post('confirm_password', false);
		if (strlen($new_password) < 5 || strlen($new_password2) < 5)
		{
			$this->template->content->status_msg = _('The password must be at least 5 chars long.');
		}
		elseif ($new_password == $new_password2)
		{
			$user = Auth::instance()->get_user();
			if ($user->password == ninja_auth::hash_password($current_password))
			{
				$user->password = User_Model::update_password($user->username, $new_password);
				User_Model::save_user($user);
				$this->template->content->status_msg = _('The password has been changed.');
			}
			else
				$this->template->content->status_msg = _('You entered incorrect current password.');
		}
		else
		{
			$this->template->content->status_msg = _('Passwords do not match.');
		}
	}
}
