<?php

$manifest = array_merge_recursive($manifest, array(
	'monitor' => array(
		'monitoring' => array(
			'hosts' => array(
				'commands' => array(
					'acknowledge_problem' => array(
						':create' => array()
					),
					'add_comment' => array(
						':create' => array()
					),
					'disable_check' => array(
						':create' => array()
					),
					'disable_service_checks' => array(
						':create' => array()
					),
					'disable_service_notifications' => array(
						':create' => array()
					),
					'enable_check' => array(
						':create' => array()
					),
					'enable_service_checks' => array(
						':create' => array()
					),
					'enable_service_notifications' => array(
						':create' => array()
					),
					'process_check_result' => array(
						':create' => array()
					),
					'remove_acknowledgement' => array(
						':create' => array()
					),
					'schedule_check' => array(
						':create' => array()
					),
					'schedule_downtime' => array(
						':create' => array()
					),
					'schedule_service_checks' => array(
						':create' => array()
					),
					'send_custom_notification' => array(
						':create' => array()
					),
				),
			),
			'services' => array(
				'commands' => array(
					'add_comment' => array(
						':create' => array()
					),
					'disable_check' => array(
						':create' => array()
					),
					'process_check_result' => array(
						':create' => array()
					),
					'schedule_check' => array(
						':create' => array()
					),
					'schedule_downtime' => array(
						':create' => array()
					),
					'send_custom_notification' => array(
						':create' => array()
					),
				),
			),
			'hostgroup' => array(
				'commands' => array(
					'disable_service_checks' => array(
						':create' => array()
					),
					'disable_service_notifications' => array(
						':create' => array()
					),
					'enable_service_checks' => array(
						':create' => array()
					),
					'enable_service_notifications' => array(
						':create' => array()
					),
					'schedule_host_downtime' => array(
						':create' => array()
					),
					'schedule_service_downtime' => array(
						':create' => array()
					),
				),
			),
			'servicegroup' => array(
				'commands' => array(
					'disable_service_checks' => array(
						':create' => array()
					),
					'disable_service_notifications' => array(
						':create' => array()
					),
					'enable_service_checks' => array(
						':create' => array()
					),
					'enable_service_notifications' => array(
						':create' => array()
					),
					'schedule_host_downtime' => array(
						':create' => array()
					),
					'schedule_service_downtime' => array(
						':create' => array()
					),
				),
			),
		)
	)
));
