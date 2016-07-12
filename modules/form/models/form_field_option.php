<?php

/**
 * Let the user choose one of several options that are known on beforehand.
 */
class Form_Field_Option_Model extends Form_Field_Model {

	private $options;
	private $force_render;

	/**
	 * @param $name string
	 * @param $pretty_name string
	 * @param $options array
	 * @param $force_render boolean (render options as radio buttons or a select?)
	 */
	public function __construct($name, $pretty_name, array $options, $force_render = false) {
		parent::__construct( $name, $pretty_name );
		$this->options = $options;
		$this->force_render = $force_render;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'options';
	}

	/**
	 * @return mixed
	 */
	public function get_force_render() {
		return $this->force_render;
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 * @throws FormException
	 * @throws MissingValueException
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (! isset( $raw_data [$name] )) {
			throw new MissingValueException("Missing a value for the field '$name'", $this);
		}
		if (! is_string( $raw_data [$name] )) {
			throw new FormException( "$name does not have a option value", $this);
		}
		if (! isset( $this->options [$raw_data [$name]] )) {
			throw new FormException( "$name has not a valid option value" , $this);
		}
		$result->set_value($name, $raw_data[$name]);
	}
}
