<?php
class Form_Field_Radio_Model extends Form_Field_Model {
	private $options;
	public function __construct($name, $pretty_name, array $options) {
		parent::__construct( $name, $pretty_name );
		$this->options = $options;
	}
	public function get_type() {
		return 'radio';
	}
	public function get_options() {
		return $this->options;
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (! isset( $raw_data [$name] ))
			throw new FormException( "Unknown field $name" );
		if (! is_string( $raw_data [$name] ))
			throw new FormException( "$name does not have a option value" );
		if (! isset( $this->options [$raw_data [$name]] ))
			throw new FormException( "$name has not a valid option value" );
		$result->set_value($name, $raw_data[$name]);
	}
}
