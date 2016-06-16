<?php

/**
 * Let the user pick one of the performance data source for a specific host- or service model. Usage:
 *
 * $form_model = new Form_Model('action', array(
 *   new Form_Field_ORMObject_Model('host', 'A host', array('hosts')),
 *   new Form_Field_Perfdata_Model('perfdata_src', 'Pick a performance data source', 'host')
 * ));
 */
class Form_Field_Perfdata_Model extends Form_Field_Model {
	private $target_model;
	private $options;

	/**
	 * @param $name string
	 * @param $pretty_name string
	 * @param $target_model string
	 * @param array $options = array()
	 */
	public function __construct($name, $pretty_name, $target_model, array $options = array()) {
		parent::__construct($name, $pretty_name);
		$this->target_model = $target_model;
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'perfdata';
	}

	/**
	 * @return string
	 */
	public function get_target_model() {
		return $this->target_model;
	}

	/**
	 * Since we depend on the value (ORM object) of $target_model, we only
	 * know which the specific options are after @see
	 * Form_Model::set_values() has been called, or when the form has been
	 * dynamically rendered by JS. It's perfectly OK that this is empty,
	 * and handled by JS.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if(!isset($raw_data[$name])) {
			throw new FormException("Missing value for '$name'");
		}
		if(!is_string($raw_data[$name])){
			throw new FormException("'$name' has a bad value, needs a string");
		}
		if(!$result->has_value($this->target_model)) {
			throw new FormException("Target model '$this->target_model' missing");
		}
		$orm_model = $result->get_value($this->target_model);
		if(!($orm_model instanceof Host_Model) && !($orm_model instanceof Service_Model)) {
			throw new FormException("'$this->target_model' should hold a host or a service");
		}
		$valid_performance_data = $orm_model->get_perf_data();
		if(!$valid_performance_data) {
			throw new FormException("'$this->target_model' contains an object that doesn't have performance data");
		}
		if(!array_key_exists($raw_data[$name], $valid_performance_data)) {
			throw new FormException("The performance data source '".$raw_data[$name]."' is not found on the given object");
		}
		$result->set_value($name, $raw_data[$name]);
	}
}
