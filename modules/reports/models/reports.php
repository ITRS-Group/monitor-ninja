<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Basic reports model that only exists for legacy reasons.
 * Only contains crap that should be refactored away.
 */
class Reports_Model extends Model
{
	// state codes
	const STATE_PENDING = -1; /**< Magical state for unchecked objects. In other parts of ninja, 6 is used for this */
	const STATE_OK = 0; /**< "Everything is fine"-state */
	const HOST_UP = 0; /**< Host is up */
	const HOST_DOWN = 1; /**< Host is down */
	const HOST_UNREACHABLE = 2; /**< Host is unreachable */
	const HOST_PENDING = -1; /**< Magical state for unchecked hosts. In other parts of ninja, 6 is used for this */
	const HOST_EXCLUDED = -2; /**< Magical state when a host event falls outside of the specified timeperiod */
	const HOST_ALL = 7; /**< Bitmask for any non-magical host state */
	const SERVICE_OK = 0; /**< Service is up */
	const SERVICE_WARNING = 1; /**< Service is warning */
	const SERVICE_CRITICAL = 2; /**< Service is critical */
	const SERVICE_UNKNOWN = 3; /**< Service is unknown */
	const SERVICE_PENDING = -1; /**< Magical state for unchecked services. In other parts of ninja, 6 is used for this */
	const SERVICE_EXCLUDED = -2; /**< Magical state when a service event falls outside of the specified timeperiod */
	const SERVICE_ALL = 15; /**< Bitmask for any non-magical service state */
	const PROCESS_SHUTDOWN = 103; /**< Nagios code for when it is shut down */
	const PROCESS_RESTART = 102; /**< Nagios code for when it is restarted - not normally added to report_data, check for stop and start instead */
	const PROCESS_START = 100; /**< Nagios code for when it is started */
	const SERVICECHECK = 701; /**< Nagios code for a service check */
	const HOSTCHECK =  801; /**< Nagios code for a host check */
	const DOWNTIME_START = 1103; /**< Nagios code for downtime start */
	const DOWNTIME_STOP = 1104; /**< Nagios code for downtime stop, either because it ended or because it was deleted */
	const DEBUG = true; /**< Debug bool - can't see this is ever false */

	/** A map of state ID => state name for hosts. FIXME: one of a gazillion */
	static public $host_states = array(
		Reports_Model::HOST_UP => 'up',
		Reports_Model::HOST_DOWN => 'down',
		Reports_Model::HOST_UNREACHABLE => 'unreachable',
		Reports_Model::HOST_PENDING => 'pending',
		Reports_Model::HOST_EXCLUDED => 'excluded');

	/** A map of state ID => state name for services. FIXME: one of a gazillion */
	static public $service_states = array(
		Reports_Model::SERVICE_OK => 'ok',
		Reports_Model::SERVICE_WARNING => 'warning',
		Reports_Model::SERVICE_CRITICAL => 'critical',
		Reports_Model::SERVICE_UNKNOWN => 'unknown',
		Reports_Model::SERVICE_PENDING => 'pending',
		Reports_Model::SERVICE_EXCLUDED => 'excluded');



	/** The provided options */
	protected $options = false;
	/** The timeperiod associated with this report */
	protected $timeperiod;
	protected $db_table = 'report_data';

		/**
	 * Constructor
	 * @param $options An instance of Report_options
	 */
	public function __construct(Report_options $options)
	{
		parent::__construct();

		if (self::DEBUG === true) {
			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 0);
			assert_options(ASSERT_QUIET_EVAL, 0);
			assert_options(ASSERT_BAIL, 1);

			# use report helper callback
			assert_options(ASSERT_CALLBACK, array('reports', 'lib_reports_assert_handler'));
		}

		$this->options = $options;
		$this->timeperiod = Old_Timeperiod_Model::instance($options);
		$this->timeperiod->resolve_timeperiods();
	}

	/**
	 * Helper method for retrieving a user-friendly representation for nagios codes
	 * Randomly put into the report model, because both "model" and "helper"
	 * contains "e" and "l", so who can keep them apart?
	 *
	 * @param $event_type int
	 * @param $object_type string = null (host or service)
	 * @param $short boolean = false (true = key, false = English)
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public static function event_type_to_string($event_type, $object_type = null, $short = false) {
		$events = array(
			self::PROCESS_SHUTDOWN => array(
				'short' => 'monitor_shut_down',
				'full' => _('Monitor shut down')
			),
			self::PROCESS_RESTART => array(
				'short' => 'monitor_restart',
				'full' => _('Monitor restart')
			),
			self::PROCESS_START => array(
				'short' => 'monitor_start',
				'full' => _('Monitor started')
			),
			self::SERVICECHECK => array(
				'short' => 'service_alert',
				'full' => _('Service alert')
			),
			self::HOSTCHECK => array(
				'short' => 'host_alert',
				'full' => _('Host alert')
			),
			self::DOWNTIME_START => array(
				'short' => 'scheduled_downtime_start',
				'full' => _($object_type . ' has entered a period of scheduled downtime')
			),
			self::DOWNTIME_STOP => array(
				'short' => 'scheduled_downtime_stop',
				'full' => _($object_type . ' has exited a period of scheduled downtime')
			)
		);
		if(!isset($events[$event_type])) {
			throw new InvalidArgumentException("Invalid event type '$event_type' in ".__METHOD__.":".__LINE__);
		}
		return $events[$event_type][$short ? 'short' : 'full'];
	}

	/**
	*	Check that we have a valid database installed and usable.
	*/
	public function _self_check()
	{
		try {
			# this will result in error if db_name section
			# isn't set in config/database.php
			$db = Database::instance();
		} catch (Kohana_Database_Exception $e) {
			return false;
		}
		$table_exists = false;
		if (isset($db)) {
			try {
				$table_exists = $db->table_exists($this->db_table);
			} catch (Kohana_Database_Exception $e) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}
}
