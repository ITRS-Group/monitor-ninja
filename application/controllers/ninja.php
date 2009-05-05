<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Base NINJA controller.
 *
 * Sets necessary objects like session and database
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
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
				$this->translate->_('Tactical overview') 			=> 'tac/index',
				$this->translate->_('Host detail') 						=> 'status/host',
				$this->translate->_('Service detail') 				=> 'status/service',
				$this->translate->_('Hostgroup summary') 			=> 'status/host/hostgroup',
				$this->translate->_('Hostgroup overview') 		=> 'status/hostgroup',
				$this->translate->_('Hostgroup grid') 				=> 'status/host/hostgroup/grid',
				$this->translate->_('Servicegroup summary') 	=> 'status/service/servicegroup',
				$this->translate->_('Servicegroup overview') 	=> 'status/servicegroup',
				$this->translate->_('Servicegroup grid') 			=> 'status/service/servicegroup/grid',
				$this->translate->_('Host problems') 					=> 'status/host/all/6?group_type=',
				$this->translate->_('Service problems') 			=> 'status/service/all?servicestatustypes=14&group_type=',
				$this->translate->_('Unhandled problems') 		=> 'status/host/all/6?group_type=',
			),
			//$this->translate->_('Reporting') => array(),
			//$this->translate->_('Configuration') => array()
		);

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
	*	@name	add_view
	*	@desc	Handle paths to current theme etc
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
	 *	Set correct image path considering
	 *	the path to current theme.
	 */
	public function img_path($rel_path='')
	{
		return $this->add_path($rel_path);
	}

	/**
	 *	Set correct image path considering
	 *	the path to current theme.
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
