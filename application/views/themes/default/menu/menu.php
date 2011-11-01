<?php
if (isset($user_auth)) {
	$auth = $user_auth;
} else {
	$auth = new Nagios_auth_Model();
}

# translate menu labels
$menu_items = false;
$menu_items['section_about'] = _('About');
$menu_items['portal'] = _('op5 Portal');
$menu_items['manual'] = _('op5 Monitor manual');
$menu_items['support'] = _('op5 Support portal');
$menu_items['ninja_project'] = _('The Ninja project');
$menu_items['merlin_project'] = _('The Merlin project');
$menu_items['project_documentation'] = _('Project documentation');

$menu_items['section_monitoring'] = _('Monitoring');
$menu_items['tac'] = _('Tactical overview');
$menu_items['host_detail'] = _('Host detail');
$menu_items['service_detail'] = _('Service detail');

$menu_items['hostgroup_summary'] = _('Hostgroup summary');
$menu_items['hostgroup_overview'] = _('Hostgroup overview');
$menu_items['hostgroup_grid'] = _('Hostgroup grid');

$menu_items['servicegroup_summary'] = _('Servicegroup summary');
$menu_items['servicegroup_overview'] = _('Servicegroup overview');
$menu_items['servicegroup_grid'] = _('Servicegroup grid');

$menu_items['network_outages'] = _('Network outages');
$menu_items['host_problems'] = _('Host problems');
$menu_items['service_problems'] = _('Service problems');
$menu_items['unhandled_problems'] = _('Unhandled problems');

$menu_items['comments'] = _('Comments');
$menu_items['schedule_downtime'] = _('Schedule downtime');
$menu_items['process_info'] = _('Process info');
$menu_items['performance_info'] = _('Performance info');
$menu_items['scheduling_queue'] = _('Scheduling queue');

if (Kohana::config('hypergraph.hyperapplet_path') !== false) {
	$menu_items['hyper_map'] = _('Hyper Map');
}

/* remove hardcoded nagvis menu entry
if (Kohana::config('config.nagvis_path') !== false) {
	$menu_items['nagvis'] = _('Nagvis');
}
*/

$menu_items['section_reporting'] = _('Reporting');
$menu_items['trends'] = _('Trends');
$menu_items['pnp'] = _('PNP');
$menu_items['alert_history'] = _('Alert history');
$menu_items['alert_summary'] = _('Alert summary');
$menu_items['notifications'] = _('Notifications');
$menu_items['event_log'] = _('Event log');
$menu_items['availability'] = _('Availability');
$menu_items['sla'] = _('SLA Reporting');
$menu_items['schedule_reports'] = _('Schedule reports');

if (Kohana::config('config.cacti_path')) {
	$menu_items['statistics'] = _('Statistics');
}

if ($auth->authorized_for_configuration_information || Kohana::config('config.nacoma_path')===false) {
	$menu_items['configure'] = _('Configure');
}

$menu_items['section_configuration'] = _('Configuration');
$menu_items['view_config'] = _('View config');
$menu_items['my_account'] = _('My Account');
$menu_items['backup_restore'] = _('Backup/Restore');

# menu structure using array keys from translated labels above
$menu = array(
	'section_about' => array('portal', 'manual', 'support', 'ninja_project', 'merlin_project', 'project_documentation'),
	'section_monitoring' => array('tac', 'host_detail', 'service_detail',
		'hostgroup_summary', 'hostgroup_overview', 'hostgroup_grid',
		'servicegroup_summary', 'servicegroup_overview', 'servicegroup_grid',
		'network_outages', 'host_problems', 'service_problems', 'unhandled_problems',
		'comments', 'schedule_downtime', 'process_info', 'scheduling_queue', 'hyper_map'), //'nagvis'), /* remove hardcoded nagvis menu entry */
	'section_reporting' => array('trends', 'pnp', 'alert_history', 'alert_summary', 'notifications', 'event_log',
		'availability', 'sla', 'schedule_reports', 'statistics'),
	'section_configuration' => array('view_config', 'my_account', 'backup_restore', 'configure')
);

