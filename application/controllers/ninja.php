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

	const ADMIN = 'admin'; # how do we define the admin role in database

	public $session = false;
	public $template;
	public $user = false;
	public $profiler = false;
	public $inline_js = false;
	public $js_strings = false;
	public $stale_data = false;
	public $run_tests = false;
	public $notifications_disabled = false;
	public $checks_disabled = false;
	public $log = false;
	public $help_link = false;
	public $help_link_url = false;

	public function __construct()
	{
		$this->log = op5log::instance('ninja');
		parent::__construct();
		if(request::is_ajax()) {
			$this->auto_render = FALSE;
		}

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
		$this->template->current_skin = Kohana::config('config.current_skin');

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

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
				'sort_field' => $this->input->get('sort_field', false),
				'sort_order' => $this->input->get('sort_order', false)
				);
			$session_sort[$sort_key] = $cur_data;
			$sort_options = $this->session->get('sort_options', false);

			$_SESSION['sort_options'][$sort_key] = $cur_data;
		}

		bindtextdomain('ninja', APPPATH.'/languages');
		textdomain('ninja');

		$saved_searches = false;

		# This should be defined even if not logged in
		$this->template->help_link = false;

		if (Auth::instance()->logged_in() && PHP_SAPI !== "cli") {
			# warning! do not set anything in xlinks, as it isn't working properly
			# and cannot (easily) be fixed
			$this->xlinks = array();
			$this->_addons();

			# help link
			# To enable link: create an addon with code:
			# $this->help_link_url = "http://example.org/$VERSION$/$CONTROLLER$/$METHOD$"
			if( $this->help_link_url ) {
				$this->template->help_link = str_replace(
					array('$VERSION$', '$CONTROLLER$', '$METHOD$'),
					array_map('urlencode',
						array(trim(config::get_version_info()), Router::$controller, Router::$method)
					),
					$this->help_link_url
				);
			}


			# create the user menu
			$menu = new Menu_Model();
			$this->template->links = $menu->create();

			foreach ($this->xlinks as $link)
				$this->template->links[$link['category']][$link['title']] = $link['contents'];

			$this->_global_notification_checks();

			# fetch info on saved searches and assign to master template
			$searches = Saved_searches_Model::get_saved_searches();
			if ($searches !== false && count($searches)) {
				$this->template->saved_searches = $this->add_view('saved_searches');
				$this->template->saved_searches->searches = $searches;
			}
		}

		$items_per_page = arr::search($_GET, 'items_per_page');
		if ($items_per_page !== false) {
			$_GET['items_per_page'] = ($items_per_page !== false && $items_per_page < 0)
				? ($items_per_page * -1)
				: (int)$items_per_page;
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
	*	Check for notifications to be displayed to user
	* 	Each notification should be an array with (text, link)
	*/
	public function _global_notification_checks()
	{
		try {
			$status = StatusPool_Model::status();
			if($status) {
				// we've got access
				if (!$status->get_enable_notifications()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Notifications are disabled')) );
				}
				if (!$status->get_execute_service_checks()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Service checks are disabled')) );
				}
				if (!$status->get_execute_host_checks()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Host checks are disabled')) );
				}
				if (!$status->get_process_performance_data()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Performance data processing are disabled')) );
				}
				if (!$status->get_accept_passive_service_checks()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Passive service checks are disabled')) );
				}
				if (!$status->get_accept_passive_host_checks()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Passive host checks are disabled')) );
				}
				if (!$status->get_enable_event_handlers()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Event handlers disabled')) );
				}
				if (!$status->get_enable_flap_detection()) {
					$this->add_global_notification( html::anchor('extinfo/show_process_info', _('Flap detection disabled')) );
				}

				unset($status);
			}
		}
		catch( LivestatusException $e ) {
			$this->add_global_notification( _('Livestatus is not accessable') );
		}
		catch( ORMException $e ) {
			$this->add_global_notification( _('Livestatus is not accessable') );
		}
		# check permissions
		$user = Auth::instance()->get_user();
		if (nacoma::link()===true && $user->authorized_for('configuration_information')
			&& $user->authorized_for('system_commands') && $user->authorized_for('host_view_all')) {
			$nacoma = Database::instance('nacoma');
			$query = $nacoma->query('SELECT COUNT(id) AS cnt FROM autoscan_results WHERE visibility != 0');
			$query->result(false);
			$row = $query->current();
			if ($row !== false && $row['cnt'] > 0) {
				$this->add_global_notification( html::anchor('configuration/configure?scan=autoscan_complete', $row['cnt'] . _(' unmonitored hosts present.')) );
			}
		}

	}

	/**
	 * Find and include php files from 'addons' found in defined folders
	 */
	protected function _addons()
	{
		$addons_files = array_merge(
			glob(APPPATH.'addons/*', GLOB_ONLYDIR),
			glob(MODPATH.'*/addons/*', GLOB_ONLYDIR)
			);

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
		echo _("The requested page doesn't exist") . " ($method)";
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
}
