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
class Ninja_Controller extends Base_Controller {

	public $session = false;
	public $template;
	public $profiler = false;
	public $inline_js = false;
	public $js_strings = false;
	public $log = false;
	public $widgets = array();

	/**
	 * @var LinkProvider
	 */
	public $linkprovider;

	/**
	 * @var Menu_Model
	 */
	public $menu;

	/**
	 * @var op5MayI
	 */
	public $mayi = false;
	public $access_perfdata = array();

	public function __construct () {
		parent::__construct();

		$this->mayi = op5MayI::instance();
		$this->log = op5log::instance('ninja');

		/* Only available outside of CLI */
		if (PHP_SAPI !== 'cli') {
			$this->linkprovider = LinkProvider::factory();
		}

		$this->template = $this->add_view('template');
		$this->template->css = array();
		$this->template->js = array();
		$this->template->content_class = '';

		$this->template->print_notifications = array();
		$this->template->notices = $this->notices;

		$this->profiler = new Profiler;

		# Load default current_skin, can be replaced by
		# Authenticated_Controller if user is logged in.
		$this->template->current_skin = $this->get_current_user_skin();

		if (PHP_SAPI != 'CLI') {
			$this->template->menu = new Menu_Model();
			$pre_event_data = Event::$data;
			$pre_event_name = Event::$name;

			try {
				Event::run('ninja.menu.setup', $this->template->menu);
			} catch (Exception $e) {
				// We want to log here in order to trace menu
				// rendering exceptions, but we cannot do that
				// since it causes build problems (php errors) during
				// report tests, logs have switched owner.
				//$this->log->log('error', $e);
			}

			Event::$data = $pre_event_data;
			Event::$name = $pre_event_name;
		}

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		bindtextdomain('ninja', APPPATH.'/languages');
		textdomain('ninja');

		$this->_addons();
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
}
