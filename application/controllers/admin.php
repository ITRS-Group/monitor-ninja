<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Tactical overview controller
 * Requires authentication
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 * @copyright 2009 op5 AB
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Admin_Controller extends Authenticated_Controller {

	public function __construct()
	{
		parent::__construct();
		if (!Auth::instance()->logged_in(Ninja_Controller::ADMIN)) {
			# redirecting to default controller
			# should show a message
			url::redirect('default');
		}
	}

	public function index()
	{
		# this should probably contain some information or menu...
		$this->template->content = $this->add_view('admin/index');
		$this->template->title = $this->translate->_('Admin::index');

		$this->template->content->info = $this->translate->_('Admin startpage');
		$this->template->content->links = array
		(
			$this->translate->_('logout')     => 'default/logout'
		);
	}

	/**
	 * Print form to add user
	 *
	 */
	public function edit_user($id=false)
	{
		$user = false;
		if (!empty($id)) {
			# find user details by id
			$user = ORM::factory('user', (int)$id);
		}
		$roles = ORM::factory('role')->find_all();
		$this->template->content = $this->add_view('admin/user_form');
		$this->template->title = !empty($user->id) ? $this->translate->_('Edit user') : $this->translate->_('Add new user');
		$this->template->content->form_title = !empty($user->id) ? $this->translate->_('Edit user') : $this->translate->_('Add user');
		$this->template->content->realname = $this->translate->_('Name');
		$this->template->content->username = $this->translate->_('Username');
		$this->template->content->password = $this->translate->_('Password');
		$this->template->content->confirm_password = $this->translate->_('Confirm password');
		$this->template->content->submit_btn_txt = !empty($user->id) ? $this->translate->_('Save') : $this->translate->_('Create');
		$this->template->content->status_msg = $this->session->get('status_msg', false);
		$this->template->content->user_details = $user;
		$this->template->content->roles = $roles;
	}

	/**
	 * Wrapper for edit_user without ID
	 *
	 */
	public function add_user()
	{
		$this->edit_user(false);
	}

	/**
	 * Handle add user request. Validation and save is done by Auth
	 *
	 */
	public function user_validate()
	{
		if (Kohana::config('csrf.active') & strlen(Kohana::config('csrf.csrf_token'))
			&& !csrf::valid($this->input->post(Kohana::config('csrf.csrf_token')))) {
			$error_msg = $this->translate->_("Request forgery attack detected");
			$this->session->set_flash('error_msg', $error_msg);
			url::redirect('default/show_login');
		} else {
			unset($_POST[Kohana::config('csrf.csrf_token')]);
			unset($_POST['add_user']);
		}

		if ((int)$this->input->post('user_id')) {
			# edit
			$user = ORM::factory('user', $this->input->post('user_id'));
			if ($user->user_validate_edit($this->input->post())) {

				// check roles for new user
				#$user->add(ORM::factory('role', 'login'));

				// finally, save the user
				$user->save();
				$this->session->set_flash('status_msg', sprintf($this->translate->_('User %s was successfully updated'), $this->input->post('realname').' ('.$this->input->post('username').')'));
			} else {
				$this->session->set_flash('status_msg', sprintf($this->translate->_('An error occurred when trying to update user details for %s.'), $this->input->post('realname').' ('.$this->input->post('username').')'));
			}
			url::redirect('admin/edit_user/'.$this->input->post('user_id'));

		} else {
			$user = ORM::factory('user');
			if ($user->validate($this->input->post())) {

				// add roles for new user
				$user->add(ORM::factory('role', 'login'));

				// finally, save the user
				$user->save();
				$this->session->set_flash('status_msg', sprintf($this->translate->_('User %s was successfully added'), $this->input->post('realname').' ('.$this->input->post('username').')'));
			} else {
				$this->session->set_flash('status_msg', sprintf($this->translate->_('An error occurred when trying to add user %s.'), $this->input->post('realname').' ('.$this->input->post('username').')'));
			}
			url::redirect('admin/add_user');
		}
	}

	/**
	 * Show list of all users
	 *
	 */
	public function list_users()
	{
		$user = ORM::factory('user');
		$user_list = $user->find_all(); # with('roles')->
		$this->template->content = $this->add_view('admin/user_list');
		$this->template->content->username = $this->translate->_('Username');
		$this->template->content->realname = $this->translate->_('Name');
		$this->template->content->access = $this->translate->_('Access');
		$this->template->content->user_list = $user_list;
	}
}