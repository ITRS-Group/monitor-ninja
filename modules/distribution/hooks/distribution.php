<?php

require_once('op5/mayi.php');
require_once('op5/sysinfo.php');

/**
 * Class containing hooks to interface with the monitoring subsystem
 *
 **/
class distribution_hooks implements op5MayI_Actor
{
	private $info;

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct()
	{
		$mayi = op5MayI::instance();
		$mayi->be('monitor.distribution', $this);
		$this->info = $this->process_info();
	}

	/**
	 * Returns information about the system usage
	 *
	 * @return array
	 **/
	public function getActorInfo()
	{
		return $this->info;
	}

	/**
	 * Gathers and processes info about the system usage
	 *
	 * @return array
	 **/
	function process_info()
	{
		$sysinfo = new op5sysinfo();
		$nodeinfo = $sysinfo->get_merlininfo();

		$configured_peers = 0;
		$configured_pollers = 0;
		$pgroups = array();

		if (!isset($nodeinfo['ipc']) || $nodeinfo['ipc']['configured_masters'] > 0) {
			// We're on a poller just return 0 on everything
			return array(
				'poller_groups' => 0,
				'pollers' => 0,
				'peers' => 0
			);
		}

		// Loop nodeinfo and gather needed info
		foreach($nodeinfo as $node) {
			if(isset($node['pgroup_id']) && isset($node['type']) && $node['type'] === 'poller') {
				$pgroups[(int)$node['pgroup_id']] = true;
			}
		}

		return array(
			'poller_groups' => count($pgroups),
			'pollers' => isset($nodeinfo['ipc']['configured_pollers']) ? (int) $nodeinfo['ipc']['configured_pollers'] : 0,
			// Always count the ipc as a peer
			'peers' => isset($nodeinfo['ipc']['configured_peers']) ? (int) $nodeinfo['ipc']['configured_peers'] + 1 : 0
		);
	}
} // END class distribution_hooks implements op5MayI_Actor

new distribution_hooks();
