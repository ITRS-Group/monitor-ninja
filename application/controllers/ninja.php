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
	public $max_attempts = false;
	public $locale = false;
	public $registry = false;
	public $defaultlanguage = 'en';
	public $translate = false;
	public $template = "template";
	public $user = false;
	public $profiler = false;
	public $theme_path = false;
	public $xtra_js = array();
	public $xtra_css = array();
	public $inline_js = false;
	public $js_strings = false;
	public $stale_data = false;
	public $run_tests = false;
	public $notifications_disabled = false;
	public $checks_disabled = false;
	public $global_notifications = false;

	public function __construct()
	{
		parent::__construct();
		$this->theme_path = Kohana::config('config.theme_path').Kohana::config('config.current_theme');

		$this->run_tests = $this->input->get('run_tests', false);

		# set base template file to current theme
		$this->template = $this->add_view('template');

		#$this->profiler = new Profiler;
		if (Authenticated_Controller::ALLOW_PRODUCTION !== true && $this->run_tests === false) {
			$this->profiler = new Fire_Profiler;
		}

		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		$this->max_attempts =  Kohana::config('auth.max_attempts');

		$this->locale = zend::instance('locale');

		$this->registry = zend::instance('Registry');
		$this->registry->set('Zend_Locale', $this->locale);
		$this->registry->set('theme_path', $this->theme_path);

		$this->translate = zend::translate('gettext', $this->locale->getLanguage(), $this->locale);

		if (!$this->translate) {
			# no language file found for requested language
			# use default language set above
			$this->translate = zend::translate('gettext', $this->defaultlanguage, $this->defaultlanguage);
		}

		if (Auth::instance()->logged_in() && PHP_SAPI !== "cli") {
			$group_items_per_page = config::get('pagination.group_items_per_page', '*', true);
			$all_host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;
			$this->template->links = array(
				$this->translate->_('About') => array(
					$this->translate->_('op5 Portal') 				=> array('/', 'portal',2),
					$this->translate->_('op5 Monitor manual') 		=> array('/monitor/op5/manual/index.html', 'manual',2),
					$this->translate->_('op5 Support portal') 		=> array('http://www.op5.com/support', 'support',2),
					$this->translate->_('The Ninja project') 			=> array('http://www.op5.org/community/plugin-inventory/op5-projects/ninja', 'ninja',3),
					$this->translate->_('The Merlin project') 		=> array('http://www.op5.org/community/plugin-inventory/op5-projects/merlin', 'merlin',3),
					$this->translate->_('Project documentation') 	=> array('https://wiki.op5.org', 'eventlog',3),
				),
				$this->translate->_('Monitoring') => array(
					$this->translate->_('Tactical overview') 			=> array('/tac', 'tac',0),
					$this->translate->_('Host detail') 					=> array('/status/host/all', 'host',0),
					$this->translate->_('Service detail') 				=> array('/status/service/all', 'service',0),
					//'hr1' 														=> array('', ''),
					$this->translate->_('Hostgroup summary') 			=> array('/status/hostgroup_summary?items_per_page='.$group_items_per_page, 'hostgroupsummary',0),
					$this->translate->_('Hostgroup overview') 		=> array('/status/hostgroup?items_per_page='.$group_items_per_page, 'hostgroup',0),
					$this->translate->_('Hostgroup grid') 				=> array('/status/hostgroup_grid?items_per_page='.$group_items_per_page, 'hostgroupgrid',0),
					//'hr2'														=> array('', ''),
					$this->translate->_('Servicegroup summary') 		=> array('/status/servicegroup_summary?items_per_page='.$group_items_per_page, 'servicegroupsummary',0),
					$this->translate->_('Servicegroup overview') 	=> array('/status/servicegroup?items_per_page='.$group_items_per_page, 'servicegroup',0),
					$this->translate->_('Servicegroup grid') 			=> array('/status/servicegroup_grid?items_per_page='.$group_items_per_page, 'servicegroupgrid',0),
					//'hr3' 														=> array('', ''),
					$this->translate->_('Network outages') 			=> array('/outages', 'outages',0),
					$this->translate->_('Host problems') 				=> array('/status/host/all/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), 'hostproblems',0),
					$this->translate->_('Service problems') 			=> array('/status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN), 'serviceproblems',0),
					$this->translate->_('Unhandled problems') 		=> array('/status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING).'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED).'&hoststatustypes='.$all_host_status_types, 'problems',0),
					//'hr5' 														=> array('', ''),
					$this->translate->_('Comments') 						=> array('/extinfo/show_comments', 'comments',0),
					$this->translate->_('Schedule downtime') 			=> array('/extinfo/scheduled_downtime', 'scheduledowntime',0),
					//$this->translate->_('Recurring downtime') 			=> array('/recurring_downtime/', 'scheduledowntime',0),
					$this->translate->_('Process info') 				=> array('/extinfo/show_process_info', 'processinfo',0),
					$this->translate->_('Performance info') 			=> array('/extinfo/performance', 'performanceinfo',0),
					$this->translate->_('Scheduling queue') 			=> array('/extinfo/scheduling_queue', 'schedulingqueue',0),
				),
				$this->translate->_('Reporting') => array(
					$this->translate->_('Trends') 						=> array('/trends', 'trends',0),
					//$this->translate->_('Histogram') 					=> array('histogram', 'histogram',0),
					$this->translate->_('Alert history') 				=> array('/showlog/alert_history', 'alerthistory',0),
					$this->translate->_('Alert summary') 				=> array('/summary', 'alertsummary',0),
					$this->translate->_('Notifications') 				=> array('/notifications', 'notifications',0),
					$this->translate->_('Event log') 					=> array('/showlog/showlog', 'eventlog',0),
				),
				$this->translate->_('Configuration') => array(
					$this->translate->_('View config') 					=> array('/config', 'viewconfig',0),
					$this->translate->_('My Account') 			=> array('/user', 'password',0),
					$this->translate->_('Backup/Restore')				=> array('/backup', 'backup',0),
				)
			);
			if (Kohana::config('auth.driver') == 'LDAP')
				unset ($this->template->links[$this->translate->_('Configuration')][$this->translate->_('Change password')]);

			$this->template->links[$this->translate->_('Reporting')][$this->translate->_('Availability')] = array('/'.Kohana::config('reports.reports_link').'/?type=avail', 'availability',0);
			$this->template->links[$this->translate->_('Reporting')][$this->translate->_('SLA Reporting')] = array('/'.Kohana::config('reports.reports_link').'/?type=sla', 'sla',0);
			$this->template->links[$this->translate->_('Reporting')][$this->translate->_('Schedule reports')] = array('/'.Kohana::config('reports.reports_link').'?show_schedules', 'schedulereports',0);

			if (Kohana::config('config.cacti_path')) # @@@FIXME: Create a specific cacti logo, now re-using trends
				$this->template->links[$this->translate->_('Reporting')][$this->translate->_('Statistics')] = array('/statistics', 'statistics',1);

			# Add NACOMA link only if enabled in config
			if (nacoma::link()===true)
				$this->template->links[$this->translate->_('Configuration')][$this->translate->_('Configure')] = array('/configuration/configure','nacoma',0);

			$auth = new Nagios_auth_Model();
			if (!$auth->view_hosts_root) {
				# only show the link when authorized for all hosts
				unset($this->template->links[$this->translate->_('Monitoring')][$this->translate->_('Recurring downtime')]);
			}
			if ($auth->view_hosts_root && $auth->view_services_root && Kohana::config('config.hypermap_path') !== false)
				$this->template->links[$this->translate->_('Monitoring')][$this->translate->_('Hyper Map')] = array('/hypermap', 'hypermap',0);
			unset($auth);

			if (Kohana::config('config.nagvis_path') !== false)
				$this->template->links[$this->translate->_('Monitoring')][$this->translate->_('NagVis')] = array('/nagvis/index', 'nagvis',0);

			$this->xlinks = array();
			$this->_addons();
			foreach ($this->xlinks as $link)
				$this->template->links[$link['category']][$link['title']] = $link['contents'];

			$this->_is_alive();
			$this->_global_notification_checks();
		}

		$this->registry->set('Zend_Translate', $this->translate);

		$items_per_page = arr::search($_GET, 'items_per_page');
		if ($items_per_page !== false) {
			$_GET['items_per_page'] = ($items_per_page !== false && $items_per_page < 0)
				? ($items_per_page * -1)
				: $items_per_page;
		}

		$custom_per_page = arr::search($_GET, 'custom_pagination_field');
		if ($custom_per_page !== false) {
			$_GET['custom_pagination_field'] = ($custom_per_page !== false && $custom_per_page < 0)
				? ($custom_per_page * -1)
				: $custom_per_page;
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
		}
		unset($data);

		# check permissions
		$auth = new Nagios_auth_Model();
		if (nacoma::link()===true && $auth->authorized_for_configuration_information
			&& $auth->authorized_for_system_commands && $auth->view_hosts_root) {
			$nacoma = new Database('nacoma');
			$query = $nacoma->query('SELECT COUNT(id) AS cnt FROM autoscan_results');
			$query->result(false);
			$row = $query->current();
			if ($row !== false && $row['cnt'] > 0) {
				$notifications[] = array($row['cnt'] . $this->translate->_(' unmonitored hosts present.'), "https://" . $_SERVER['HTTP_HOST'] . "/monitor/index.php/configuration/configure?scan=autoscan_complete");
			}
		}

		$this->global_notifications = $notifications;
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

		if ($this->run_tests !== false && unittest::get_testfile($view) !== false) {
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
		$rel_path = trim($rel_path);
		if (empty($rel_path)) {
			return false;
		}

		$path = false;
		# assume rel_path is relative from current theme
		$path = 'application/views/'.$this->theme_path.$rel_path;
		# make sure we didn't mix up start/end slashes
		$path = str_replace('//', '/', $path);
		return $path;
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
