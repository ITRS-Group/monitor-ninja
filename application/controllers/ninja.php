<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA controller.
 *
 * Sets necessary objects like session and database
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

	public function __construct()
	{
		parent::__construct();
		$this->theme_path = Kohana::config('config.theme_path').Kohana::config('config.current_theme');

		# set base template file to current theme
		$this->template = $this->add_view('template');

		#$this->profiler = new Profiler;
		if (Authenticated_Controller::ALLOW_PRODUCTION !== true) {
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


		$all_host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;
		$this->template->links = array(
			$this->translate->_('Monitoring') => array(
				$this->translate->_('Tactical overview') 			=> array('tac', 'tac'),
				$this->translate->_('Host detail') 						=> array('status/host', 'host'),
				$this->translate->_('Service detail') 				=> array('status/service', 'service'),
				//'hr1' 																				=> array('', ''),
				$this->translate->_('Hostgroup summary') 			=> array('status/hostgroup_summary', 'hostgroupsummary'),
				$this->translate->_('Hostgroup overview') 		=> array('status/hostgroup', 'hostgroup'),
				$this->translate->_('Hostgroup grid') 				=> array('status/hostgroup_grid', 'hostgroupgrid'),
				//'hr2'																					=> array('', ''),
				$this->translate->_('Servicegroup summary') 	=> array('status/servicegroup_summary', 'servicegroupsummary'),
				$this->translate->_('Servicegroup overview') 	=> array('status/servicegroup', 'servicegroup'),
				$this->translate->_('Servicegroup grid') 			=> array('status/servicegroup_grid', 'servicegroupgrid'),
				//'hr3' 																				=> array('', ''),
				$this->translate->_('Network outages') 				=> array('outages', 'outages'),
				$this->translate->_('Host problems') 					=> array('status/host/all/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), 'hostproblems'),
				$this->translate->_('Service problems') 			=> array('status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN), 'serviceproblems'),
				$this->translate->_('Unhandled problems') 		=> array('status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING).'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED).'&hoststatustypes='.$all_host_status_types, 'problems'),
				//'hr5' 																				=> array('', ''),
				$this->translate->_('Comments') 							=> array('extinfo/show_comments', 'comments'),
				$this->translate->_('Schedule downtime') 			=> array('extinfo/scheduled_downtime', 'scheduledowntime'),
				$this->translate->_('Process info') 					=> array('extinfo/show_process_info', 'processinfo'),
				$this->translate->_('Performance info') 			=> array('extinfo/performance', 'performanceinfo'),
				$this->translate->_('Scheduling queue') 			=> array('extinfo/scheduling_queue', 'schedulingqueue'),
				//'hr6' 																				=> array('', ''),
			),
			$this->translate->_('Reporting') => array(
				$this->translate->_('Trends') 								=> array('trends', 'trends'),
				$this->translate->_('Schedule reports') 			=> array('reports?show_schedules', 'schedulereports'),
				$this->translate->_('Histogram') 							=> array('histogram', 'histogram'),
				$this->translate->_('Alert history') 					=> array('showlog/showlog', 'alerthistory'),
				$this->translate->_('Alert summary') 					=> array('summary', 'alertsummary'),
				$this->translate->_('Notifications') 					=> array('notifications', 'notifications'),
				$this->translate->_('Event log') 							=> array('showlog/showlog', 'eventlog'),
			),
			$this->translate->_('Configuration') => array(
				$this->translate->_('View config') 						=> array('config', 'viewconfig'),
				$this->translate->_('Change password') 				=> array('change_password', 'password'),
				$this->translate->_('Backup/Restore')					=> array('underconstruction/backup_restore', 'backup'),
			)
		);

		if (Reports_Model::_self_check() === true) {
			$this->template->links[$this->translate->_('Reporting')][$this->translate->_('Availability')] = array('reports/?type=avail', 'availability');
			$this->template->links[$this->translate->_('Reporting')][$this->translate->_('SLA Reporting')] = array('reports/?type=sla', 'sla');
		}

		# Add NACOMA link only if enabled in config
		if (nacoma::link()===true) {
			$this->template->links[$this->translate->_('Configuration')][$this->translate->_('Configure')] = array('configuration/configure','nacoma');
		}

		if (Kohana::config('config.nagvis_path') !== false) {
			$this->template->links[$this->translate->_('Monitoring')][$this->translate->_('NagVis')] = array('nagvis/index', 'nagvis');
		}

		$this->registry->set('Zend_Translate', $this->translate);
		$this->_addons();
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
		return new View($this->theme_path.$view);
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
		$path = str_replace('//', '/', $path);
		return $path;
	}
}
