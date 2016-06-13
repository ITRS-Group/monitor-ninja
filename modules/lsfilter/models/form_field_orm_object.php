<?php
class Form_Field_ORM_Object_Model extends Form_Field_Model {
	private $table;

	public function __construct($name, $pretty_name, $table) {
		parent::__construct( $name, $pretty_name );
		$this->table = $table;
	}
	public function get_type() {
		return 'orm_object';
	}
	public function process_data(array $raw_data, Form_Result_Model $result) {
		$name = $this->get_name();
		if (! isset( $raw_data [$name] ))
			throw new FormException( "Unknown field $name" );
		if (! is_string( $raw_data [$name] ))
			throw new FormException( "$name is not a text field" );
		$key = $raw_data[$name];
		$object = ObjectPool_Model::pool($this->table)->fetch_by_key($key);
		if(!$object)
			throw new FormException( "$name doesn't point to a valid object" );
		$result->set_value($name, $object);
	}

	public function get_table() {
		return $this->table;
	}
}
