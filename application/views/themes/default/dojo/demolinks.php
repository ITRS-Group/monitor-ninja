<?php

	$menu = array (

		"about" => array (
			array('the ninja project', 'ninja', '#ninja'),
			array('the merlin project', 'merlin', '#ninja'),
			array('project documentation', 'manual', '#manual')
		),

		"monitoring" => array (

			array('tactical overview', 'tac', '#tac'),

			'separator',

			array('host detail', 'host', '#hostdetail'),
			array('host problems', 'hostproblems', '#hostproblems'),
			array('hostgroup summary', 'hostgroupsummary', '#hostgroupsummary'),
			array('hostgroup', 'hostgroup', '#hostgroup'),
			array('hostgroup grid', 'hostgroupgrid', '#hostgroupgrid'),

			'separator',

			array('service detail', 'service', '#service'),
			array('service problems', 'serviceproblems', '#service'),
			array('servicegroup summary', 'servicegroupsummary', '#service'),
			array('servicegroup overview', 'servicegroup', '#service'),
			array('servicegroup grid', 'servicegroupgrid', '#service'),

			'separator',

			array('network outages', 'outages', '#networkoutages'),
			array('unhandled problems', 'problems', '#unhandledproblems'),
			array('comments', 'comments', '#comments'),
			array('schedule downtime', 'scheduledowntime', '#scheduledowntime'),
			array('process info', 'processinfo', '#procinfo'),
			array('performance info', 'performanceinfo', '#perfinfo'),
			array('scheduling queue', 'schedulingqueue', '#schedulingqueue')

		),

		"reporting" => array (
			array('alert history', 'alerthistory', '#alerthistory'),
			array('alert summary', 'alertsummary', '#alertsummary'),
			array('notifications', 'notifications', '#notifications'),
			array('event log', 'eventlog', '#eventlog'),

			'separator',

			array('trends', 'trends', '#trends'),
			array('graph', 'pnp', '#graph'),
			array('availability', 'availability', '#availability'),
			array('SLA Reporting', 'sla', '#sla'),
			array('schedule reports', 'schedulereports', '#schedulereports')
		),

		"configuration" => array (
			array('view config', 'viewconfig', '#viewconfig'),
			array('my account', 'password', '#myaccount'),
			array('backup / restore', 'backup', '#backuprestore'),
			array('configure', 'nacoma', '#configure')
		)


	);
