<?php
class Form_Field_Range_Model extends Form_Field_Model {

	const RANGE_STEP_DEFAULT = "RANGE_STEP_DEFAULT";

	protected $min = 0;
	protected $max = 100;

	public function __construct($name, $pretty_name, $min = 0, $max = 100, $step = Form_Field_Range_Model::RANGE_STEP_DEFAULT) {
		parent::__construct($name, $pretty_name);
		$this->min = $min;
		$this->max = $max;
		if ($step === Form_Field_Range_Model::RANGE_STEP_DEFAULT) {
			$this->step = ceil(($this->max - $this->min) / 100);
		} else {
			$this->step = $step;
		}
	}

	public function get_type() {
		return 'range';
	}

	public function get_min () {
		return $this->min;
	}

	public function get_max () {
		return $this->max;
	}

	public function get_step () {
		return $this->step;
	}

	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (!isset($raw_data[$name]))
			throw new FormException( "Unknown field $name");
		if (!is_numeric($raw_data[$name]))
			throw new FormException( "$name is not a number field");
		if ((float) $raw_data[$name] < $this->min || (float)$raw_data[$name] > $this->max)
			throw new FormException( "Invalid value " . $raw_data[$name] . " for range " . $this->min. " - " . $this->max . " named $name");
		$result->set_value($name, (float)$raw_data[$name]);
	}
}
