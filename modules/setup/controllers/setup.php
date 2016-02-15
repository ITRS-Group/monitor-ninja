<?php
/**
 * Controls the initial administrator setup sequence
 */
class Setup_Controller extends Chromeless_Controller {

	public function __construct () {
		parent::__construct();
		if (!setup::is_available())
			$this->redirect('auth', 'login');
	}

	/**
	 * Displays the default form for administrator setup
	 */
	public function index ($message = false) {

		$this->template->js = array();

		$this->template->content = new View('setup');
		$this->template->content->message = $message;
		$this->template->content->linkprovider = $this->linkprovider;

	}

	/**
	 * Configures an administrator submitted by the form displayed by
	 * index
	 */
	public function configure () {
		try {
			$user = new User_Model();

			$password = $this->input->post('password', '');
			$repeat = $this->input->post('password-repeat');
			$username = $this->input->post('username');
			$method = 'Default';

			if ($password !== $repeat) {
				throw new Exception(
					'Passwords do not match'
				);
			}

			// for UX reasons, displaying the proper authentication
			// module driven exception for this was deemed to far
			// above new users
			if (strlen($password) === 0) {
				throw new Exception(
					'You need to set a password'
				);
			}

			$user->set_username($username);
			$user->set_realname($username);
			$user->set_password($password);

			$user->set_groups(array('admins'));
			$user->set_modules(array($method));

			$user->save();
			$eventdata = array(
				'administrator' => array(
					'username' => $username
				)
			);

			Event::run('ninja.initial.setup', $eventdata);
			$this->auto_login($username, $password, $method);

		} catch (Exception $e) {
			$this->index(
				new ErrorNotice_Model($e->getMessage())
			);
		}
	}

	/**
	 * Performs an auto login after the administrator account has been
	 * created
	 */
	private function auto_login ($username, $password, $method) {
		$auth = op5Auth::instance();
		if ($auth->login($username, $password, $method)) {
			Event::run('ninja.logged_in');
			$this->redirect('default');
		} else {
			// Should the auto login fail for some reason;
			// redirect to manual login
			$this->redirect('auth', 'login');
		}
	}

}
