<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * showlog helper
 */
class showlog_Core
{
	/**
	 * Generate HTML-formatted representation of the on-disk nagios log
	 *
	 * @param $options A magical array of options to use - check source for more info
	 * @return HTML-representation of the log
	 */
	public static function show_log_entries($options)
	{
		# default limit
		$limit = 2500;
		$cgi_cfg = false;
		$etc_path = System_Model::get_nagios_etc_path();
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
					$cmd .= ' --state-type=soft';
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

		# Add the proper image url.
		$cmd .= " --image-url=" . url::base(false) .
			'application/views/icons/16x16/';

		if (!Auth::instance()->authorized_for('system_information')) {
			$cmd .= ' --hide-process --hide-commands ';
		}

		$cmd .= " --html";

		passthru($cmd, $exit_code);
		if($exit_code) {
			echo "<p>Could not use showlog binary, got '$exit_code' as exit code.</p>";
		}
	}

	/**
	*	Get path to showlog executable
	*/
	public static function get_path()
	{
		$showlog = Kohana::config('reports.showlog_path');
		if (!file_exists($showlog) || !is_executable($showlog)) {
			echo "Showlog program '$showlog' not installed or not executable.<br />\n";
			return false;
		}
		return $showlog;

	}
}
