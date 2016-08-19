<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Chromeless controller.
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
class Chromeless_Controller extends Base_Controller {

	public $session = false;
	public $template = 'chromeless';
	public $inline_js = false;
	public $js_strings = false;
	public $log = false;

	public $widgets = array();
	public $linkprovider;

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

		$this->template = new View('chromeless');
		$this->template->css = array();
		$this->template->js = array();

		# Load default current_skin, can be replaced by Authenticated_Controller if user is logged in.
		$this->template->current_skin = $this->get_current_user_skin();

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		bindtextdomain('ninja', APPPATH.'/languages');
		textdomain('ninja');

	}

	/**
	 * Load a skin
	 */
	private function get_current_user_skin() {
		# user might not be logged in due to CLI scripts, be quiet
		$current_skin = config::get('config.current_skin');
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
