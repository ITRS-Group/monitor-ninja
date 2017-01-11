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
	 * Only the first rule that matches the current action subset decides
	 * the outcome, that's why we must always start with the most severe
	 * rules (both permissive and restrictive).
	 */
	private $raw_acl = <<<EOF
always                          ninja.session:                                                true

!authenticated                  ninja.auth:login                                              true
authenticated                   ninja.auth:login                                              false

authenticated                   ninja:                                                        true

authenticated                   monitor.system.saved_filters:                                 true
authenticated                   monitor.reports.saved_reports:                                true
authenticated                   monitor.monitoring.columns:                                   true
authenticated                   monitor.system.settings:                                      true
authenticated                   monitor.system.widgets:                                       true
authenticated                   monitor.system.permission_quarks:                             true
configuration_information       monitor.system.backup:                                        true

authenticated                   monitor.system.dashboards:read                                true
dashboard_share                 monitor.system.dashboards:{create,update,delete}              true
authenticated                   monitor.system.dashboards.personal:{create.update.delete}     true

!api_command                    :read.api.command                                             false
!api_command                    :update.api.command                                           false

!api_config                     :read.api.configuration                                       false
!api_config                     :create.api.configuration                                     false
!api_config                     :delete.api.configuration                                     false
!api_config                     :update.api.configuration                                     false

!api_report                     :view.api.report                                              false
!api_status                     :view.api.status                                              false

!api_command                    :read.app.command                                             false
!api_command                    :update.app.command                                           false

!api_config                     :read.app.configuration                                       false
!api_config                     :create.app.configuration                                     false
!api_config                     :delete.app.configuration                                     false
!api_config                     :update.app.configuration                                     false

!api_report                     :view.app.report                                              false
!api_status                     :view.app.status                                              false

!api_command                    :read.local.command                                           false
!api_command                    :update.local.command                                         false

!api_config                     :read.local.configuration                                     false
!api_config                     :create.local.configuration                                   false
!api_config                     :delete.local.configuration                                   false
!api_config                     :update.local.configuration                                   false

!api_report                     :view.local.report                                            false
!api_status                     :view.local.status                                            false

system_information              monitor.support:read                                          true

system_information              monitor.monitoring.status:read                                true
system_information              monitor.monitoring.performance:read                           true
system_commands                 monitor.monitoring.status:update                              true

host_command_acknowledge        monitor.monitoring.hosts:update.command.acknowledge           true
host_command_add_comment        monitor.monitoring.hosts:update.command.comment               true
host_edit_{all,contact}         monitor.monitoring.hosts:update.command.configure             true
host_command_schedule_downtime  monitor.monitoring.hosts:update.command.downtime              true
host_command_check_execution    monitor.monitoring.hosts:update.command.enabled               true
host_command_event_handler      monitor.monitoring.hosts:update.command.event_handler         true
host_command_flap_detection     monitor.monitoring.hosts:update.command.flap_detection        true
host_command_notifications      monitor.monitoring.hosts:update.command.notification          true
host_command_obsess             monitor.monitoring.hosts:update.command.obsess                true
host_command_passive_check      monitor.monitoring.hosts:update.command.passive               true
host_command_schedule_check     monitor.monitoring.hosts:update.command.schedule              true
host_command_send_notification  monitor.monitoring.hosts:update.command.send_notification     true

service_command_acknowledge        monitor.monitoring.services:update.command.acknowledge           true
service_command_add_comment        monitor.monitoring.services:update.command.comment               true
service_edit_{all,contact}         monitor.monitoring.services:update.command.configure             true
service_command_schedule_downtime  monitor.monitoring.services:update.command.downtime              true
service_command_check_execution    monitor.monitoring.services:update.command.enabled               true
service_command_event_handler      monitor.monitoring.services:update.command.event_handler         true
service_command_flap_detection           monitor.monitoring.services:update.command.flap_detection              true
service_command_notifications      monitor.monitoring.services:update.command.notification          true
service_command_obsess             monitor.monitoring.services:update.command.obsess                true
service_command_passive_check      monitor.monitoring.services:update.command.passive               true
service_command_schedule_check     monitor.monitoring.services:update.command.schedule              true
service_command_send_notification  monitor.monitoring.services:update.command.send_notification     true

hostgroup_edit_{all,contact}        monitor.monitoring.hostgroups:update.command.configure     true
hostgroup_command_schedule_downtime monitor.monitoring.hostgroups:update.command.downtime      true
hostgroup_command_check_execution   monitor.monitoring.hostgroups:update.command.enabled       true
hostgroup_command_send_notifications     monitor.monitoring.hostgroups:update.command.notification  true

