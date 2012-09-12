<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieves and manipulates current status of hosts (and services?)
 */
class Current_status_Model extends Model
{
	private static $instance = false;

	const HOST_UP =  0; /**< Nagios' host up code */
	const HOST_DOWN = 1; /**< Nagios' host down code */
	const HOST_UNREACHABLE = 2; /**< Nagios' host unreachable code */
	const HOST_PENDING = 6; /**< Our magical "host pending" code for unchecked hosts */

	const SERVICE_OK = 0; /**< Nagios' service ok code */
	const SERVICE_WARNING = 1; /**< Nagios' service warning code */
	const SERVICE_CRITICAL = 2; /**< Nagios' service critical code */
	const SERVICE_UNKNOWN =  3; /**< Nagios' service unknown code */
	const SERVICE_PENDING = 6; /**< Our magical "service pending" code for unchecked services */
	const HOST_CHECK_ACTIVE = 0;	/**< Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;	/**< the host check result was submitted by an external source */
	const SERVICE_CHECK_ACTIVE = 0; /**< Nagios performed the service check */
	const SERVICE_CHECK_PASSIVE = 1; /**< the service check result was submitted by an external source */

	private $program_data_present = false;
	private $host_data_present = false;
	private $service_data_present = false;
	private $outage_data_present = false;

