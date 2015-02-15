<?php
require_once ('op5/mayi.php');

/**
 * Add authorization rules to ninja, where each auth point maps to a set of allowed MayI rules.
 */
class user_mayi_authorization implements op5MayI_Constraints {

	/* If a user has an old auth access right that is a key in this array,
	 * she gains all rights represented by that key's values.
	 *
	 * Every value matching ^ninja.* should not be defined here, see @run()
	 * for more information.
	 */
	private $access_rules = array (
		'always' => array(
			'monitoring.lsfilter.saved_filters:',
			'ninja:',
			'http_api:'
		),
		'system_information' => array (
			'monitoring.status:view',
			'monitoring.performance:view'
		),
		'configuration_information' => array (),
		'system_commands' => array (),
		'api_command' => array (
			'monitoring.hosts:view.api.command',
			'monitoring.hosts:update.api.command'
		),
		'api_config' => array (
			'monitoring.hosts:view.api.configuration',
			'monitoring.hosts:create.api.configuration',
			'monitoring.hosts:delete.api.configuration',
			'monitoring.hosts:update.api.configuration'
		),
		'api_perfdata' => array (
			'monitoring.hosts:view.api.perfdata'
		),
		'api_report' => array (
			'monitoring.hosts:view.api.report'
		),
		'api_status' => array (
			'monitoring.hosts:view.api.status'
		),
		'host_add_delete' => array (),
		'host_view_all' => array (
			'monitoring.hosts:view',
			'monitoring.comments:view',
			'monitoring.downtimes:view',
			'monitoring.recurringdowntimes:view',
			'monitoring.notifications:view'
		),
		'host_view_contact' => array (
			'monitoring.hosts:view',
			'monitoring.comments:view',
			'monitoring.downtimes:view',
			'monitoring.recurringdowntimes:view',
			'monitoring.notifications:view'
		),
		'host_edit_all' => array (),
		'host_edit_contact' => array (),
		'test_this_host' => array (),
		'host_template_add_delete' => array (),
		'host_template_view_all' => array (),
		'host_template_edit_all' => array (),
		'service_add_delete' => array (),
		'service_view_all' => array (
			'monitoring.services:view',
			'monitoring.comments:view',
			'monitoring.downtimes:view',
			'monitoring.recurringdowntimes:view',
			'monitoring.notifications:view'
		),
		'service_view_contact' => array (
			'monitoring.services:view',
			'monitoring.comments:view',
			'monitoring.downtimes:view',
			'monitoring.recurringdowntimes:view',
			'monitoring.notifications:view'
		),
		'service_edit_all' => array (),
		'service_edit_contact' => array (),
		'test_this_service' => array (),
		'service_template_add_delete' => array (),
		'service_template_view_all' => array (),
		'service_template_edit_all' => array (),
		'hostgroup_add_delete' => array (),
		'hostgroup_view_all' => array (
			'monitoring.hostgroups:view'
		),
		'hostgroup_view_contact' => array (
			'monitoring.hostgroups.view'
		),
		'hostgroup_edit_all' => array (),
		'hostgroup_edit_contact' => array (),
		'servicegroup_add_delete' => array (),
		'servicegroup_view_all' => array (
			'monitoring.servicegroups:view'
		),
		'servicegroup_view_contact' => array (
			'monitoring.servicegroups:view'
		),
		'servicegroup_edit_all' => array (),
		'servicegroup_edit_contact' => array (),
		'hostdependency_add_delete' => array (),
		'hostdependency_view_all' => array (),
		'hostdependency_edit_all' => array (),
		'servicedependency_add_delete' => array (),
		'servicedependency_view_all' => array (),
		'servicedependency_edit_all' => array (),
		'hostescalation_add_delete' => array (),
		'hostescalation_view_all' => array (),
		'hostescalation_edit_all' => array (),
		'serviceescalation_add_delete' => array (),
		'serviceescalation_view_all' => array (),
		'serviceescalation_edit_all' => array (),
		'contact_add_delete' => array (),
		'contact_view_contact' => array (
			'monitoring.contacts:view'
		),
		'contact_view_all' => array (
			'monitoring.contacts:view'
		),
		'contact_edit_contact' => array (),
		'contact_edit_all' => array (),
		'contact_template_add_delete' => array (),
		'contact_template_view_all' => array (),
		'contact_template_edit_all' => array (),
		'contactgroup_add_delete' => array (),
		'contactgroup_view_contact' => array (
			'monitoring.contactgroups:view'
		),
		'contactgroup_view_all' => array (
			'monitoring.contactgroups:view'
		),
		'contactgroup_edit_contact' => array (),
		'contactgroup_edit_all' => array (),
		'timeperiod_add_delete' => array (),
		'timeperiod_view_all' => array (
			'monitoring.timeperiods:view'
		),
		'timeperiod_edit_all' => array (),
		'command_add_delete' => array (),
		'command_view_all' => array (
			'monitoring.commands:view'
		),
		'command_edit_all' => array (),
		'test_this_command' => array (),
		'management_pack_add_delete' => array (),
		'management_pack_view_all' => array (),
		'management_pack_edit_all' => array (),
		'export' => array (),
		'configuration_all' => array (),
		'wiki' => array (),
		'wiki_admin' => array (),
		'nagvis_add_delete' => array (),
		'nagvis_view' => array (),
		'nagvis_edit' => array (),
		'nagvis_admin' => array (),
		'logger_access' => array (
			'logger.messages:view'
		),
		'logger_configuration' => array (),
		'logger_schedule_archive_search' => array (),
		'FILE' => array (),
		'access_rights' => array (),
		'pnp' => array (),
		'manage_trapper' => array (),
		'saved_filters_global' => array ()
	);

