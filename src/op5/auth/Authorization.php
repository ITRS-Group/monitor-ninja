<?php

require_once('op5/config.php');
require_once('op5/log.php');

/**
 * User authentication and authorization library.
 *
 * @package    Authorization
 * @author
 * @copyright
 * @license
 */
class op5Authorization {
	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory()
	{
		return new self();
	}


	private $groups = false;

	public function __construct()
	{
		$this->log = op5Log::instance('auth');
		$this->groups = op5Config::instance()->getConfig('auth_groups');
	}

	/**
	 * Authorizes user. Fill in authorization points for the user given
	 * the users groups.
	 *
	 * Returns true if the user is a member of one or more groups in the
	 * monitor system.
	 *
	 * @param   $user   op5User  The user to authorize
	 * @return          boolean     If the user is authorized
	 */
	public function authorize(op5User $user) {
		/* Fetch groups */
		$groups = $user->groups;

		/* Also allow the per-user-group */
		$groups[] = 'user_' . $user->username;
		$groups[] = 'meta_all_users';
		if(isset($user->auth_method) && $user->auth_method)
			$groups[] = 'meta_driver_' . $user->auth_method;
		$authorized = false;

		/* Fetch the name column as an array from the result */
		$auth_data = array();
		if(isset($user->auth_data)) {
			$auth_data = $user->auth_data;
		}
		foreach($groups as $group) {
			if(isset($this->groups[$group])) {
				$authorized = true;
				foreach($this->groups[$group] as $perm) {
					$auth_data[$perm] = true;
				}
			}
		}

		foreach($auth_data as $perm => $val) {
			$this->log->log('debug', $user->username . ": permission: " . $perm);
		}

		/* Store as auth_data */
		$user->auth_data = $auth_data;

		return $authorized;
	}

	/**
	 * Returns all available authorization points
	 *
	 * @return array
	 **/
	public static function get_all_auth_levels()
	{
		return array(
			'nagios_auth' => array(
				'system_information' => '',
				'configuration_information' => '',
				'system_commands' => ''),
			'api' => array(
				'api_command' => '',
				'api_config' => '',
				'api_report' => '',
				'api_status' => ''),
			'host' => array(
				'host_add_delete' => '',
				'host_view_all' => '',
				'host_view_contact' => '',
				'host_edit_all' => '',
				'host_edit_contact' => '',
				'test_this_host' => ''),
			'host_template' => array(
				'host_template_add_delete' => '',
				'host_template_view_all' => '',
				'host_template_edit_all' => ''),
			'service' => array(
				'service_add_delete' => '',
				'service_view_all' => '',
				'service_view_contact' => '',
				'service_edit_all' => '',
				'service_edit_contact' => '',
				'test_this_service' => ''),
			'service_template' => array(
				'service_template_add_delete' => '',
				'service_template_view_all' => '',
				'service_template_edit_all' => ''),
			'hostgroup' => array(
				'hostgroup_add_delete' => '',
				'hostgroup_view_all' => '',
				'hostgroup_view_contact' => '',
				'hostgroup_edit_all' => '',
				'hostgroup_edit_contact' => ''),
			'servicegroup' => array(
				'servicegroup_add_delete' => '',
				'servicegroup_view_all' => '',
				'servicegroup_view_contact' => '',
				'servicegroup_edit_all' => '',
				'servicegroup_edit_contact' => ''),
			'hostdependency' => array(
				'hostdependency_add_delete' => '',
				'hostdependency_view_all' => '',
				'hostdependency_edit_all' => ''),
			'servicedependency' => array(
				'servicedependency_add_delete' => '',
				'servicedependency_view_all' => '',
				'servicedependency_edit_all' => ''),
			'hostescalation' => array(
				'hostescalation_add_delete' => '',
				'hostescalation_view_all' => '',
				'hostescalation_edit_all' => ''),
			'serviceescalation' => array(
				'serviceescalation_add_delete' => '',
				'serviceescalation_view_all' => '',
				'serviceescalation_edit_all' => ''),
			'contact' => array(
				'contact_add_delete' => '',
				'contact_view_contact' => '',
				'contact_view_all' => '',
				'contact_edit_contact' => '',
				'contact_edit_all' => ''),
			'contact_template' => array(
				'contact_template_add_delete' => '',
				'contact_template_view_all' => '',
				'contact_template_edit_all' => ''),
			'contactgroup' => array(
				'contactgroup_add_delete' => '',
				'contactgroup_view_contact' => '',
				'contactgroup_view_all' => '',
				'contactgroup_edit_contact' => '',
				'contactgroup_edit_all' => ''),
			'timeperiod' => array(
				'timeperiod_add_delete' => '',
				'timeperiod_view_all' => '',
				'timeperiod_edit_all' => ''),
			'command' => array(
				'command_add_delete' => '',
				'command_view_all' => '',
				'command_edit_all' => '',
				'test_this_command' => ''),
			'management_pack' => array(
				'management_pack_add_delete' => '',
				'management_pack_view_all' => '',
				'management_pack_edit_all' => ''),
			'configuration' => array(
				'export' => '',
				'configuration_all' => ''),
			'dokuwiki' => array(
				'wiki' => '',
				'wiki_admin' => ''),
			'nagvis' => array(
				'nagvis_add_delete' => '',
				'nagvis_view' => '',
				'nagvis_edit' => '',
				'nagvis_admin' => ''),
			'misc' => array(
				'FILE' => '',
				'access_rights' => '',
				'pnp' => '',
				'saved_filters_global' => '')
		);
	}

