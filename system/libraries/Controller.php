<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * $Id: Controller.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Controller {

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// URI should always be available
		$this->uri = URI::instance();

		// Input should always be available
		$this->input = Input::instance();
	}

	/**
	 * Handles methods that do not exist.
	 *
	 * @param   string  method name
	 * @param   array   arguments
	 * @return  void
	 */
	public function __call($method, $args)
	{
		op5log::instance('ninja')->log('debug',
			sprintf(
				"404 triggered from '%s' for url '%s', wanted to call method '%s' with args '%s'",
				__METHOD__,
				Router::$complete_uri,
				$method,
				var_export($args, true)
			)
		);

		Event::run('system.404');
	}

} // End Controller Class
