<?php

/**
 * State calculator for multiple objects that returns the best state at any point in time
 */
class BestStateCalculator extends WorstStateCalculator
{
	public function calculate_object_state()
	{
		if ($this->st_is_service)
			$states = array(Reports_Model::SERVICE_OK, Reports_Model::SERVICE_WARNING, Reports_Model::SERVICE_CRITICAL, Reports_Model::SERVICE_UNKNOWN, Reports_Model::SERVICE_PENDING);
		else
			$states = array(Reports_Model::HOST_UP, Reports_Model::HOST_DOWN, Reports_Model::HOST_UNREACHABLE, Reports_Model::HOST_PENDING);

		$final_state = $states[count($states) - 1];

		foreach ($states as $state) {
			$keys = array_keys($this->st_sub[$state]);
			// Sort downtime states outside downtime first
			sort($keys);
			foreach ($keys as $in_dt) {
				if (!empty($this->st_sub[$state][$in_dt])) {
					$final_state = $state;
					$this->st_dt_depth = $in_dt;
					break 2;
				}
			}
		}

		$this->st_obj_state = $this->filter_excluded_state($final_state);
	}
}
