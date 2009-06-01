<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA controller.
 *
 * Sets necessary objects like session and database
 *
 * @package NINJA
 * @author op5 AB
 * @license GPL
 */
class Ninja_Controller extends Template_Controller {

	const ADMIN = 'admin'; # how do we define the admin role in database

	public $session = false;
	public $output = array(); # page content to be passed to template
	public $table_prefix = false;
	public $max_attempts = false;
	public $locale = false;
	public $registry = false;
	public $defaultlanguage = 'en';
	public $translate = false;
	public $template = "template";
	public $user = false;
	public $profiler = false;
	public $theme_path = false;

	public function __construct()
	{
		parent::__construct();
		$this->theme_path = Kohana::config('config.theme_path').Kohana::config('config.current_theme');

		# set base template file to current theme
		$this->template = $this->add_view('template');

		#$this->profiler = new Profiler;
		#$this->profiler = new Fire_Profiler;
		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		$this->max_attempts =  Kohana::config('auth.max_attempts');

		$this->locale = zend::instance('locale');

		$this->registry = zend::instance('Registry');
		$this->registry->set('Zend_Locale', $this->locale);

		$this->translate = zend::translate('gettext', $this->locale->getLanguage(), $this->locale);

		if (!$this->translate) {
			# no language file found for requested language
			# use default language set above
			$this->translate = zend::translate('gettext', $this->defaultlanguage, $this->defaultlanguage);
		}


		$this->template->links = array(
			$this->translate->_('Monitoring') => array(
				$this->translate->_('Tactical overview') 			=> array('tac', 'tac'),
				$this->translate->_('hr1') 										=> array('', ''),
				$this->translate->_('Host detail') 						=> array('status/host', 'host'),
				$this->translate->_('Host problems') 					=> array('status/host/all/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), 'host-problems'),
				$this->translate->_('Hostgroup overview') 		=> array('status/hostgroup', 'hostgroup'),
				$this->translate->_('Hostgroup grid') 				=> array('status/hostgroup_grid', 'hostgroup-grid'),
				$this->translate->_('Hostgroup summary') 			=> array('status/hostgroup_summary', 'hostgroup-summary'),
				$this->translate->_('hr2') 										=> array('', ''),
				$this->translate->_('Service detail') 				=> array('status/service', 'star'),
				$this->translate->_('Service problems') 			=> array('status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN), 'service-problems'),
				$this->translate->_('Servicegroup overview') 	=> array('status/servicegroup', 'servicegroup'),
				$this->translate->_('Servicegroup grid') 			=> array('status/servicegroup_grid', 'servicegroup-grid'),
				$this->translate->_('Servicegroup summary') 	=> array('status/servicegroup_summary', 'servicegroup-summary'),
				$this->translate->_('hr3') 										=> array('', ''),
				$this->translate->_('Network outages') 				=> array('outages', 'problems2'),
				$this->translate->_('Unhandled problems') 		=> array('status/service/all/?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN).'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED), 'problems'),
				$this->translate->_('hr4') 										=> array('', ''),
				$this->translate->_('Comments') 							=> array('extinfo/show_comments', 'comments'),
				$this->translate->_('Process info') 					=> array('extinfo/show_process_info', 'extinfo'),
			),
			$this->translate->_('Reporting') => array(
				$this->translate->_('Availability') 					=> array('reporting/availability', 'reports'),
				$this->translate->_('SLA Reporting') 					=> array('reporting/sla_reporting', 'sla'),
			)
		);

		# Add NACOMA link only if enabled in config
		if (Kohana::config('config.nacoma_path')!==false) {
			$this->template->links[$this->translate->_('Configuration')][$this->translate->_('Configure')] = array('configuration/configure','nacoma');
		}

		if (Kohana::config('config.nagvis_path') !== false) {
			$this->template->links[$this->translate->_('Monitoring')][$this->translate->_('NagVis')] = array('nagvis/index', 'nagvis');
		}

		$this->registry->set('Zend_Translate', $this->translate);
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
}