$group_items_per_page = config::get('pagination.group_items_per_page', '*', true);
$all_host_status_types = nagstat::HOST_PENDING|nagstat::HOST_UP|nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE;

// Preparing the reporting section on beforehand since it might or might not include the pnp link
$section_reporting = array(
	$menu_items['trends'] => array('/trends', 'trends',0));
if(Kohana::config('config.pnp4nagios_path') !== false) {
	$section_reporting[$menu_items['pnp']] = array('pnp/?host=.pnp-internal&srv=runtime', 'pnp',0);
}
$section_reporting[$menu_items['alert_history']] = array('/showlog/alert_history', 'alerthistory',0);
$section_reporting[$menu_items['alert_summary']]= array('/summary', 'alertsummary',0);
$section_reporting[$menu_items['notifications']]  = array('/notifications', 'notifications',0);
$section_reporting[$menu_items['event_log']] = array('/showlog/showlog', 'eventlog',0);
$section_reporting[$menu_items['availability']] = array('/'.Kohana::config('reports.reports_link').'/?type=avail', 'availability',0);
$section_reporting[$menu_items['sla']] = array('/'.Kohana::config('reports.reports_link').'/?type=sla', 'sla',0);
$section_reporting[$menu_items['schedule_reports']]= array('/'.Kohana::config('reports.reports_link').'/?show_schedules', 'schedulereports',0);

# base menu (all)
$menu_base = array(
	$menu_items['section_about'] => array(
		$menu_items['portal'] 					=> array('http://'.$_SERVER['HTTP_HOST'], 'portal',2),
		$menu_items['manual'] 					=> array('http://'.$_SERVER['HTTP_HOST'].'/monitor/op5/manual/index.html', 'manual',2),
		$menu_items['support'] 					=> array('http://www.op5.com/support', 'support',2),
		$menu_items['ninja_project'] 			=> array('http://www.op5.org/community/plugin-inventory/op5-projects/ninja', 'ninja',3),
		$menu_items['merlin_project'] 			=> array('http://www.op5.org/community/plugin-inventory/op5-projects/merlin', 'merlin',3),
		$menu_items['project_documentation'] 	=> array('https://wiki.op5.org', 'eventlog',3),
	),
	$menu_items['section_monitoring'] => array(
		$menu_items['tac'] 						=> array('/tac', 'tac',0),
		$menu_items['host_detail'] 				=> array('/status/host/all', 'host',0),
		$menu_items['service_detail'] 			=> array('/status/service/all', 'service',0),
		//'hr1' 														=> array('', ''),
		$menu_items['hostgroup_summary']		=> array('/status/hostgroup_summary?items_per_page='.$group_items_per_page, 'hostgroupsummary',0),
		$menu_items['hostgroup_overview'] 		=> array('/status/hostgroup?items_per_page='.$group_items_per_page, 'hostgroup',0),
		$menu_items['hostgroup_grid']			=> array('/status/hostgroup_grid?items_per_page='.$group_items_per_page, 'hostgroupgrid',0),
		//'hr2'														=> array('', ''),
		$menu_items['servicegroup_summary'] 	=> array('/status/servicegroup_summary?items_per_page='.$group_items_per_page, 'servicegroupsummary',0),
		$menu_items['servicegroup_overview'] 	=> array('/status/servicegroup?items_per_page='.$group_items_per_page, 'servicegroup',0),
		$menu_items['servicegroup_grid'] 		=> array('/status/servicegroup_grid?items_per_page='.$group_items_per_page, 'servicegroupgrid',0),
		//'hr3' 														=> array('', ''),
		$menu_items['network_outages']  		=> array('/outages', 'outages',0),
		$menu_items['host_problems'] 			=> array('/status/host/all/'.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE), 'hostproblems',0),
		$menu_items['service_problems'] 		=> array('/status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN), 'serviceproblems',0),
		$menu_items['unhandled_problems']  		=> array('/status/service/all?servicestatustypes='.(nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL|nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_PENDING).'&hostprops='.(nagstat::HOST_NO_SCHEDULED_DOWNTIME|nagstat::HOST_STATE_UNACKNOWLEDGED).'&service_props='.(nagstat::SERVICE_NO_SCHEDULED_DOWNTIME|nagstat::SERVICE_STATE_UNACKNOWLEDGED).'&hoststatustypes='.$all_host_status_types, 'problems',0),
		//'hr5' 														=> array('', ''),
		$menu_items['comments'] 				=> array('/extinfo/show_comments', 'comments',0),
		$menu_items['schedule_downtime']		=> array('/extinfo/scheduled_downtime', 'scheduledowntime',0),

		$menu_items['process_info'] 			=> array('/extinfo/show_process_info', 'processinfo',0),
		$menu_items['performance_info'] 		=> array('/extinfo/performance', 'performanceinfo',0),
		$menu_items['scheduling_queue'] 		=> array('/extinfo/scheduling_queue', 'schedulingqueue',0)
	),
	$menu_items['section_reporting'] => $section_reporting,
	$menu_items['section_configuration'] => array(
		$menu_items['view_config'] 				=> array('/config', 'viewconfig',0),
		$menu_items['my_account'] 				=> array('/user', 'password',0),
		$menu_items['backup_restore']			=> array('/backup', 'backup',0)
	)
);


