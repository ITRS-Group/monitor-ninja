<?php defined('SYSPATH') OR die('No direct access allowed.');
$csv_content = array('"'.implode('", "', array(
	 $this->options->get_value('summary_type'),
	'From: '.$options->get_date('start_time'),
	'To: '.$options->get_date('end_time'),
	'Duration: '.($options['end_time'] - $options['start_time'])
)).'"');

if(Summary_Controller::RECENT_ALERTS == $options['summary_type']) {
	// headers
	$csv_content[] = '"'.implode('", "', array(
		'TIME',
		'ALERT TYPE',
		'HOST',
		'SERVICE',
		'STATE TYPE',
		'INFORMATION'
	)).'"';

	// content
	foreach($result as $log_entry) {
		$csv_content[] = '"'.implode('", "', array(
			date($date_format, $log_entry['timestamp']),
			Reports_Model::event_type_to_string($log_entry['event_type']),
			isset($log_entry['host_name'])?$log_entry['host_name']:'',
			isset($log_entry['service_description'])? $log_entry['service_description'] : 'N/A',
			$log_entry['hard'] ? _('Hard') : _('Soft'),
			$log_entry['output']
		)).'"';
	}
} elseif(Summary_Controller::TOP_ALERT_PRODUCERS == $options['summary_type']) {
	// summary of services
	// headers
	$csv_content[] = '"'.implode('", "', array(
		'HOST',
		'SERVICE',
		'ALERT TYPE',
		'TOTAL ALERTS'
	)).'"';

	// content
	foreach($result as $log_entry) {
		$csv_content[] = '"'.implode('", "', array(
			$log_entry['host_name'],
			isset($log_entry['service_description']) ? $log_entry['service_description'] : null,
			Reports_Model::event_type_to_string($log_entry['event_type'], 'service'),
			$log_entry['total_alerts']
		)).'"';
	}
} else {
	// custom settings, even more alert types to choose from;
	// also explains the nested layout of $result
	$header = array(
		'TYPE',
		'HOST',
		'STATE',
		'SOFT ALERTS',
		'HARD ALERTS',
		'TOTAL ALERTS'
	);
	switch($options['report_type']) {
		case 'hostgroups':
			$label = _('Hostgroup');
			array_splice($header, 1, 1, 'HOSTGROUP');
			break;
		case 'hosts':
			$label = _('Host');
			break;
		case 'services':
			$label = _('Service');
			array_splice($header, 2, 0, 'SERVICE');
			break;
		case 'servicegroups':
			$label = _('Servicegroup');
			array_splice($header, 1, 1, 'SERVICEGROUP');
			break;
	}
	$csv_content[] = '"'.implode('", "', $header).'"';
	foreach ($result as $host_name => $ary) {
		$service_name = null;
		if($options['report_type'] == 'services') {
			list($host_name, $service_name) = explode(';', $host_name);
		}
		foreach($ary['host'] as $state => $host) {
			$row = array(
				$label,
				$host_name,
				$host_state_names[$state],
				$host[0], # soft
				$host[1], # hard
				$host[0] + $host[1] # total
			);
			if($service_name) {
				array_splice($row, 2, 0, $service_name);
			}
		}
		$csv_content[] = '"'.implode('", "', $row).'"';
		foreach($ary['service'] as $state => $service) {
			$row = array(
				$label,
				$host_name,
				$service_state_names[$state],
				$service[0], # soft
				$service[1], # hard
				$service[0] + $service[1] # total
			);
			if($service_name) {
				array_splice($row, 2, 0, $service_name);
			}
		}
		$csv_content[] = '"'.implode('", "', $row).'"';
	}
}

echo implode($csv_content, "\n")."\n";
