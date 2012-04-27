<?php defined('SYSPATH') OR die('No direct access allowed.');

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

	const ADMIN = 'admin'; # how do we define the admin role in database

	public $session = false;
	public $locale = false;
	public $registry = false;
	public $defaultlanguage = 'en';
	public $translate = false;
	public $template = "template";
	public $user = false;
	public $profiler = false;
	public $xtra_js = array();
	public $xtra_css = array();
	public $inline_js = false;
	public $js_strings = false;
	public $stale_data = false;
	public $run_tests = false;
	public $notifications_disabled = false;
	public $checks_disabled = false;
	public $global_notifications = false;
	protected $theme_path = false;

	public function __construct()
	{
		parent::__construct();
		$this->theme_path = ninja::get_theme_path();

		$this->run_tests = $this->input->get('run_tests', false) !== false;

		# set base template file to current theme
		$this->template = $this->add_view('template');

		#$this->profiler = new Profiler;
		if (Authenticated_Controller::ALLOW_PRODUCTION !== true && $this->run_tests === false) {
			$this->profiler = new Fire_Profiler;
		}
		else if ($this->run_tests !== false) {
			unittest::instance();
		}

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		# load reduced (noc) master template
		# if param 'noc' is found in $_REQUEST (status controller) or if we are showing the noc controller
		#if ((isset($_REQUEST['noc']) && Router::$controller === 'status') || Router::$controller === 'noc') {
		if (isset($_REQUEST['noc'])) {
			$this->session->set('use_noc', !empty($_REQUEST['noc']) );
		}

		if (Router::$controller === 'noc' || $this->session->get('use_noc', false) !== false) {
			$this->template = $this->add_view('noc');
		}

		if (isset($_REQUEST['noheader'])) {
			$this->session->set('noheader', !empty($_REQUEST['noheader']));
		}

		if ($this->session->get('noheader', false) !== false) {
			# hack the session variable into the $_GET array
			# to make it visible in $this->input->get()
			$_GET['noheader'] = 1;
		}

		/**
		* check for generic sort parameters in GET and store in session
		*/
		# use e.g status/host/all to store sort settings
		# this will lead to specific sort order for
		# every <host_name> e.g status/host/<host_name>
		$sort_key = Router::$current_uri;

		# The following will instead make all calls to e.g status/host
		# to behave the same
		# $sort_key = Router::$controller.'/'.Router::$method;

		if ($this->input->get('sort_field', false)) {
			$cur_data = array(
				'sort_field' => urldecode($this->input->get('sort_field', false)),
				'sort_order' => urldecode($this->input->get('sort_order', false))
				);
			$session_sort[$sort_key] = $cur_data;
			$sort_options = $this->session->get('sort_options', false);

			$_SESSION['sort_options'][$sort_key] = $cur_data;
		}

		# check for sort options in session and use those if found
		$sort_options = $this->session->get('sort_options', false);

		if (!empty($sort_options) && isset($_SESSION['sort_options'][$sort_key])) {
			# found sort options in session for requested page
			$_GET['sort_field'] = $_SESSION['sort_options'][$sort_key]['sort_field'];
			$_GET['sort_order'] = $_SESSION['sort_options'][$sort_key]['sort_order'];
		}

		$this->locale = zend::instance('locale');

		$this->registry = zend::instance('Registry');
		$this->registry->set('Zend_Locale', $this->locale);

		$this->translate = zend::translate('gettext', $this->locale->getLanguage(), $this->locale);

		if (!$this->translate) {
			# no language file found for requested language
			# use default language set above
			$this->translate = zend::translate('gettext', $this->defaultlanguage, $this->defaultlanguage);
		}

		$saved_searches = false;

		Kohana::config_set('auth.auth_methods', Kohana::config('auth.driver'));
		// set auth.driver to the currently used authentication method
		$auth_methods = Kohana::config('auth.auth_methods');
		if (isset($_SESSION['auth_method']))
			Kohana::config_set('auth.driver', $_SESSION['auth_method']);
		else if (is_array($auth_methods) && count($auth_methods) == 1)
			Kohana::config_set('auth.driver', array_pop(array_keys($auth_methods)));
		else if (!is_array($auth_methods))
			Kohana::config_set('auth.driver', $auth_methods);
		else {
			// this can never be unset, so find some nice default
			// (should only happen when user is logged out)
			Kohana::config_set('auth.driver', "Ninja");
		}

		if (Auth::instance()->logged_in() && PHP_SAPI !== "cli") {
			# warning! do not set anything in xlinks, as it isn't working properly
			# and cannot (easily) be fixed
			$this->xlinks = array();
			$this->_addons();

			# create the user menu
			$this->template->links = $this->create_menu();

			if (Kohana::config('auth.driver') == 'LDAP')
				unset ($this->template->links[$this->translate->_('Configuration')][$this->translate->_('Change password')]);

			foreach ($this->xlinks as $link)
				$this->template->links[$link['category']][$link['title']] = $link['contents'];

			$this->_is_alive();
			$this->_global_notification_checks();

			# fetch info on saved searches and assign to master template
			$this->template->saved_searches = $this->add_view('saved_searches');
			$this->template->is_searches = false;
			$searches = Saved_searches_Model::get_saved_searches();
			if ($searches !== false && count($searches)) {
				$this->template->saved_searches->searches = $searches;
				$this->template->is_searches = true;
			}
		}

		$this->registry->set('Zend_Translate', $this->translate);

		$items_per_page = arr::search($_GET, 'items_per_page');
		if ($items_per_page !== false) {
			$_GET['items_per_page'] = ($items_per_page !== false && $items_per_page < 0)
				? ($items_per_page * -1)
				: (int)$items_per_page;
		}

		$custom_per_page = arr::search($_GET, 'custom_pagination_field');
		if ($custom_per_page !== false) {
			$_GET['custom_pagination_field'] = ($custom_per_page !== false && $custom_per_page < 0)
				? ($custom_per_page * -1)
				: (int)$custom_per_page;
		}

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


	/**
	*	Build menu structure and possibly remove some items
	*/
	public function create_menu()
	{
		include(APPPATH.'views/'.$this->theme_path.'menu/menu.php');
		$removed_items = config::get('removed_menu_items', '*');
		if (!empty($removed_items)) {
			$remove_items = unserialize($removed_items);
			$this->remove_menu_items($menu_base, $menu_items, $remove_items);
		}
		return $menu_base;
	}

	/**
	*	Remove menu item by index
	* 	Both section string ['about', 'monitoring', etc]
	* 	and item string ['portal', 'manual', 'support', etc] are required.
	* 	As a consequence, all menu items has to be explicitly removed before removing the section
	*/
	public function remove_menu_items(&$menu_links=false, &$menu_items=false, $section_str=false,
		$item_str=false)
	{
		if (empty($menu_links) || empty($menu_items) || empty($section_str)) {
			return false;
		}

		if (is_array($section_str)) {
			# we have to make recursive calls
			foreach ($section_str as $section => $items) {
				foreach ($items as $item) {
					$this->remove_menu_items($menu_links, $menu_items, $section, $item);
				}
			}
		} else {
			if (empty($item_str) && isset($menu_links[$menu_items['section_'.$section_str]])
				&& empty($menu_links[$menu_items['section_'.$section_str]])) {
				# remove the section
				unset($menu_links[$menu_items['section_'.$section_str]]);
			} elseif (!empty($item_str) && isset($menu_items['section_'.$section_str]) && isset($menu_links[$menu_items['section_'.$section_str]]) && isset($menu_items[$item_str]) && isset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]])) {
				unset($menu_links[$menu_items['section_'.$section_str]][$menu_items[$item_str]]);
			}
		}
	}

	/**
	*	Check that we are still getting data from merlin.
	*	If not, user should be alerted
	*/
	public function _is_alive()
	{
		$last_alive = Program_status_Model::last_alive();
		$stale_data_limit = Kohana::config('config.stale_data_limit');
		$diff = time() - $last_alive;;
		if ($diff  > $stale_data_limit) {
			$this->stale_data = $diff;
			$this->inline_js .= "$('#infobar-sml').show();";
			$this->template->inline_js = "$('#infobar-sml').show();";
		}
	}

	/**
	*	Check for notifications to be displayed to user
	* 	Each notification should be an array with (text, link)
	*/
	public function _global_notification_checks()
	{
		$data = Program_status_Model::notifications_checks();
		$notifications = false;
		$data = $data ? $data->current() : false;
		if ($data !== false) {
			$this->notifications_disabled = !$data->notifications_enabled;
			if ($this->notifications_disabled == true) {
				$notifications[] = array($this->translate->_('Notifications are disabled'), false);
			}

			$this->checks_disabled = !$data->active_service_checks_enabled;
			if ($this->checks_disabled == true) {
				$notifications[] = array($this->translate->_('Service checks are disabled'), false);
			}
		} else {
			$notifications[] = array($this->translate->_('Unable to determine if notifications or service checks are disabled'), false);
		}
		unset($data);

		# check permissions
		$auth = new Nagios_auth_Model();
		if (nacoma::link()===true && $auth->authorized_for_configuration_information
			&& $auth->authorized_for_system_commands && $auth->view_hosts_root) {
			$nacoma = Database::instance('nacoma');
			$query = $nacoma->query('SELECT COUNT(id) AS cnt FROM autoscan_results WHERE visibility != 0');
			$query->result(false);
			$row = $query->current();
			if ($row !== false && $row['cnt'] > 0) {
				$notifications[] = array($row['cnt'] . $this->translate->_(' unmonitored hosts present.'), "https://" . $_SERVER['HTTP_HOST'] . "/monitor/index.php/configuration/configure?scan=autoscan_complete");
			}
		}

		$this->global_notifications = $notifications;
		$this->template->global_notifications = $notifications;
	}

	/**
	*	Check if any addons should be included
	*/
	public function _addons()
	{
		$addons_dir = APPPATH."addons/";
		$addons_files = glob($addons_dir.'*', GLOB_ONLYDIR);

		foreach ($addons_files as $file) {
			$addons = glob($file.'/*.php');
			foreach ($addons as $addon) {
				include_once($addon);
			}
		}

	}

	public function __call($method, $arguments)
	{
		// Disable auto-rendering
		$this->auto_render = FALSE;

		// By defining a __call method, all pages routed to this controller
		// that result in 404 errors will be handled by this method, instead of
		// being displayed as "Page Not Found" errors.
		echo $this->translate->_("The requested page doesn't exist") . " ($method)";
	}

	/**
	 * Handle paths to current theme etc
	 *
	 */
	public function add_view($view=false)
	{
		$view = trim($view);
		if (empty($view)) {
			return false;
		}

		if ($this->run_tests !== false) {
			if(unittest::get_testfile($view) === false) {
				$tap = unittest::instance();
				$tap->fail("Could not find the view file '$view'");
				exit($tap->done());
			}

			return new View('tests/'.$view);
		} else {
			return new View($this->theme_path.$view);
		}

	}

	/**
	 * Set correct image path considering
	 * the path to current theme.
	 */
	public function img_path($rel_path='')
	{
		return $this->add_path($rel_path);
	}

	/**
	 * Set correct image path considering
	 * the path to current theme.
	 */
	public function add_path($rel_path)
	{
		return ninja::add_path($rel_path);
	}

	/**
	 * Set correct template path considering
	 * the path to current theme.
	 */
	public function add_template_path($rel_path)
	{
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		$path = false;
		# assume rel_path is relative from current theme
		$path = url::base(false).'application/views/'.$this->theme_path.$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = text::reduce_slashes($path);
		return $path;
	}
}