servicegroup_edit_{all,contact}        monitor.monitoring.servicegroups:update.command.configure     true
servicegroup_command_schedule_downtime monitor.monitoring.servicegroups:update.command.downtime      true
servicegroup_command_check_execution   monitor.monitoring.servicegroups:update.command.enabled       true
servicegroup_command_send_notifications     monitor.monitoring.servicegroups:update.command.notification  true

always                          :update.command                                               false

configuration_information       monitor.monitoring.users:read.local.configuration             true
access_rights                   monitor.monitoring.users:                                     true
access_rights                   monitor.system.users:                                         true
access_rights                   monitor.monitoring.usergroups:                                true
access_rights                   monitor.system.usergroups:                                    true
access_rights                   monitor.system.authmodules:                                   true

pnp                             monitor.monitoring.combined_graphs:                           true
pnp                             monitor.monitoring.graph_collections:                         true
pnp                             monitor.monitoring.graph_templates:                           true

nagvis_add_delete               monitor.nagvis.maps:{create,delete}                           true
nagvis_view                     monitor.nagvis.maps:read                                      true
nagvis_edit                     monitor.nagvis.maps:{create,update,delete}                    true
nagvis_admin                    monitor.nagvis.maps:                                          true
nagvis_admin                    monitor.nagvis.permissions:                                   true

hostescalation_view_all         monitor.monitoring.hostescalations:read                       true
hostescalation_edit_all         monitor.monitoring.hostescalations:{create,update,delete}     true
hostescalation_add_delete       monitor.monitoring.hostescalations:{create,delete}            true

serviceescalation_view_all      monitor.monitoring.serviceescalations:read                    true
serviceescalation_edit_all      monitor.monitoring.serviceescalations:{create,update,delete}  true
serviceescalation_add_delete    monitor.monitoring.serviceescalations:{create,delete}         true

own_user_change_password        monitor.system.users.password:update                          true

command_add_delete              monitor.monitoring.commands:{create,delete}                   true
command_view_all                monitor.monitoring.commands:read                              true
command_edit_all                monitor.monitoring.commands:{create,update,delete}            true

management_pack_view_all        monitor.monitoring.management_packs:read                      true
management_pack_edit_all        monitor.monitoring.management_packs:{create,update,delete}    true
management_pack_add_delete      monitor.monitoring.management_packs:{create,delete}           true

test_this_command               monitor.monitoring.commands:update.test                       true
test_this_service               monitor.monitoring.services:update.test                       true
test_this_host                  monitor.monitoring.hosts:update.test                          true

host_template_view_all          monitor.monitoring.host_templates:read                        true
host_template_edit_all          monitor.monitoring.host_templates:{create,update,delete}      true
host_template_add_delete        monitor.monitoring.host_templates:{create,delete}             true

service_template_view_all       monitor.monitoring.service_templates:read                     true
service_template_edit_all       monitor.monitoring.service_templates:{create,update,delete}   true
service_template_add_delete     monitor.monitoring.service_templates:{create,delete}          true

host_view_{all,contact}         monitor.monitoring.hosts:read                                 true
host_view_{all,contact}         monitor.monitoring.comments:read                              true
host_view_{all,contact}         monitor.monitoring.downtimes:read                             true
host_view_{all,contact}         monitor.monitoring.downtimes.recurring:read                   true
host_view_{all,contact}         monitor.monitoring.notifications:read                         true

host_edit_{all,contact}         monitor.monitoring.hosts:{create,update}                      true
host_edit_{all,contact}         monitor.monitoring.comments:{create,update,delete}            true
host_edit_{all,contact}         monitor.monitoring.downtimes:{create,update,delete}           true
host_edit_{all,contact}         monitor.monitoring.downtimes.recurring:{create,update,delete} true
host_edit_{all,contact}         monitor.monitoring.notifications:{create,update,delete}       true

host_add_delete                 monitor.monitoring.hosts:{create,delete}                      true

hostdependency_view_all         monitor.monitoring.hostdependencies:read                      true
hostdependency_edit_all         monitor.monitoring.hostdependencies:{create,update,delete}    true
hostdependency_add_delete       monitor.monitoring.hostdependencies:{create,delete}           true

servicedependency_view_all      monitor.monitoring.servicedependencies:read                   true
servicedependency_edit_all      monitor.monitoring.servicedependencies:{create,update,delete} true
servicedependency_add_delete    monitor.monitoring.servicedependencies:{create,delete}        true