if (isset($menu_items['statistics']))
	$menu_base[$menu_items['section_reporting']][$menu_items['statistics']] = array('/statistics', 'statistics',1);

# Add NACOMA link only if enabled in config
if (isset($menu_items['configure']))
	$menu_base[$menu_items['section_configuration']][$menu_items['configure']] = array('/configuration/configure','nacoma',0);


if (isset($menu_items['hyper_map']))
	$menu_base[$menu_items['section_monitoring']][$menu_items['hyper_map']] = array('/hypermap', 'hypermap',0);
unset($auth);

/* remove hardcoded nagvis menu entry
if (isset($menu_items['nagvis']))
	$menu_base[$menu_items['section_monitoring']][$menu_items['nagvis']] = array('/nagvis/index', 'nagvis',0);
*/

if (Kohana::config('config.site_domain') != '/monitor/') {
	# remove op5 monitor specific links
	unset($menu_base[$menu_items['section_about']][$menu_items['portal']]);
	unset($menu_items['portal']);
	unset($menu['section_about']['portal']);

	unset($menu_base[$menu_items['section_about']][$menu_items['manual']]);
	unset($menu_items['manual']);
	unset($menu['section_about']['manual']);

	unset($menu_base[$menu_items['section_about']][$menu_items['support']]);
	unset($menu_items['support']);
	unset($menu['section_about']['support']);
} else {
	# remove community links
	unset($menu_base[$menu_items['section_about']][$menu_items['project_documentation']]);
	unset($menu_items['project_documentation']);
	unset($menu['section_about']['project_documentation']);

	unset($menu_base[$menu_items['ninja_project']]);
	unset($menu_items['ninja_project']);
	unset($menu['section_about']['ninja_project']);

	unset($menu_base[$menu_items['merlin_project']]);
	unset($menu_items['merlin_project']);
	unset($menu['section_about']['merlin_project']);
}

# master menu section
$sections = array(
	'about',
	'monitoring',
	'reporting',
	'configuration'
);

$xtra_menu = Kohana::config('menu.items');
if (!empty($xtra_menu)) {
	foreach ($xtra_menu as $section => $page_info) {
		foreach ($page_info as $page => $info) {
			$menu_base[$section][$page] = $info;
			#$menu['section_reporting'][] = $page;
			$menu_items[$page] = $page;
		}
		unset($xtra_menu[$section]);
	}
}
