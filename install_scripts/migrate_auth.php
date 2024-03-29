<?php

if(PHP_SAPI !== 'cli') {
	die("This script should never be run from a browser, execute it from commmand-line instead.");
}

$considered_superadmin = 'system_commands';

/**
 * [version => [existing => new]]
 * or [version => function($group_name, &$permissions): bool]
 *   where function returns true if it changed the $permissions array.
 */
$new_rights = array(
	0 => array(
		'hostgroup_view_all' => 'management_pack_view_all',
		'hostgroup_edit_all' => 'management_pack_edit_all',
		'hostgroup_add_delete' => 'management_pack_add_delete',
	),
	1 => array(
		'api_config' => 'api_command'
	),
	2 => array(
		$considered_superadmin => 'manage_trapper'
	),
	3 => array(
		'host_view_all' => 'logger_access',
		$considered_superadmin => array(
			'logger_configuration',
			'logger_schedule_archive_search'
		)
	),
	4 => array(
		'host_edit_all' => array(
			'host_command_acknowledge',
			'host_command_add_comment',
			'host_command_schedule_downtime',
			'host_command_check_execution',
			'host_command_event_handler',
			'host_command_flap_detection',
			'host_command_notifications',
			'host_command_obsess',
			'host_command_passive_check',
			'host_command_schedule_check',
			'host_command_send_notification'
		),
		'service_edit_all' => array(
			'service_command_acknowledge',
			'service_command_add_comment',
			'service_command_schedule_downtime',
			'service_command_check_execution',
			'service_command_event_handler',
			'service_command_flap_detection',
			'service_command_notifications',
			'service_command_obsess',
			'service_command_passive_check',
			'service_command_schedule_check',
			'service_command_send_notification'
		),
		'hostgroup_edit_all' => array(
			'hostgroup_command_schedule_downtime',
			'hostgroup_command_check_execution',
			'hostgroup_command_send_notifications'
		),
		'servicegroup_edit_all' => array(
			'servicegroup_command_schedule_downtime',
			'servicegroup_command_check_execution',
			'servicegroup_command_send_notifications'
		)
	),
	5 => array(
		'service_view_all' => array(
			'business_services_access',
		),
		'service_view_contact' => array(
			'business_services_access',
		)
	),
	6 => array(
		$considered_superadmin => array(
			'dashboard_share',
		)
	),
	7 => array(
		'service_view_all' => array(
			'traps_view_all',
		)
	),
	11 => function($group_name, &$permissions) {
		// Remove 'test_this_host' and 'test_this_service' from the 'limited_edit' group
		if ($group_name != "limited_edit")
			return;
		$changed = false;
		foreach (array('test_this_host', 'test_this_service') as $perm) {
			$index = array_search($perm, $permissions);
			if ($index !== false) {
				$changed = true;
				echo "INFO: Migrate auth_groups: removing permission '$perm' from '$group_name' group.\n";
				unset($permissions[$index]);
			}
		}
		return $changed;
	},
	12 => function($group_name, &$permissions) {
		// Remove docuwiki permissions
		$changed = false;
		foreach (array('wiki', 'wiki_admin') as $perm) {
			$index = array_search($perm, $permissions);
			if ($index !== false) {
				$changed = true;
				unset($permissions[$index]);
			}
		}
		return $changed;
	},
	13 => function($group_name, &$permissions) {
		// Remove logger permissions
		$changed = false;
		foreach (array('logger_access', 'logger_configuration', 'logger_schedule_archive_search') as $perm) {
			$index = array_search($perm, $permissions);
			if ($index !== false) {
				$changed = true;
				unset($permissions[$index]);
			}
		}
		return $changed;
	},
);

require_once('op5/config.php');
$c = op5config::instance();
$config = $c->getConfig('auth');
$groups = $c->getConfig('auth_groups');

$current_version = 0;
if(isset($config['common']['version'])) {
       $current_version = $config['common']['version'];
}
$new_version = $current_version;

foreach ($groups as $group_name => &$group) {
	foreach ($new_rights as $version => $perms) {
		if ($version <= $current_version)
			continue;
		$new_version = $version;
		if (is_callable($perms)) {
			$changed = $perms($group_name, $group);
			if ($changed) {
				$group = array_values($group);
			}
		}
		else {
			foreach ($perms as $from => $to) {
				if (in_array($from, $group)) {
					if (is_array($to)) {
						foreach ($to as $perm) {
							$group[] = $perm;
						}
					} else {
						$group[] = $to;
					}
				}
			}
		}
	}
}
$config['common']['version'] = $new_version;

$c->setConfig('auth', $config);
$c->setConfig('auth_groups', $groups);