	/**
	 *  Add the event handler for this object
	 */
	public function __construct() {
		Event::add( 'system.ready', array (
			$this,
			'populate_mayi'
		) );
	}
	/**
	 * On system.ready, add this class as a MayI constraint
	 */
	public function populate_mayi() {
		op5MayI::instance()->act_upon( $this );
	}
	private function is_subset($subset, $world) {
		$subset_parts = explode( ':', $subset );
		$world_parts = explode( ':', $world );

		$count = count( $subset_parts );

		if ($count != count( $world_parts ))
			return false;

		for($i = 0; $i < $count; $i ++) {
			$subset_attr = array_filter( explode( '.', $subset_parts[$i] ) );
			$world_attr = array_filter( explode( '.', $world_parts[$i] ) );

			/* If this part isn't a subset bail out */
			if (array_slice( $world_attr, 0, count( $subset_attr ) ) != $subset_attr) {
				return false;
			}
		}

		/* We passed all parts, accept */
		return true;
	}

	/**
	 * Execute a action
	 *
	 * @param $action
	 *        	name of the action, as "path.to.resource:action"
	 * @param $env
	 *        	environment variables for the constraints
	 * @param $messages
	 *        	referenced array to add messages to
	 * @param $perfdata
	 *        	referenced array to add performance data to
	 */
	public function run($action, $env, &$messages, &$perfdata) {
		/*
		 * The ninja:-resource is a little bit special. It contains more
		 * meta-permissions.
		 *
		 * The general rule is that: ninja: should be available when logged in,
		 * except for ninja.auth:login, which should be visible when logged out.
		 */
		$authenticated =  isset( $env['user'] ) && isset( $env['user']['authenticated'] ) && $env['user']['authenticated'];
		if ($this->is_subset( 'ninja.auth:login', $action )) {
			return !$authenticated;
		}

		/* Map auth points to actions */
		if (!isset( $env['user'] ))
			return false;
		if (!isset( $env['user']['authorized'] ))
			return false;
		if (!$authenticated)
			return false;

		$authpoints = $env['user']['authorized'];
		$authpoints['always'] = true;

		foreach ( $authpoints as $authpoint => $allow ) {
			if (! $allow)
				continue;
			if (!isset( $this->access_rules[$authpoint] ))
				continue;
			foreach ( $this->access_rules[$authpoint] as $match ) {
				if ($this->is_subset( $match, $action )) {
					return true;
				}
			}
		}
		return false;
	}
}

new user_mayi_authorization();
