<?php
require_once ('op5/mayi.php');

/**
 * Add authorization rules to ninja, where each auth point maps to a set of allowed MayI rules.
 */
class user_mayi_authorization implements op5MayI_Constraints {
	/**
	 * ACL list for users authorization
	 *
	 * The ACL is defined as a string, where each line is either empty (ignored)
	 * or a string, containint three parts seperated by whitespaces (spaces, no
	 * tabs).
	 *
	 * First part is a group memebership, the string "always" or the string
	 * "authenticated". If the user is a member of the group, the line is
	 * matched, if not prefixed, by !, which means the line is executed if no
	 * match.
	 *
	 * Second part is a action subset match. If the action matches the part, the
	 * function returns the third part as status.
	 *
	 * @var string
	 */
	private $raw_acl = <<<EOF
always                      ninja.session:                                true
authenticated               ninja.auth:login                              true
!authenticated              ninja.auth:login                              false
always                      ninja:                                        true

always                      monitor.system.saved_filters:                 true

system_information          monitor.monitoring.status:read                true
system_information          monitor.monitoring.performance:read           true

!api_command                :read.api.command                             false
!api_command                :update.api.command                           false

!api_config                 :read.api.configuration                       false
!api_config                 :create.api.configuration                     false
!api_config                 :delete.api.configuration                     false
!api_config                 :update.api.configuration                     false

!api_report                 :view.api.report                              false
!api_status                 :view.api.status                              false

!api_command                :read.app.command                             false
!api_command                :update.app.command                           false

!api_config                 :read.app.configuration                       false
!api_config                 :create.app.configuration                     false
!api_config                 :delete.app.configuration                     false
!api_config                 :update.app.configuration                     false

!api_report                 :view.app.report                              false
!api_status                 :view.app.status                              false

!api_command                :read.local.command                           false
!api_command                :update.local.command                         false

!api_config                 :read.local.configuration                     false
!api_config                 :create.local.configuration                   false
!api_config                 :delete.local.configuration                   false
!api_config                 :update.local.configuration                   false

!api_report                 :view.local.report                            false
!api_status                 :view.local.status                            false

host_view_all               monitor.monitoring.hosts:read                 true
host_view_all               monitor.monitoring.comments:read              true
host_view_all               monitor.monitoring.downtimes:read             true
host_view_all               monitor.monitoring.downtimes.recurring:read   true
host_view_all               monitor.monitoring.notifications:read         true

host_view_contact           monitor.monitoring.hosts:read                 true
host_view_contact           monitor.monitoring.comments:read              true
host_view_contact           monitor.monitoring.downtimes:read             true
host_view_contact           monitor.monitoring.downtimes.recurring:read   true
host_view_contact           monitor.monitoring.notifications:read         true

service_view_all            monitor.monitoring.services:read              true
service_view_all            monitor.monitoring.comments:read              true
service_view_all            monitor.monitoring.downtimes:read             true
service_view_all            monitor.monitoring.downtimes.recurring:read   true
service_view_all            monitor.monitoring.notifications:read         true

service_view_contact        monitor.monitoring.services:read              true
service_view_contact        monitor.monitoring.comments:read              true
service_view_contact        monitor.monitoring.downtimes:read             true
service_view_contact        monitor.monitoring.downtimes.recurring:read   true
service_view_contact        monitor.monitoring.notifications:read         true

hostgroup_view_all          monitor.monitoring.hostgroups:read            true

hostgroup_view_contact      monitor.monitoring.hostgroups.view            true

servicegroup_view_all       monitor.monitoring.servicegroups:read         true

servicegroup_view_contact   monitor.monitoring.servicegroups:read         true

contact_view_contact        monitor.monitoring.contacts:read              true

contact_view_all            monitor.monitoring.contacts:read              true

contactgroup_view_contact   monitor.monitoring.contactgroups:read         true

contactgroup_view_all       monitor.monitoring.contactgroups:read         true

timeperiod_view_all         monitor.monitoring.timeperiods:read           true

command_view_all            monitor.monitoring.commands:read              true

logger_access               monitor.logger.messages:read                  true

manage_trapper              monitor.trapper.handlers:                     true

manage_trapper              monitor.trapper.log:                          true

manage_trapper              monitor.trapper.matchers:                     true

manage_trapper              monitor.trapper.modules:                      true

manage_trapper              monitor.trapper.traps:                        true
EOF;


	/**
	 * Processed result of the $raw_acl varible.
	 *
	 * @var array
	 */
	private $acl = array();

	/**
	 *  Add the event handler for this object
	 */
	public function __construct() {
		$rows = array_filter(explode("\n", $this->raw_acl));
		foreach($rows as $row) {
			$row_parts = array_values(array_filter(explode(" ", $row)));
			if(count($row_parts) != 3) {
				die("Invalid ACL line");
			}

			$negate = false;
			if($row_parts[0][0] == '!') {
				$negate = true;
				$row_parts[0] = substr($row_parts[0], 1);
			}

			$this->acl[] = array($row_parts[0], $negate, $row_parts[1], $row_parts[2] == 'true');
		}

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

		/* Map auth points to actions */
		if (!isset( $env['user'] )) {
			$messages[] = "You are not logged in";
			return false;
		}

		elseif (!isset( $env['user']['authorized'] )) {
			$messages[] = "Your are not assigned any rights and are therefore not allowed to do this";
			return false;
		}

		elseif (!$authenticated) {
			$messages[] = "You are not authenticated";
			return false;
		}

		$authpoints = $env['user']['authorized'];
		$authpoints['always'] = true;
		$authpoints['authenticated'] = $authenticated;

		foreach($this->acl as $acl_line) {
			list($access_rule, $negate, $action_pattern, $allow) = $acl_line;
			$access = isset($authpoints[$access_rule]) && $authpoints[$access_rule];

			if($negate)
				$access = !$access;

			if(!$access)
				continue;

			if($this->is_subset($action_pattern, $action))
				return $allow;
		}
		$messages[] = "You are not authorized for $action";
		return false;
	}
}

new user_mayi_authorization();