	/**
	 * Use this class as a singleton, as it is quite slow
	 *
	 * @return A Current_status_Model object
	 */
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new Current_status_Model();
		}
		return self::$instance;
	}

	/**
	 * Fetch current host status from db for current user
	 * @return bool indicating whether query worked
	 */
	public function program_status()
	{
		if ($this->program_data_present)
			return;

		$stats = new Program_status_Model();
		$this->ps = $stats->get_local();
		$this->program_data_present = true;
		return;
	}

	/**
	 * Fetch current host status from db for current user
	 * @return bool indicating whether query worked
	 */
	public function host_status()
	{
		if ($this->host_data_present)
			return;

		$stats = new Stats_Model();
		$this->hst = $stats->get_stats('host_totals');
		$this->hst_perf = $stats->get_stats('host_performance', null, $this->ps->program_start);

		$all = $this->hst->up + $this->hst->down + $this->hst->unreachable;
		if ($all == 0)
			$this->percent_host_health = 0.0;
		else
			$this->percent_host_health = number_format($this->hst->up/$all*100, 1);

		$this->host_data_present = true;
		return;
	}

	/**
	 * Fetch and calculate status for all services for current user
	 * @return bool indicating whether query worked
	 */
	public function service_status()
	{
		if ($this->service_data_present)
			return;

		$stats = new Stats_Model();
		$this->svc = $stats->get_stats('service_totals');
		$this->svc_perf = $stats->get_stats('service_performance', null, $this->ps->program_start);

		$all = $this->svc->ok + $this->svc->warning + $this->svc->critical + $this->svc->unknown;
		if ($all == 0)
			$this->percent_service_health = 0.0;
		else
			$this->percent_service_health = number_format($this->svc->ok/$all*100, 1);

		$this->service_data_present = true;
		return;
	}

	/**
	 * Analyze all status data for hosts and services
	 * Calls
	 * - host_status()
	 * - service_status()
	 * @return bool
	 */
	public function analyze_status_data()
	{
		$this->program_status();
		$this->host_status();
		$this->service_status();
		return empty($errors) ? true : false;
	}

	/**
	 * 	determine what hosts are causing network outages
	 * 	and the severity for each one found
	 */
	public function find_hosts_causing_outages()
	{
throw new Exception('implement');
/*
TODO: implement
		if ($this->outage_data_present)
			return true;
		try {
			$ls = Livestatus::instance();

			$result = $ls->query(<<<EOQ
GET hosts
Filter: state = 1
Columns: name services childs
EOQ
);

			foreach ($result as $res){
				$this->unreachable_hosts[$res[0]] = count($res[2]);
				$this->affected_hosts[$res[0]] = count($res[2]) + 1;
				$this->affected_services[$res[0]] = count($res[1]);
				# check if each host has any affected child hosts
				foreach ($res[2] as $sub) {
					if (!($children = $this->get_child_hosts($sub)))
						$this->total_nonblocking_outages++;
					else
						$this->total_blocking_outages++;
					$this->affected_hosts[$res[0]] += $children['hosts'];
					$this->unreachable_hosts[$res[0]] += $children['hosts'];
					$this->affected_services[$res[0]]+= $children['services'];
				}
			}
		} catch (LivestatusException $ex) {
			return false;
		}

		$this->outage_data_present = true;
		return true;
*/
	}

	/**
	 * Fetch child hosts for a host
	 * @param $host_id Id of the host to fetch children for
	 * @return True on success, false on errors
	 */
	private function get_child_hosts($host_name=false)
	{
throw new Exception('implement');
return false;
/*
TODO: implement
		$ls = Livestatus::instance();

		$result = $ls->query(<<<EOQ
GET hosts
Filter: name = $host_name
Columns: services childs
EOQ
);

		$children = 0;
		$children_services = 0;
		foreach ($result as $res) {
			$children_services += count($res[0]);
			foreach ($res[1] as $sub_host) {
				$children++;
				$out = $this->get_child_hosts($sub_host);
				$children += $out['hosts'];
				$children_services += $out['services'];
			}
		}
		return array('hosts' => $children, 'services' => $children_services);
*/
	}

	/**
	 * Translates a given status from db to a readable string
	 */
	public static function status_text($db_status=false, $type='host')
	{
		$host_states = array(
			self::HOST_UP => 'UP',
			self::HOST_DOWN => 'DOWN',
			self::HOST_UNREACHABLE => 'UNREACHABLE',
			self::HOST_PENDING => 'PENDING'
		);

		$service_states = array(
			self::SERVICE_OK => 'OK',
			self::SERVICE_WARNING => 'WARNING',
			self::SERVICE_CRITICAL => 'CRITICAL',
			self::SERVICE_PENDING => 'PENDING',
			self::SERVICE_UNKNOWN => 'UNKNOWN'
		);

		$retval = false;
		switch ($type) {
			case 'host': case 'hostgroup':
				if (array_key_exists($db_status, $host_states)) {
					$retval = $host_states[$db_status];
				}
				break;
			case 'service': case 'servicegroup':
				if (array_key_exists($db_status, $service_states)) {
					$retval = $service_states[$db_status];
				}
				break;
		}
		return $retval;
	}

	/**
	 * List available states for host or service
	 *
	 * @param $what string 'host' (or 'service')
	 * @return array
	 */
	public function get_available_states($what='host')
	{
		switch($what) {
			case 'host':
				return array(
					self::HOST_UP => 'UP',
					self::HOST_DOWN => 'DOWN',
					self::HOST_UNREACHABLE => 'UNREACHABLE',
					self::HOST_PENDING => 'PENDING'
				);
			case 'service':
				return array(
					self::SERVICE_OK => 'OK',
					self::SERVICE_WARNING => 'WARNING',
					self::SERVICE_CRITICAL => 'CRITICAL',
					self::SERVICE_PENDING => 'PENDING',
					self::SERVICE_UNKNOWN => 'UNKNOWN'
				);
			default:
				return array();
		}
	}

	/**
	 * Fetch information regarding the various merlin nodes
	 * @param $host Unused
	 * @return Array with various info elements
	 */
	public function get_merlin_node_status($host=null)
	{
		$sql = false;
		$db = New Database();
		$cols = array('instance_name' => false, 'instance_id' => false,
				'is_running' => false, 'last_alive' => false);
		$sql = "SELECT " . implode(',', array_keys($cols)) . " FROM program_status ORDER BY instance_name";

		$result = $db->query($sql);
		$result_set = array();

		foreach ($result as $row) {
			$result_set[$row->instance_id]['instance_name'] = $row->instance_name;
			$result_set[$row->instance_id]['instance_id'] = $row->instance_id;
			$result_set[$row->instance_id]['is_running'] = $row->is_running;
			$result_set[$row->instance_id]['last_alive'] = $row->last_alive;
			$result_set[$row->instance_id]['host']['checks'] = Current_status_Model::get_merlin_num_checks("host", $row->instance_id);
			$result_set[$row->instance_id]['host']['latency'] = Current_status_Model::get_merlin_min_max_avg('host', 'latency' , $row->instance_id);
			$result_set[$row->instance_id]['host']['exectime'] = Current_status_Model::get_merlin_min_max_avg('host', 'execution_time' , $row->instance_id);
			$result_set[$row->instance_id]['service']['checks'] = Current_status_Model::get_merlin_num_checks("service", $row->instance_id);
			$result_set[$row->instance_id]['service']['latency'] = Current_status_Model::get_merlin_min_max_avg('service', 'latency' , $row->instance_id);
			$result_set[$row->instance_id]['service']['exectime'] = Current_status_Model::get_merlin_min_max_avg('service', 'execution_time' , $row->instance_id);

		}

		return $result_set;
	}

	/**
	 * Fetch the number of checks performed by a specific merlin node
	 *
	 * @param $table The table to use ('host' or 'service')
	 * @param $iid The instance id we want to check for
	 * @return Number of checks executed by the node with iid $iid
	 */
	public function get_merlin_num_checks($table, $iid=false)
	{
		$sql = false;
		$db = New Database();
		$sql = "SELECT COUNT(*) as total FROM $table";
		if ($iid !== false) {
			$sql.= " WHERE instance_id = $iid";
		}

		if (!empty($sql)){
			$result = $db->query($sql);
			foreach ($result as $row) {
				return (int)$row->total;
			}
		}
		return false;
	}

	/**
	 * Get min, average and max values from a random table
	 *
	 * @param $table Usually 'host' or 'service', though table will work
	 * @param $column The column to get values from. Must be numerical
	 * @param $iid instance_id of the Merlin node we're interested in
	 * @return A string in the format "min / avg / max"
	 */
	public function get_merlin_min_max_avg($table, $column, $iid=false)
	{
		$sql = false;
		$db = New Database();

		$sql = "SELECT min($column) as min, avg($column) as avg, max($column) as max FROM $table";
		if ($iid != false) {
			$sql.= " WHERE instance_id = $iid";
		}

		if (!empty($sql)) {
			$result = $db->query($sql);
			foreach ($result as $row) {
				return number_format($row->min, 3) . " / " . number_format($row->avg, 3) . " / " . number_format($row->max, 3);
			}
		}
		return false;
	}
}
