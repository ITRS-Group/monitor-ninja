<?php
require_once ('op5/mayi.php');
require_once ('op5/auth/Auth.php');

/**
 * Temporary constraints class to implement some legacy constraints, useful
 * during porting of the authentication library
 */
class mayi_ninja_legacy_constraints implements op5MayI_Constraints {
	/**
	 * Determine if the action is allowed, if the namespace is ninja.legacy.
	 * Otherwise allow everything, so we don't interfere with anything else.
	 * We're allowed access to ninja.legacy if we're logged in.
	 */
	public function run($action, $env, &$messages, &$perfdata) {
		list ($resource, $method) = explode(':', $action, 2);
		if ($resource == 'ninja.legacy') {
			switch ($method) {
			case 'authenticated':
				if(!isset($env['user']) || !isset($env['user']['authenticated']))
					return false;
				return $env['user']['authenticated'];
			}
			// Since we don't know about the method, but it's a legacy resource,
			// deny
			return false;
		}
		// Since we don't know about anything else than legacy, we allow it, and
		// pass it forward
		return true;
	}
}

/**
 * Hooks to handle authentication and add default authorization to the mayi
 * interface in ninja.
 */
class mayi_auth_hooks {
	public function __construct() {
		Event::add('system.ready', array ($this,'populate_mayi'));
		Event::add('system.post_controller', array ($this,'write_back'));
	}

	/**
	 * Populate the MayI interface with op5auth, to enable access to user
	 * information in the constraints.
	 */
	public function populate_mayi() {
		$mayi = op5MayI::instance();
		$auth = op5auth::instance();
		$mayi->be('user', $auth);

		$mayi->act_upon(new mayi_ninja_legacy_constraints());
	}

	/**
	 * Hook to execute before page is rendered, to make it possible to write back
	 * and close the session if rendering is heavy.
	 */
	public function write_back() {
		$auth = op5auth::instance();
		$auth->write_close();
	}
}

new mayi_auth_hooks();
