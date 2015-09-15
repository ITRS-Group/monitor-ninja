<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Performance model
 * @author op5 AB
 */
class Performance_Model extends Model
{

	/**
	 * Constructs the Performance model
	 */
	public function __construct () {

		$ls = Livestatus::instance();
		$set = StatusPool_Model::all();

		$it = $set->it(false, array(), 1, 0);
		$status = $it->current();

		$this->program = $status;
		$this->service = $ls->getServicePerformance($status->get_program_start());
		$this->host = $ls->getHostPerformance($status->get_program_start());

	}

	/**
	 * Retrieve the active service loads
	 * @return array The active service loads
	 */
	public function get_active_service_loads () {
		return array(
			1 => $this->service->active_1_sum,
			5 => $this->service->active_5_sum,
			15 => $this->service->active_15_sum,
			60 => $this->service->active_60_sum
		);
	}

	/**
	 * Retrieve the active host loads
	 * @return array The active host loads
	 */
	public function get_active_host_loads () {
		return array(
			1 => $this->host->active_1_sum,
			5 => $this->host->active_5_sum,
			15 => $this->host->active_15_sum,
			60 => $this->host->active_60_sum
		);
	}

	/**
	 * Retrieve the passive service loads
	 * @return array The passive service loads
	 */
	public function get_passive_service_loads () {
		return array(
			1 => $this->service->passive_1_sum,
			5 => $this->service->passive_5_sum,
			15 => $this->service->passive_15_sum,
			60 => $this->service->passive_60_sum
		);
	}

	/**
	 * Retrieve the passive host loads
	 * @return array The passive host loads
	 */
	public function get_passive_host_loads () {
		return array(
			1 => $this->host->passive_1_sum,
			5 => $this->host->passive_5_sum,
			15 => $this->host->passive_15_sum,
			60 => $this->host->passive_60_sum
		);
	}

	/**
	 * Get a percentage value from $value of $of
	 * @return float
	 */
	public function percentage_of ($value, $of) {
		if ($of > 0) return ($value * 100) / $of;
		return 0;
	}

}
