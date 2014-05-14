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

		$this->template->toolbar = new Toolbar_Controller( _("My Account"), _("Change Password") );
		$root = url::base(FALSE) . 'index.php/';

		$this->template->toolbar->info(
			'<a href="' . $root . 'user" title="' . _( "Account Settings" ) . '">' . _( "Account Settings" ) . '</a>'
		);

		if ( Auth::instance()->authorized_for('access_rights') ) {
			$this->template->toolbar->info(
				'<a href="' . $root . 'user/menu_edit' . '" title="' . _( "Edit user menu" ) . '">' . _( "Edit user menu" ) . '</a>'
			);
		}

	}

	public function index()
	{
		$this->template->content->status_msg = '';
	}

	public function change_password()
	{

		$messages = array(
			"TO_SHORT" => _('The password must be at least 5 characters long.'),
			"NO_UPDATE" => _('Authentication backend reported that password could not be updated.'),
			"INVALID_CURRENT" => _('You entered incorrect current password.'),
			"NO_MATCH" => _('New password did not match repeated password.'),
			"SUCCESS" => _('Password changed successfully')
		);

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
