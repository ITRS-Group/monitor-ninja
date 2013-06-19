<?php

class AverageStateCalculator extends WorstStateCalculator
{
	public function add_event($row = false)
	{
		foreach ($this->sub_reports as $idx => $rpt) {
			$rpt->add_event($row);
		}
	}

	public function calculate_object_state() { /* No thanks */ }

	public function finalize()
	{
		foreach ($this->sub_reports as $rpt) {
			$rpt->finalize();
		}

		foreach ($this->sub_reports as $rpt) {
			foreach ($rpt->st_raw as $type => $value) {
				$this->st_raw[$type] = (isset($this->st_raw[$type])?$this->st_raw[$type]:0) + $value;
			}
		}
		$c = count($this->sub_reports);
		foreach ($this->st_raw as $type => $val) {
			$this->st_raw[$type] = $val / $c;
		}
	}
}
