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
	const HOST_CHECK_ACTIVE = 0;    /**< Nagios performed the host check */
	const HOST_CHECK_PASSIVE = 1;   /**< the host check result was submitted by an external source */
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
			return $this->ps;

		$ls       = Livestatus::instance();
		$this->ps = $ls->getProcessInfo(array('auth'=>false));
		$this->program_data_present = true;
		return $this->ps;
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
		return empty($errors);
	}

	/**
	 * Translates a given status from db to a readable string
	 *
	 * @param $db_status int
	 * @param $db_checked boolean
	 * @param $type string = host
	 * @return string
	 */
	public static function status_text($db_status, $db_checked, $type='host')
	{
		if (!$db_checked)
			return 'PENDING';

		# pending down here doesn't exist anymore, but handle it anyway
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
}
