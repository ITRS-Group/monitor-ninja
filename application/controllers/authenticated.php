<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 *	Base authenticated controller for NINJA
 *	All controllers requiring authentication should
 * 	extend this controller
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Authenticated_Controller extends Ninja_Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function __construct()
	{
		parent::__construct();
		# make sure user is authenticated
		$authentic = new Auth;
		if (!$authentic->logged_in()) {
			# store requested uri in session for later redirect
			$this->session->set('requested_uri', url::current());
			url::redirect('default/show_login');
		} else {
			$this->user = Auth::instance()->get_user();
		}
	}

	public function is_authenticated()
	{
		return !Auth::instance()->logged_in();
	}

	public function index()
	{
		# don't allow direct access
		# redirect to logged_in_default route as set in routes config
		url::redirect(Kohana::config('routes.logged_in_default'));
	}

	public function to_template(array $content)
	{
		$this->output = array_merge($content, $this->output);
	}
}