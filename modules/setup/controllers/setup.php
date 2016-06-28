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

		$this->template->content = new View('setup');
		$this->template->content->message = $message;
		$this->template->content->linkprovider = $this->linkprovider;

	}

	/**
	 * Configures an administrator submitted by the form displayed by
	 * index
	 */
	public function configure () {

		$data = array(
			'user' => array(
				'username' => $this->input->post('username'),
				'realname' => $this->input->post('realname', $this->input->post('username')),
				'password' => $this->input->post('password', ''),
				'password-repeat' => $this->input->post('password-repeat')
			)
		);

		try {
			Event::run('ninja.setup', $data);
			$this->auto_login($data['user']['username'], $data['user']['password'], 'Default');
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