	/**
	 * Translates pre mon6 authorization points to post mon6 authorization points
	 *
	 * @param $access_rights array
	 * @return array
	 **/
	public static function nagios_rights_to_op5auth($access_rights)
	{
		$translated_access_levels = array();

		$translated_access_levels['wiki'] = true;
		$translated_access_levels['api_status'] = true;
		$translated_access_levels['api_report'] = true;
		if (array_search('authorized_for_system_commands', $access_rights) !== false && array_search('authorized_for_configuration_information', $access_rights) !== false) {
			if (array_search('authorized_for_all_hosts', $access_rights) !== false)
				return array_reduce(self::get_all_auth_levels(), function($a, $b) {return array_merge($a, array_keys($b));}, array());

			$translated_access_levels['test_this_service'] = true;
			$translated_access_levels['test_this_host'] = true;
			$translated_access_levels['test_this_command'] = true;
			$translated_access_levels['api_config'] = true;
			$translated_access_levels['api_command'] = true;
		}

		foreach ($access_rights as $value) {
			if (substr($value, 0, 15) === 'authorized_for_') {
				$value = substr($value, 15);
			}
			switch($value) {
				case 'all_hosts':
					$translated_access_levels['host_view_all'] = true;
					$translated_access_levels['hostgroup_view_all'] = true;
					$translated_access_levels['hostdependency_view_all'] = true;
					$translated_access_levels['hostescalation_view_all'] = true;
					break;
				case 'all_services':
					$translated_access_levels['service_view_all'] = true;
					$translated_access_levels['servicegroup_view_all'] = true;
					$translated_access_levels['servicedependency_view_all'] = true;
					$translated_access_levels['serviceescalation_view_all'] = true;
					break;
				case 'all_host_commands':
					$translated_access_levels['host_edit_all'] = true;
					$translated_access_levels['host_add_delete'] = true;
					$translated_access_levels['test_this_host'] = true;
					$translated_access_levels['hostgroup_add_delete'] = true;
					$translated_access_levels['hostgroup_edit_all'] = true;
					$translated_access_levels['hostdependency_edit_all'] = true;
					$translated_access_levels['hostescalation_edit_all'] = true;
					$translated_access_levels['hostdependency_add_delete'] = true;
					$translated_access_levels['hostescalation_add_delete'] = true;
					break;
				case 'all_service_commands':
					$translated_access_levels['service_edit_all'] = true;
					$translated_access_levels['test_this_service'] = true;
					$translated_access_levels['servicegroup_edit_all'] = true;
					$translated_access_levels['servicedependency_edit_all'] = true;
					$translated_access_levels['serviceescalation_edit_all'] = true;
					$translated_access_levels['servicedependency_add_delete'] = true;
					$translated_access_levels['serviceescalation_add_delete'] = true;
					break;
				case 'system_commands':
					$translated_access_levels['command_edit_all'] = true;
					$translated_access_levels['test_this_command'] = true;
					$translated_access_levels['export'] = true;
					/* fallthrough */
				case 'system_information':
					$translated_access_levels['command_view_all'] = true;
					/* fallthrough */
				case 'configuration_information':
					$translated_access_levels[$value] = true;
					break;
				default: // unknown, ignore
					break;
			}
		}
		return array_keys($translated_access_levels);
	}
}
