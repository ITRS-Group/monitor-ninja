<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/log.php');

/**
 * Base NINJA controller.
 *
 * Sets necessary objects like session and database
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Ninja_Controller extends Template_Controller {
	public $session = false;
	public $template;
	public $profiler = false;
	public $inline_js = false;
	public $js_strings = false;
	public $run_tests = false;
	public $log = false;

	public $widgets = array();

	/**
	 * @var op5MayI
	 */
	public $mayi = false;
	public $access_perfdata = array();

	public function __construct()
	{
		parent::__construct();
		$this->mayi = op5MayI::instance();
		$this->log = op5log::instance('ninja');

		$this->run_tests = $this->input->get('run_tests', false) !== false;

		$this->template = $this->add_view('template');
		$this->template->css = array();
		$this->template->js = array();

		$this->template->global_notifications = array();
		$this->template->print_notifications = array();

		if (!$this->run_tests) {
			$this->profiler = new Profiler;
		} else if ($this->run_tests !== false) {
			unittest::instance();
		}

		# Load default current_skin, can be replaced by Authenticated_Controller if user is logged in.
		$this->template->current_skin = $this->get_current_user_skin();

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		bindtextdomain('ninja', APPPATH.'/languages');
		textdomain('ninja');

		$this->_addons();

		# create the user menu
		$menu = new Menu_Model();
		$this->template->links = $menu->create();

		# convert test params to $_REQUEST to enable more
		# parameters to different controllers (reports for one)
		if (PHP_SAPI == "cli" && $this->run_tests !== false
		&& !empty($_SERVER['argc']) && isset($_SERVER['argv'][1])) {
			$params = $_SERVER['argv'][1];
			if (strstr($params, '?')) {
				$params = explode('?', $params);
				parse_str($params[1], $_REQUEST);
			}
		}
	}

	public function add_global_notification( $notification ) {
		if (!is_array($notification)) {
			$notification = array($notification);
		}
		$this->template->global_notifications[] = $notification;
	}

	public function add_print_notification($notification) {
		$this->template->print_notifications[] = $notification;
	}

	/**
	 * Find and include php files from 'addons' found in defined folders
	 */
	protected function _addons()
	{
		$addons_files = array_merge(
			glob(APPPATH.'addons/menu', GLOB_ONLYDIR),
			glob(MODPATH.'*/addons/menu', GLOB_ONLYDIR)
			);

		foreach ($addons_files as $file) {
			$addons = glob($file.'/*.php');
			foreach ($addons as $addon) {
				include_once($addon);
			}
		}

	}

	/**
	 * Create a View object
	 */
	public function add_view($view)
	{
		$view = trim($view);
		if (empty($view)) {
			return false;
		}
		return new View($view);
	}

	/**
	 * Set correct image path.
	 */
	public function img_path($rel_path='')
	{
		return $this->add_path($rel_path);
	}

	/**
	 * Set correct image path
	 */
	public function add_path($rel_path)
	{
		return ninja::add_path($rel_path);
	}

	/**
	 * Load a skin
	 */

	private function get_current_user_skin() {
		# user might not be logged in due to CLI scripts, be quiet
		$current_skin = config::get('config.current_skin', '*', true);
		if (!$current_skin) {
			$current_skin = 'default/';
		}
		else if (substr($current_skin, -1, 1) != '/') {
			$current_skin .= '/';
		}

		if (!file_exists(APPPATH."views/css/".$current_skin)) {
			op5log::instance('ninja')->log('notice', 'Wanted to use skin "'. $current_skin.'", could not find it');
			$current_skin = 'default/';
		}
		return $current_skin;
	}

	/**
	 * Verify access to a given action.
	 * If no access, throw a Kohana_User_Exception
	 *
	 * This method returns if access is allowed, setting $this->access_messages
	 * and $this->access_perfdata.
	 *
	 * If not access is allowed, throw an execption, to break out of normal
	 * execution path, and render a access denied-page.
	 */
	protected function _verify_access($action, $args = array()) {
		$access = $this->mayi->run($action, $args, $messages,
			$this->access_perfdata);

		if ($access) {
			foreach ($messages as $msg) {
				$this->add_global_notification($msg);
				// Since the messages are published depending on action instead
				// of target, we should add all messages as print_notifications
				// as well
				$this->add_print_notification($msg);
			}
		}
		else {
			if($this->mayi->run('ninja.auth:login.redirect')) {
				url::redirect('auth/login?uri=' . rawurlencode(Router::$complete_uri));
			} else {
				$this->template->content = new View('auth/no_access');
				$this->template->content->messages = $messages;
				$this->template->content->action = $action;
				throw new Kohana_User_Exception('No access',
					'Access denied for action ' . $action, $this->template);
			}
		}
	}
}