service_view_{all,contact}      monitor.monitoring.services:read                              true
service_view_{all,contact}      monitor.monitoring.comments:read                              true
service_view_{all,contact}      monitor.monitoring.downtimes:read                             true
service_view_{all,contact}      monitor.monitoring.downtimes.recurring:read                   true
service_view_{all,contact}      monitor.monitoring.notifications:read                         true

service_edit_{all,contact}      monitor.monitoring.services:{create,update}                   true
service_edit_{all,contact}      monitor.monitoring.comments:{create,update,delete}            true
service_edit_{all,contact}      monitor.monitoring.downtimes:{create,update,delete}           true
service_edit_{all,contact}      monitor.monitoring.downtimes.recurring:{create,update,delete} true
service_edit_{all,contact}      monitor.monitoring.notifications:{create,update,delete}       true

service_add_delete              monitor.monitoring.services:{create,delete}                   true

hostgroup_view_{all,contact}    monitor.monitoring.hostgroups:read                            true
hostgroup_edit_{all,contact}    monitor.monitoring.hostgroups:{create,update}                 true
hostgroup_add_delete            monitor.monitoring.hostgroups:{create,delete}                 true

servicegroup_view_{all,contact} monitor.monitoring.servicegroups:read                         true
servicegroup_edit_{all,contact} monitor.monitoring.servicegroups:{create,update}              true
servicegroup_add_delete         monitor.monitoring.servicegroups:{create,delete}              true

contact_view_{all,contact}      monitor.monitoring.contacts:read                              true
contact_edit_{all,contact}      monitor.monitoring.contacts:{create,update,delete}            true
contact_add_delete              monitor.monitoring.contacts:{create,delete}                   true

contact_template_view_all       monitor.monitoring.contact_templates:read                     true
contact_template_edit_all       monitor.monitoring.contact_templates:{create,update,delete}   true
contact_template_add_delete     monitor.monitoring.contact_templates:{create,delete}          true

contactgroup_view_{all,contact} monitor.monitoring.contactgroups:read                         true
contactgroup_edit_{all,contact} monitor.monitoring.contactgroups:{create,update,delete}       true
contactgroup_add_delete         monitor.monitoring.contactgroups:{create,delete}              true

timeperiod_view_all             monitor.monitoring.timeperiods:read                           true
timeperiod_edit_all             monitor.monitoring.timeperiods:{create,update,delete}         true
timeperiod_add_delete           monitor.monitoring.timeperiods:{create,delete}                true

command_view_all                monitor.monitoring.commands:read                              true
command_edit_all                monitor.monitoring.commands:{create,update,delete}            true

logger_access                   monitor.logger.messages:read                                  true
logger_configuration            monitor.logger.settings:update                                true

manage_trapper                  monitor.trapper.handlers:                                     true
manage_trapper                  monitor.trapper.log:                                          true
manage_trapper                  monitor.trapper.matchers:                                     true
manage_trapper                  monitor.trapper.modules:                                      true
manage_trapper                  monitor.trapper.traps:                                        true

traps_view_all                  monitor.trapper.traps:read                                    true
system_information              monitor.license:{read,create,update,delete}                   true

