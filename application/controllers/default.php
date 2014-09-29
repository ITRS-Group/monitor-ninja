<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/config.php');
require_once('op5/log.php');

/**
 * Default controller.
 * Does not require login but should display default page
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
*/

class Default_Controller extends Ninja_Controller  {
	public function index()
	{
		/* No url specified? go to the default page */
		return url::redirect(Kohana::config('routes.logged_in_default'));
	}

	/**
	 * For backward compatibility before Montior 7.1, this was the default handler
	 * for displaying the login form. So just don't break bookmarked links.
	 */
	public function show_login()
	{
		return url::redirect(Kohana::config('routes.log_in_form'));
	}

	/**
	 *	Used from CLI calls to detect cli setting and
	 * 	possibly default access from config file
	 */
	public function get_cli_status()
	{
		if (PHP_SAPI !== "cli") {
			return url::redirect('default/index');
		} else {
			$this->auto_render=false;
			$cli_access =Kohana::config('config.cli_access');
			echo $cli_access;
		}
	}

	/**
	 * Accept a call from cron to look for scheduled reports to send
	 * @param string $period_str [Daily, Weekly, Monthly, downtime]
	 */
	public function cron($period_str, $timestamp = false)
	{
		if (PHP_SAPI !== "cli") {
			die("illegal call\n");
		}
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		$this->auto_render=false;
		$cli_access = Kohana::config('config.cli_access');

		if (empty($cli_access)) {
			# CLI access is turned off in config/config.php
			op5log::instance('ninja')->log('error', 'No cli access');
			exit(1);
		}

		$op5_auth = Op5Auth::factory(array('session_key' => false));
		$op5_auth->force_user(new Op5User_AlwaysAuth());

		if ($period_str === 'downtime') {
			$sd = new ScheduleDate_Model();
			$sd->schedule_downtime($timestamp);
			exit(0);
		}

		$controller = new Schedule_Controller();
		try {
			$controller->cron($period_str);
		} catch(Exception $e) {
			$this->log->log('error', $e->getMessage() . ' at ' . $e->getFile() . '@' . $e->getLine());
			exit(1);
		}
		exit(0);
	}
}
