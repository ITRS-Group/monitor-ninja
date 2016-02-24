<?php
require_once (__DIR__ . '/../config.php');

/**
 * User authentication and authorization library.
 *
 * @package Authorization
 * @author
 *
 * @copyright
 *
 * @license
 *
 */
class op5Authorization {
	/**
	 * Create an instance of Auth.
	 *
	 * @return object
	 */
	public static function factory() {
		return new self();
	}
	private $groups = null;
	public function __construct() {
		$this->groups = UserGroupPool_Model::all();
	}

	/**
	 * Authorizes user.
	 * Fill in authorization points for the user given
	 * the users groups.
	 *
	 * Returns true if the user is a member of one or more groups in the
	 * monitor system.
	 *
	 * @param $user User_Model
	 *        	The user to authorize
	 * @return boolean If the user is authorized
	 */
	public function authorize(User_Model $user) {
		/* Fetch groups */
		$groups = $user->get_groups();

		/* Also allow the per-user-group */
		$groups[] = 'user_' . $user->get_username();

		/**
		 * Meta groups (all users and group per driver)
		 */
		$groups[] = 'meta_all_users';
		if ($user->get_auth_method()) {
			$groups[] = 'meta_driver_' . $user->get_auth_method();
		}

		$authorized = false;

		/* Fetch the name column as an array from the result */
		$auth_data = $user->get_auth_data();
		foreach ($groups as $group) {
			$checkgroup = $this->groups->reduce_by('groupname', $group, '=')->one();
			if ($checkgroup) {
				$authorized = true;
				foreach ($checkgroup->get_rights() as $right) {
					$auth_data[$right] = true;
				}
			}
		}

		$user->set_auth_data($auth_data);
		return $authorized;
	}

