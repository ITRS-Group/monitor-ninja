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

		$messages = array(
			"TO_SHORT" => _('The password must be at least 5 characters long.'),
			"NO_UPDATE" => _('Authentication backend reported that password could not be updated.'),
			"INVALID_CURRENT" => _('You entered incorrect current password.'),
			"NO_MATCH" => _('New password did not match repeated password.'),
			"SUCCESS" => _('Password changed successfully')
		);

		$this->template->content = new View('change_password');
		$this->template->disable_refresh = true;
		$this->template->title = _('Configuration » Change password');

		$this->template->toolbar = new Toolbar_Controller( _("My Account"), _("Change Password") );
		$root = url::base(FALSE) . 'index.php/';
		$links = "";

		$links .= '<li>' . html::icon('profiles') . html::href(url::method('user', null), _("Account settings")) . '</li>';

		if ( Auth::instance()->authorized_for('access_rights') ) {
			$links .= '<li>' . html::icon('eventlog') . html::href(url::method('user', 'menu_edit'), _("Edit user menu")) . '</li>';
		}

		$this->template->toolbar->info("<ul>$links</ul>");

		$this->template->content->successful = false;
		$this->template->content->status_msg = '';

		if( $_POST ) {
			$post = Validation::factory( $_POST );
			$post->add_rules( '*', 'required' );

			$current_password = $this->input->post('current_password', false);
			$new_password = $this->input->post('new_password', false);
			$new_password2 = $this->input->post('confirm_password', false);

			if ( strlen( $new_password ) < 5 || strlen( $new_password2 ) < 5 ) {

				$this->template->content->status_msg = $messages[ "TO_SHORT" ];

			} elseif ( $new_password == $new_password2 ) {

				$auth = Auth::instance();
				$user = $auth->get_user();

				if ( $auth->verify_password( $user, $current_password ) ) {

					if ( $auth->update_password($user, $new_password) ) {
						$this->template->content->successful = true;
						$this->template->content->status_msg = $messages[ "SUCCESS" ];
					} else {
						$this->template->content->status_msg = $messages[ "NO_UPDATE" ];
					}

				} else {
					$this->template->content->status_msg = $messages[ "INVALID_CURRENT" ];
				}

			} else {
				$this->template->content->status_msg = $messages[ "NO_MATCH" ];
			}
		}
	}
}
