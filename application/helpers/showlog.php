<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * showlog helper
 */
class showlog
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

		if (!op5mayi::instance()->run('monitoring.status:view.showlog')) {
			$cmd .= ' --hide-process --hide-commands ';
		}

		$cmd .= ' --restrict-objects';

		$cmd .= " --html";

		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'), // really, php, no "I don't want to see it, just send it to the browser"?
			2 => array('pipe', 'a'),
		);

		$process = proc_open($cmd, $descriptorspec, $pipes);
		if (!is_resource($process)) {
			echo "Couldn't run showlog binary";
			return;
		}
		$pool = new HostPool_Model();
		$set = $pool->all();
		if(!op5mayi::instance()->run($set->mayi_resource().":view.showlog", false, $messages)) {
			echo "<p>Not enough rights for viewing showlog for hosts</p>\n";
			if($messages) {
				echo implode("<br />", $messages);
			}
			return;
		}
		$hosts = array();
		foreach ($set->it(array('name')) as $obj) {
			$hosts[$obj->get_name()] = $obj->get_name();
		}
		fwrite($pipes[0], implode(';', $hosts));
		fwrite($pipes[0], "\n");
		$pool = new ServicePool_Model();
		$set = $pool->all();
		if(!op5mayi::instance()->run($set->mayi_resource().":view.showlog", false, $messages)) {
			echo "<p>Not enough rights for viewing showlog for services</p>\n";
			if($messages) {
				echo implode("<br />", $messages);
			}
			return;
		}
		$svc = array();
		foreach ($set->it(array('host_name', 'description')) as $obj) {
			$hname = $obj->get_host()->get_name();
			if (!array_key_exists($hname, $hosts)) {
				$svc[] = $hname;
				$svc[] = $obj->get_description();
			}
		}
		fwrite($pipes[0], implode(';', $svc));

		fclose($pipes[0]);
		while (!feof($pipes[1])) {
			echo fgets($pipes[1], 1024);
		}
		fclose($pipes[1]);
		proc_close($process);
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
