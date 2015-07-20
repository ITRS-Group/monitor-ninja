<?php

$manifest = array_merge_recursive($manifest, array(
	'monitor' => array(
		'monitoring' => array(
			'hosts' => array(
				':update' => array(
					'commands' => array(
						'acknowledge_problem' => array(),
						'add_comment' => array(),
						'disable_check' => array(),
						'disable_service_checks' => array(),
						'disable_service_notifications' => array(),
						'enable_check' => array(),
						'enable_service_checks' => array(),
						'enable_service_notifications' => array(),
						'process_check_result' => array(),
						'remove_acknowledgement' => array(),
						'schedule_check' => array(),
						'schedule_downtime' => array(),
						'schedule_service_checks' => array(),
						'send_custom_notification' => array()
					)
				)
			),
			'services' => array(
				':update' => array(
					'commands' => array(
						'add_comment' => array(),
						'disable_check' => array(),
						'process_check_result' => array(),
						'schedule_check' => array(),
						'schedule_downtime' => array(),
						'send_custom_notification' => array()
					)
				)
			),
			'hostgroup' => array(
				':update' => array(
					'commands' => array(
						'disable_service_checks' => array(),
						'disable_service_notifications' => array(),
						'enable_service_checks' => array(),
						'enable_service_notifications' => array(),
						'schedule_host_downtime' => array(),
						'schedule_service_downtime' => array()
					)
				)
			),
			'servicegroup' => array(
				':update' => array(
					'commands' => array(
						'disable_service_checks' => array(),
						'disable_service_notifications' => array(),
						'enable_service_checks' => array(),
						'enable_service_notifications' => array(),
						'schedule_host_downtime' => array(),
						'schedule_service_downtime' => array()
					)
				)
			)
		)
	)
));
