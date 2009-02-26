<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default NINJA controller.
 * Does not require login but should display default page
 * and show login form(?)
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    ???
 */
class Login_Controller extends Controller {

	public $output = array();
	public $ninja_db = false;

	public function __construct()
	{
		parent::__construct();
		$this->session = Session::instance();
		$this->ninja_db = new Database;
	}

	public function index()
	{
		url::redirect('default/show_login');
	}

	/**
	 * Collect user input from login form, authenticate against
	 * Authenticated_Controller::authenticate and redirect to
	 * controller requested by user.
	 */
	public function do_login()
	{
		if ($_POST) {
			$post = Validation::factory($_POST);
			$post->add_rules('*', 'required');

			foreach ($_POST as $key => $val) {
				$user->$key = $val;
			}

			if (isset($user->login) && isset($user->username) && isset($user->password)) {
				if (Authenticated_Controller::authenticate($user->username, $user->password)) {
					$this->session->set('logged_in', true);
					$requested_uri = $this->session->get('requested_uri', false);
					if ($requested_uri !== false) {
						# remove 'requested_uri' from session
						$this->session->delete('requested_uri');
						url::redirect($requested_uri);
					} else {
						# we have no requested uri which could indicate that some
						# accessed the login page directly (bookmark?)
						# trying with tac/index since it should be default
						url::redirect('tac/index');
					}
				} else {
					# increase login attempts counter
					# this could be used to restrict access after
					#  a certain nr of failed login attempts
					$this->session->set('login_attempts', $this->session->get('login_attempts')+1);

					# set login error to user
					$error_msg = "Login failed - please try again";
					$this->session->set_flash('error_msg', $error_msg);
					url::redirect('login/show_login');
				}
			}
		}
		# trying to login without $_POST is not allowed and shouldn't
		# even happen - redirecting to default controller
		url::redirect('default/');
	}

	public function logout()
	{
		$this->session->destroy();
		url::redirect('default/');
	}
}