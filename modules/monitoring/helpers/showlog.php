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
		$pool = new HostPool_Model();
		$host_set = $pool->all();
		if(!op5mayi::instance()->run($host_set->mayi_resource().":read.showlog", array(), $messages)) {
			echo "<p>Not enough rights for viewing showlog for hosts</p>\n";
			if($messages) {
				echo implode("<br />", $messages);
			}
			return;
		}

		$pool = new ServicePool_Model();
		$service_set = $pool->all();
		if(!op5mayi::instance()->run($service_set->mayi_resource().":read.showlog", array(), $messages)) {
			echo "<p>Not enough rights for viewing showlog for services</p>\n";
			if($messages) {
				echo implode("<br />", $messages);
			}
			return;
		}

		$limit = 2500;
		$cgi_cfg = false;
		$etc_path = System_Model::get_nagios_etc_path();
		$cgi_cfg = rtrim($etc_path, '/').'/cgi.cfg';

		$args = array(
			self::get_path(),
			"--cgi-cfg=$cgi_cfg"
		);

		foreach ($options as $k => $v) {
			# support all the various 'hide' options
			if (substr($k, 0, 4) === 'hide') {
				$args[] = '--' . str_replace('_', '-', $k);
				continue;
			}
			switch ($k) {
			 case 'state_type':
				if (isset($v['hard']) && isset($v['soft'])) {
					break;
				}
				if (isset($v['hard'])) {
					$args[] = '--state-type=hard';
				} elseif (isset($v['soft'])) {
					$args[] = '--state-type=soft';
				}
				break;
			 case 'first': case 'last':
				if (!empty($v)) {
					$args[] = "--$k=$v";
					$limit = false;
				}
				break;
			 case 'time_format':
				$args[] = '--' . str_replace('_', '-', $k) . '=' . $v;
				break;
			 case 'host':
				foreach($v as $h) {
					$args[] = "--host=$h";
				}
				break;
			 case 'service':
				foreach($v as $s) {
					$args []= "--service=$s";
				}
				break;
			 default:
				break;
			}
		}
		if (!empty($options['host_state_options'])) {
			$args[] = '--host-states='.implode(array_keys($options['host_state_options']));
		}
		if (!empty($options['service_state_options'])) {
			$args[] = '--service-states='.implode(array_keys($options['service_state_options']));
		}

		if (empty($options['parse_forward'])) {
			$args[] = '--reverse';
		}
		# invoke a hard limit in case the user didn't set any.
		# This will prevent php from exiting with an out-of-memory
		# error, and will also stop users' browsers from hanging
		# when trying to load a gargantually large page
		if (empty($options['limit']) && $limit !== false) {
			$args[] = '--limit='.$limit;
		}

		$args[] = "--image-url=" . url::base(false) .
			'application/views/icons/x16/';

		$resource = ObjectPool_Model::pool('status')->all()->mayi_resource();
		if (!op5mayi::instance()->run($resource.':read.showlog')) {
			$args[] = '--hide-process';
			$args[] = '--hide-commands';
		}

		$args[] = '--restrict-objects';

		$args[] = "--html";

		$descriptorspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'), // really, php, no "I don't want to see it, just send it to the browser"?
			2 => array('pipe', 'a'),
		);

		$escaped_cmd = implode(' ', array_map('escapeshellarg', $args));
		$process = proc_open($escaped_cmd, $descriptorspec, $pipes);
		if (!is_resource($process)) {
			echo "Couldn't run showlog binary";
			return;
		}
		$hosts = array();
		foreach ($host_set->it(array('name')) as $obj) {
			$hosts[$obj->get_name()] = $obj->get_name();
		}
		fwrite($pipes[0], implode(';', $hosts));
		fwrite($pipes[0], "\n");

		$svc = array();
		foreach ($service_set->it(array('host_name', 'description')) as $obj) {
			$hname = $obj->get_host()->get_name();
			if (!array_key_exists($hname, $hosts)) {
				$svc[] = $hname;
				$svc[] = $obj->get_description();
			}
		}
		fwrite($pipes[0], implode(';', $svc));

		fclose($pipes[0]);
		$empty_result = true;
		while (!feof($pipes[1])) {
			$line = fgets($pipes[1], 1024);
			if($empty_result && strlen($line) > 0) {
				// This might be a very large loop,
				// check the simple boolean value
				// instead of executing a function
				// for each run
				$empty_result = false;
			}
			echo $line;

		}
		fclose($pipes[1]);
		proc_close($process);
		if($empty_result) {
			echo "We found no log messages for your selected options.";
		}
	}

	/**
 	 * Get path to showlog executable
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
