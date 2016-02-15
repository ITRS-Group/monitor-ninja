<?php
require_once('op5/auth/Auth.php');

/**
 * Hooks for enabling temporary access through external widgets.
 *
 * This hook is used to give access to a page as a given user temporarly after a request to the external widget.
 */
class external_widget_auth_hooks {
	public function __construct() {
		if (PHP_SAPI !== "cli") {
			Event::add('system.pre_controller', array ($this,'handle_external_widget_auth'));
		}
	}

	/**
	 * The handler for get auth, running as a system.ready hook.
	 */
	public function handle_external_widget_auth() {
		$conf = Kohana::config('external_widget');

		// If external widget user isn't defined, don't try to do authentication
		if($conf['username'] === false)
			return;

		$input = Input::instance();

		$is_external_widget = false;
		if($input->get('request_context', '') == 'external_widget')
			$is_external_widget = true;

		if($input->post('request_context', '') == 'external_widget')
			$is_external_widget = true;

		if(Router::$controller == 'external_widget')
			$is_external_widget = true;

		if(!$is_external_widget)
			return;


		$auth = op5auth::instance();
		/* @var $auth op5auth */

		/*
		 * Due to a current issue in op5auth v.s. session start,
		 * op5auth::session_fetch() can't currently be called in the constructor
		 * of op5auth. Thus, we need to trigger that call before write_close().
		 *
		 * That call is easiest to trigger just by callin
		 * op5auth::instance()->get_user(), which forces the user to be fetched,
		 * so we can store the session down.
		 *
		 * Should be resolved after Session class is refactored away.
		 */
		$auth->get_user();

		// This should be a one-shot request, close session
		$auth->write_close();

		$auth->force_user(new User_Model(array(
			'username' => $conf['username'],
			'groups' => $conf['groups']
		)));
	}
}

new external_widget_auth_hooks();
