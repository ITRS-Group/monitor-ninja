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
class Change_Password_Controller extends Ninja_Controller {
	/**
	 * Action to show the change password page, and handle change if POST
	 */
	public function index()
	{
		$this->_verify_access('monitor.system.users.password:update');

		$this->template->content = new View('change_password');
		$this->template->title = _('Configuration » Change password');

		$this->template->disable_refresh = true;
		$this->template->toolbar = new Toolbar_Controller( _("My Account"), _("Change Password") );

		$this->template->toolbar->info(
			html::href(
				$this->linkprovider->get_url('user'),
				_('Account settings'),
				array(
					'title' => _('Account settings')
				)
			)
		);

		if (Auth::instance()->authorized_for('access_rights')) {
			$this->template->toolbar->info(
				html::href(
					$this->linkprovider->get_url('user', 'menu_edit'),
					_('Edit user menu'),
					array(
						'title' => _('Edit user menu')
					)
				)
			);
		}

		$this->template->content->successful = false;
		$this->template->content->message = null;

		if(!$_POST) return;

		$post = Validation::factory($_POST);
		$post->add_rules('*', 'required');

		$current_password = $this->input->post('current_password', false);
		$new_password = $this->input->post('new_password', false);
		$new_password2 = $this->input->post('confirm_password', false);

		if ($new_password != $new_password2) {
			$this->template->content->message = new ErrorNotice_Model(_('New password did not match repeated password.'));
			return;
		}

		$auth = Auth::instance();

		// This looks odd, but user instances instantiated by Auth
		// are not storable, they are only to be used ephemerally
		// So get a proper storable instance of the user from the ORM.
		$authuser = $auth->get_user();
		$user = UserPool_Model::all()->reduce_by('username', $authuser->get_username(), '=')->one();

		if ($auth->verify_password($authuser, $current_password)) {
			try {
				$user->set_password($new_password);
				$user->save();
				$this->template->content->successful = true;
				$this->template->content->message = new SuccessNotice_Model(_('Password changed successfully'));
			} catch (Exception $e) {
				$this->template->content->message = new ErrorNotice_Model($e->getMessage());
			}
		} else {
			$this->template->content->message = new ErrorNotice_Model(_('You entered incorrect current password.'));
		}

	}
}
