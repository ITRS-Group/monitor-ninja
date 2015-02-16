<?php
require_once __DIR__.'/objstore.php';
require_once __DIR__.'/livestatus.php';
require_once __DIR__.'/queryhandler.php';

class op5sysinfo_Exception extends Exception {}

class op5sysinfo {
	/**
	 * Return sysinfo instance
	 *
	 * @return op5sysinfo
	 */
	static public function instance() {
		return op5objstore::instance()->obj_instance(__CLASS__);
	}

	/**
	 * List of names of all available metrics.
	 * A get_xxx_usage-method is needed per metric
	 *
	 * @var unknown
	 */
	private static $metric_names = array ('monitor','monitor.service','logserver','pollers',
		'peers','aps','trapper');

	/**
	 * Cache the result from "merlin node info", so we don't need to ask more
	 * than once every isntance
	 *
	 * @var array
	 */
	private $merlin_nodeinfo = false;

	/**
	 * Get a list of all metrics in the system.
	 *
	 * @return array
	 */
	public function get_usage(array $request = null) {
		if ($request === null) {
			$request = self::$metric_names;
		}

		$metrics = array ();
		foreach ($request as $metric) {
			$getter = "get_" . str_replace('.','_',$metric) . "_usage";
			try {
				if (method_exists($this, $getter)) {
					$metrics[$metric] = $this->$getter();
				}
			} catch (Exception $e) {
				/* Something went wrong... skip this metric */
			}
		}
		return $metrics;
	}

	/**
	 * Get number of hosts used by Monitor
	 *
	 * @throws op5LivestatusException
	 * @return int
	 */
	public function get_monitor_usage() {
		$ls = op5livestatus::instance();
		/* Query livestatus for number of hosts loaded in system */
		list ($columns, $objects, $count) = $ls->query('hosts', 'Limit: 0',
			array ('name'), array ('auth' => false));
		return $count;
	}

	/**
	 * Get number of hosts used by Monitor
	 *
	 * @throws op5LivestatusException
	 * @return int
	 */
	public function get_monitor_service_usage() {
		$ls = op5livestatus::instance();
		/* Query livestatus for number of hosts loaded in system */
		list ($columns, $objects, $count) = $ls->query('services', 'Limit: 0',
			array ('description'), array ('auth' => false));
		return $count;
	}

	/**
	 * Get number of hosts used by LogServer
	 *
	 * @throws op5sysinfo_Exception
	 * @return int
	 */
	public function get_logserver_usage() {
		$logserver_cache_file = '/tmp/logserver-license.dat';
		if (!is_readable($logserver_cache_file)) {
			/* Amount is unknown, don't report it */
			throw new op5sysinfo_Exception('Could not read logserver license-cache file');
		}
		$in = @file_get_contents($logserver_cache_file);
		$in = trim($in);
		if (!$in) {
			/* Otherwise, empty file means one empty element in the array */
			return 0;
		}
		$ary = explode(':', $in);
		$num = count($ary);
		return $num;
	}

	/**
	 * Get number of pollers configured in the system
	 *
	 * @throws op5queryhandler_Exception
	 * @return int
	 */
	public function get_pollers_usage() {
		$nodeinfo = $this->get_merlininfo();
		if (!isset($nodeinfo['ipc']) ||
			 !isset($nodeinfo['ipc']['configured_polllers']))
			throw new op5sysinfo_Exception('No poller information found');
		return intval($nodeinfo['ipc']['configured_pollers']);
	}

	/**
	 * Get number of peers configured in the system
	 *
	 * @throws op5queryhandler_Exception
	 * @return int
	 */
	public function get_peers_usage() {
		$nodeinfo = $this->get_merlininfo();
		if (!isset($nodeinfo['ipc']) ||
			 !isset($nodeinfo['ipc']['configured_peers']))
			throw new op5sysinfo_Exception('No peer information found');
		return intval($nodeinfo['ipc']['configured_peers']);
	}

	/**
	 * Fetch if system is an APS
	 *
	 * @return int, 1 for aps, 0 if not
	 */
	public function get_aps_usage() {
		exec('rpm -q op5-system-release', $output, $exit_code);
		return $exit_code === 0 ? 1 : 0;
	}

	/**
	 * Fetch if trapper is installed
	 *
	 * @return int, 1 for installed, 0 if not
	 */
	public function get_trapper_usage() {
		exec('rpm -q op5-trapper-processor', $output, $exit_code);
		if ($exit_code !== 0) {
			// rpm returned non-zero, which probably means that Trapper is not installed.
			return 0;
		}
		exec('/usr/bin/traped list handlers | grep -v demo-handler | wc -l', $output2);
		if (is_numeric($output2[0]) && $output2[0] > 0){
			// Trapper has at least one handler in addition to the default demo-handler. This means it is used.
			return 1;
		}
		// We should never get here, but if it happens we'll say that Trapper is not used.
		return 0;
	}

	/**
	 * Fetch information from "merlin node info".
	 * Used by get_pollers and get_peers
	 *
	 * @return array
	 */
	public function get_merlininfo() {
		if ($this->merlin_nodeinfo !== false) {
			return $this->merlin_nodeinfo;
		}
		$qh = op5queryhandler::instance();
		try {
			$nodeinfo_all = $qh->raw_call("#merlin nodeinfo\0");
			$instances_kvvec = explode("\n", $nodeinfo_all);
			$nodeinfo = array ();
			foreach ($instances_kvvec as $kvvec) {
				if (!$kvvec)
					continue;
				$instance = array ();
				$parts = explode(';', $kvvec);
				foreach ($parts as $kv) {
					$kvarr = explode('=', $kv, 2);
					if (count($kvarr) == 2) {
						$instance[$kvarr[0]] = $kvarr[1];
					}
				}
				if (isset($instance['name']))
					$nodeinfo[$instance['name']] = $instance;
			}
			$this->merlin_nodeinfo = $nodeinfo;
		} catch (op5queryhandler_Exception $ex) {
			$nodeinfo = array();
		}
		return $nodeinfo;
	}
}
