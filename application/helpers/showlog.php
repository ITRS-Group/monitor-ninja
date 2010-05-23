<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * showlog helper
 */
class showlog_Core
{
	public function show_log_entries($options)
	{
		# default limit
		$limit = 2500;
		$cgi_cfg = false;
		$nagios_path = Kohana::config('config.nagios_base_path');
		$etc_path = Kohana::config('config.nagios_etc_path');
		if (!$etc_path)
			$etc_path = $nagios_path . '/etc';
		$cgi_cfg = $etc_path . '/cgi.cfg';

		$showlog = self::get_path();
		$cmd = $showlog . " --cgi-cfg=" . $cgi_cfg;

		foreach ($options as $k => $v) {
			# support all the various 'hide' options
			if (substr($k, 0, 4) === 'hide') {
				$cmd .= ' --' . str_replace('_', '-', $k);
				continue;
			}
			switch ($k) {
			 case 'state_type':
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
				if (!empty($v)) {
					$cmd .= " --$k=$v";
					$limit = false;
				}
				break;
			 case 'time_format':
				$cmd .= ' --' . str_replace('_', '-', $k) . '=' . $v;
				break;
			 case 'host':
				$cmd .= " --host='" . join("' --host='", $v) . "'";
				break;
			 case 'service':
				$cmd .= " --service='" . join("' --service='", $v) . "'";
				break;
			 case 'user':
				$cmd .= " --user='$v'";
				break;
			 default:
				break;
			}
		}
		if (!empty($options['host_state_options'])) {
			$cmd .= ' --host-states=' . join(array_keys($options['host_state_options']));
		} else {
			$cmd .= ' --host-states=n';
		}
		if (!empty($options['service_state_options'])) {
			$cmd .= ' --service-states=' . join(array_keys($options['service_state_options']));
		} else {
			$cmd .= ' --service-states=n';
		}

		if (empty($options['parse_forward'])) {
			$cmd .= ' --reverse';
		}
		# invoke a hard limit in case the user didn't set any.
		# This will prevent php from exiting with an out-of-memory
		# error, and will also stop users' browsers from hanging
		# when trying to load a gargantually large page
		if (empty($options['limit']) && $limit !== false) {
			$cmd .= ' --limit=' . $limit;
		}

		# Add the proper image url for this theme. Screw the user if he/she
		# uses a non-standard theme which lacks the images we need
		$cmd .= " --image-url=" . url::base(false) .
			'/application/views/' .
			zend::instance('Registry')->get('theme_path') .
			'/icons/16x16/';

		$cmd .= " --html";

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