business_services_access        monitor.bsm:{create,read,update,delete}                       true
EOF;

	/**
	 * Processed result of the $raw_acl varible.
	 */
	private $acl = array();

	/**
	 * Result cache
	 */
	private $cache = array();

	/**
	 * Convert the input line containing {xxx,yyy} segments to a list of all segments unpacked
	 *
	 * For example:
	 *
	 * aaa_{b,c} kaka {xx,yyy}
	 *
	 * would be unpacked to four lines:
	 *
	 * aaa_b kaka xx
	 * aaa_c kaka xx
	 * aaa_b kaka yyy
	 * aaa_c kaka yyy
	 *
	 * Used to preprocess the ACL to easier handle {create,update,delete} lines
	 *
	 * @param dyting $rawrow
	 * @return multitype:string
	 */
	private function preprocess_row($rawrow) {
		$splitted = preg_split('/\{([^{}]+)\}/', $rawrow, NULL, PREG_SPLIT_DELIM_CAPTURE);
		$rows = array('');
		while(count($splitted) > 0) {
			$prefix = array_shift($splitted);
			$rows = array_map(function($value) use ($prefix) { return $value.$prefix; }, $rows);
			if(count($splitted) > 0) {
				$choices = explode(',',array_shift($splitted));
				$newrows = array();
				foreach($choices as $choice) {
					$newrows = array_merge($newrows,
							array_map(function($value) use ($choice) { return $value.$choice; }, $rows)
					);
				}
				$rows = $newrows;
			}
		}
		return $rows;
	}

	/**
	 *  Add the event handler for this object
	 */
	public function __construct() {
		$rows = array_filter(explode("\n", $this->raw_acl));
		foreach($rows as $rawrow) {
			$processed_rows = $this->preprocess_row($rawrow);
			foreach($processed_rows as $row) {
				$row_parts = array_values(array_filter(explode(" ", $row)));
				if(count($row_parts) != 3) {
					die("Invalid ACL line");
				}

				$negate = false;
				if($row_parts[0][0] == '!') {
					$negate = true;
					$row_parts[0] = substr($row_parts[0], 1);
				}
				$mayimethod = op5MayI::explode_namespace_set($row_parts[1]);
				$this->acl[] = array($row_parts[0], $negate, $mayimethod, $row_parts[2] == 'true');
			}
		}

		Event::add( 'system.ready', array (
			$this,
			'populate_mayi'
		) );
	}

	/**
	 * Let's expose the parsed acl so that it can be tested.
	 *
	 * @return array
	 */
	public function get_acl() {
		return $this->acl;
	}

	/**
	 * On system.ready, add this class as a MayI constraint
	 */
	public function populate_mayi() {
		op5MayI::instance()->act_upon( $this, 10 );
	}

	/**
	 * Execute a action
	 *
	 * @param $action
	 *          name of the action, as "path.to.resource:action"
	 * @param $env
	 *          environment variables for the constraints
	 * @param $messages
	 *          referenced array to add messages to
	 * @param $perfdata
	 *          referenced array to add performance data to
	 */
	public function run($action, $env, &$messages, &$perfdata) {

		if(isset($this->cache[$action])) {
			if($this->cache[$action]['msg'] !== false)
				$messages[] = $this->cache[$action]['msg'];
			return $this->cache[$action]['result'];
		}

		$msg = false;

		$action_exploded = op5MayI::explode_namespace_set($action);

		/*
		 * The ninja:-resource is a little bit special. It contains more
		 * meta-permissions.
		 *
		 * The general rule is that: ninja: should be available when logged in,
		 * except for ninja.auth:login, which should be visible when logged out.
		 */
		$authenticated =  isset( $env['user'] ) && isset( $env['user']['authenticated'] ) && $env['user']['authenticated'];

		$authpoints = array();

		if( isset($env['user']) && isset($env['user']['authorized']))
			$authpoints = $env['user']['authorized'];

		$authpoints['always'] = true;
		$authpoints['authenticated'] = $authenticated;

		$denied_rules = array();
		// set _once_ only, otherwise rules such as "always" could
		// be overridden later on.. the order in the ACL matters
		$is_allowed = null;

		foreach($this->acl as $acl_line) {
			list($access_rule, $negate, $action_pattern, $acl_allow) = $acl_line;
			$user_access = isset($authpoints[$access_rule]) && $authpoints[$access_rule];
			if($negate)
				$user_access = !$user_access;

			if(op5MayI::is_subset_exploded($action_exploded, $action_pattern)) {
				if(!$acl_allow || !$user_access) {
					/*Display access right in the same manner as the "Grouprights" page */
					$denied_rules[] = ucwords(str_replace('_', ' ', $access_rule));
				}
				if ($user_access)
					$is_allowed = is_null($is_allowed) ? $acl_allow : $is_allowed;
			}
		}

		if (is_null($is_allowed)) {
			/* We found no explicit authpoint, and no default access for this action */
			/* Default to deny */
			$is_allowed = false;
		}

		if (count($denied_rules) == 0) {
			/* Use pure action for troubleshooting purposes, what else could we do?*/
			$denied_rules[] = $action;
		}

		if (!$is_allowed) {
			if (count($denied_rules) === 1) {
				$msg = "You are not authorized for the " . $denied_rules[0] . " access right and it is required to perform this action.";
			}
			else {
				$last_rule = array_pop($denied_rules);
				$rules_list = implode(",", array_map(function($r) {
					return "'$r'";
				}, $denied_rules));
				$msg = "You are not authorized for neither of the " . $rules_list . " nor '" . $last_rule . "' access rights. One or more of them may be required to perform this action";
			}
			$messages[] = $msg;
		}

		$this->cache[$action] = array(
			'msg' => $msg,
			'result' => $is_allowed
		);

		return $is_allowed;
	}
}

new user_mayi_authorization();
