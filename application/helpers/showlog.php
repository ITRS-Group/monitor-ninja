<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * showlog helper
 */
class showlog_Core
{
	public function show_log_entries($options)
	{
		$showlog = '/opt/monitor/op5/reports/module/showlog';
		if (!file_exists($showlog) || !is_executable($showlog)) {
			echo "Showlog program '$showlog' not installed or not executable.<br />\n";
		}
		$cmd = $showlog . " --html /opt/monitor/var/nagios.log ";
#			"/opt/monitor/var/archives/nagios-*.log";

		if (!isset($options['parse_forward'])) {
			$cmd .= ' --reverse';
		}

		foreach ($options as $k => $v) {
			switch ($k) {
			 case 'hide_flapping': case 'hide_process': case 'hide_downtime':
				$cmd .= ' --' . str_replace('_', '-', $k);
				break;
			 case 'state_types':
			 	if (isset($v['hard']) && isset($v['soft'])) {
					break;
				}
				if (isset($v['hard'])) {
					$cmd .= ' --state-type=hard';
				} elseif (isset($v['soft'])) {
					$cmd .= ' -- state-type=soft';
				}
				break;
			 case 'first': case 'last':
				if (!empty($v))
					$cmd .= " --$k=$v";
				break;
			 case 'time_format':
				$cmd .= ' --' . str_replace('_', '-', $k) . '=' . $v;
				break;
			 case 'host':
				$cmd .= " --host='" . join("' --host='", $v) . "'";
				break;
			 default:
				break;
			}
		}
		if (!empty($options['host_state_options'])) {
			$cmd .= ' --host-states=' . join(array_keys($options['host_state_options']));
		}
		if (!empty($options['service_state_options'])) {
			$cmd .= ' --service-states=' . join(array_keys($options['service_state_options']));
		}

#		echo "cmd = $cmd\n";
		passthru($cmd);
	}

	/**
	*	Get path to showlog executable
	*/
	public function get_path()
	{
		$showlog = Kohana::config('reports.showlog_path');
		if (!file_exists($showlog) || !is_executable($showlog)) {
			echo "Showlog program '$showlog' not installed or not executable.<br />\n";
			return false;
		}
		return $showlog;

	}
}