	/**
	 * Returns all available authorization points
	 *
	 * @return array
	 *
	 */
	public static function get_all_auth_levels() {
		return array (
			'nagios_auth' => array ('system_information' => '',
				'configuration_information' => '','system_commands' => ''),
			'api' => array ('api_command' => '','api_config' => '',
				'api_report' => '','api_status' => '', 'api_perfdata' => ''),
			'host' => array ('host_add_delete' => '','host_view_all' => '',
				'host_view_contact' => '','host_edit_all' => '',
				'host_edit_contact' => '','test_this_host' => ''),
			'host_commands' => array ('host_command_acknowledge' => '', 'host_command_add_comment' => '',
				'host_command_schedule_downtime' => '', 'host_command_check_execution' => '',
				'host_command_event_handler' => '', 'host_command_flap_detection' => '',
				'host_command_notifications' => '', 'host_command_passive_check' => '',
				'host_command_schedule_check' => '', 'host_command_send_notification' => ''),
			'host_template' => array ('host_template_add_delete' => '',
				'host_template_view_all' => '','host_template_edit_all' => ''),
			'service' => array ('service_add_delete' => '',
				'service_view_all' => '','service_view_contact' => '',
				'service_edit_all' => '','service_edit_contact' => '',
				'test_this_service' => ''),
			'service_commands' => array ('service_command_acknowledge' => '', 'service_command_add_comment' => '',
				'service_command_schedule_downtime' => '', 'service_command_check_execution' => '',
				'service_command_event_handler' => '', 'service_command_flap_detection' => '',
				'service_command_notifications' => '', 'service_command_passive_check' => '',
				'service_command_schedule_check' => '', 'service_command_send_notification' => ''),
			'service_template' => array ('service_template_add_delete' => '',
				'service_template_view_all' => '',
				'service_template_edit_all' => ''),
			'hostgroup' => array ('hostgroup_add_delete' => '',
				'hostgroup_view_all' => '','hostgroup_view_contact' => '',
				'hostgroup_edit_all' => '','hostgroup_edit_contact' => ''),
			'hostgroup_commands' => array ('hostgroup_command_schedule_downtime' => '',
				'hostgroup_command_check_execution' => '', 'hostgroup_command_send_notifications' => ''),
			'servicegroup' => array ('servicegroup_add_delete' => '',
				'servicegroup_view_all' => '','servicegroup_view_contact' => '',
				'servicegroup_edit_all' => '','servicegroup_edit_contact' => ''),
			'servicegroup_commands' => array ('servicegroup_command_schedule_downtime' => '',
				'servicegroup_command_check_execution' => '', 'servicegroup_command_send_notifications' => ''),
			'hostdependency' => array ('hostdependency_add_delete' => '',
				'hostdependency_view_all' => '','hostdependency_edit_all' => ''),
			'servicedependency' => array ('servicedependency_add_delete' => '',
				'servicedependency_view_all' => '',
				'servicedependency_edit_all' => ''),
			'hostescalation' => array ('hostescalation_add_delete' => '',
				'hostescalation_view_all' => '','hostescalation_edit_all' => ''),
			'serviceescalation' => array ('serviceescalation_add_delete' => '',
				'serviceescalation_view_all' => '',
				'serviceescalation_edit_all' => ''),
			'contact' => array ('contact_add_delete' => '',
				'contact_view_contact' => '','contact_view_all' => '',
				'contact_edit_contact' => '','contact_edit_all' => ''),
			'contact_template' => array ('contact_template_add_delete' => '',
				'contact_template_view_all' => '',
				'contact_template_edit_all' => ''),
			'contactgroup' => array ('contactgroup_add_delete' => '',
				'contactgroup_view_contact' => '','contactgroup_view_all' => '',
				'contactgroup_edit_contact' => '','contactgroup_edit_all' => ''),
			'timeperiod' => array ('timeperiod_add_delete' => '',
				'timeperiod_view_all' => '','timeperiod_edit_all' => ''),
			'command' => array ('command_add_delete' => '',
				'command_view_all' => '','command_edit_all' => '',
				'test_this_command' => ''),
			'management_pack' => array ('management_pack_add_delete' => '',
				'management_pack_view_all' => '',
				'management_pack_edit_all' => ''),
			'configuration' => array ('export' => '','configuration_all' => ''),
			'dokuwiki' => array ('wiki' => '','wiki_admin' => ''),
			'nagvis' => array ('nagvis_add_delete' => '','nagvis_view' => '',
				'nagvis_edit' => '','nagvis_admin' => ''),
			'logger' => array('logger_access' => '', 'logger_configuration' => '', 'logger_schedule_archive_search' => ''),
			'business_services' => array('business_services_access' => ''),
			'misc' => array ('FILE' => '','access_rights' => '','pnp' => '',
				'manage_trapper' => '',
				'saved_filters_global' => ''));
	}

	/**
	 * Translates pre mon6 authorization points to post mon6 authorization
	 * points
	 *
	 * @param $access_rights array
	 * @return array
	 *
	 */
	public static function nagios_rights_to_op5auth($access_rights) {
		$translated_access_levels = array ();

		$translated_access_levels['wiki'] = true;
		$translated_access_levels['api_status'] = true;
		$translated_access_levels['api_report'] = true;
		if (array_search('authorized_for_system_commands', $access_rights) !==
			 false &&
			 array_search('authorized_for_configuration_information',
				$access_rights) !== false) {
			if (array_search('authorized_for_all_hosts', $access_rights) !==
			 false)
			return array_reduce(self::get_all_auth_levels(),
				function ($a, $b) {
					return array_merge($a, array_keys($b));
				}, array ());

		$translated_access_levels['test_this_service'] = true;
		$translated_access_levels['test_this_host'] = true;
		$translated_access_levels['test_this_command'] = true;
		$translated_access_levels['api_config'] = true;
		$translated_access_levels['api_command'] = true;
		$translated_access_levels['api_perfdata'] = true;
	}

	foreach ($access_rights as $value) {
		if (substr($value, 0, 15) === 'authorized_for_') {
			$value = substr($value, 15);
		}
		switch ($value) {
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
