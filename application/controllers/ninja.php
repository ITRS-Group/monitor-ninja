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

	public function __construct()
	{
		parent::__construct();
		#$this->profiler = new Profiler;
		#$this->profiler = new Fire_Profiler;
		# Load session library
		# If any current session data exists, it will become available.
		# If no session data exists, a new session is automatically started
		$this->session = Session::instance();

		$this->max_attempts =  Kohana::config('auth.max_attempts');

		$this->locale = zend::instance('locale');

		# @@@FIXME: Zend_Registry - do we need this?
		$this->registry = zend::instance('Registry');
		$this->registry->set('Zend_Locale', $this->locale);

		$this->translate = zend::translate('gettext', $this->locale->getLanguage(), $this->locale);

		if (!$this->translate) {
			# no language file found for requested language
			# use default language set above
			$this->translate = zend::translate('gettext', $this->defaultlanguage, $this->defaultlanguage);
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
		echo 'The requested page ('.$method.') doesn\' exist';
	}

}