<?php
/**
 * Model a form, which can be rendered
 */
class Form_Model {
	/**
	 * An array of the fields in this form
	 *
	 * @var array of Form_Field_Model
	 */
	private $fields = array();

	/**
	 * Storage for default values, as seen processed
	 * s*
	 *
	 * @var array of default values, indexed on field names
	 */
	private $values = array();

	/**
	 * Action, for where to submit the form
	 */
	private $action = '';

	/**
	 * Create a form with a given set of fields
	 *
	 * @param $fields array
	 *        	of fields. All must be derivates of From_Field_Model
	 */
	public function __construct($action, array $fields = array()) {
		$this->action = $action;
		foreach ( $fields as $field ) {
			$this->add_field( $field );
		}
	}
	/**
	 * Add a new field to the end of the form
	 *
	 * @param $field Form_Field_Model
	 */
	public function add_field(Form_Field_Model $field) {
		$this->fields [] = $field;
	}

	/**
	 * Get the list of fields in the form
	 *
	 * @return array of Form_Field_Model
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Get a view representing a given field in the form.
	 * If no field is specified, a view for the entire form is returned
	 *
	 * @param $field Form_Field_Model
	 *        	Field object or null
	 */
	public function get_view(Form_Field_Model $field = null) {
		if ($field === null) {
			return new View( 'form/render', array( 'form' => $this, 'action' => $this->action,'method' => 'GET' ) );
		}
		return new View( 'form/' . $field->get_type(), array( 'form' => $this, 'field' => $field ) );
	}

	/**
	 * Process and validate raw input data to the form
	 *
	 * Map the raw data from for example $_POST (as input prameter) to a
	 * validated and processed set of elements.
	 *
	 * Elements not related to the form is dropped (might also be fields
	 * depending on other fields that are hidden)
	 *
	 * The result might also be processed to match a given format. For example,
	 * an object selector which takes a unique key string as raw argument can
	 * return an actual object instance.
	 *
	 * @param $raw_data array of parameters fetched as $_POST
	 */
	public function process_data(array $raw_data) {
		$result = new Form_Result_Model();
		foreach ($this->fields as $field) {
			/* @var $field Form_Field_Model */
			$field->process_data($raw_data, $result);
		}
		return $result->to_array();
	}

	/**
	 * Get the default value for a field, formatted as result of process_data()
	 *
	 * It should be possible to recreate a form state as:
	 *
	 * $form_b->set_values( $form_a->process_data($rawdata) );
	 *
	 * @param $fieldname string,
	 *        	fieldname
	 * @param $default Default
	 *        	value if no default value is found in the array. Optional, then null
	 */
	public function get_value($fieldname, $default = null) {
		if (isset($this->values[$fieldname]))
			return $this->values[$fieldname];
		return $default;
	}

	/**
	 * Set the default values for the form
	 *
	 * @param $values array
	 *        	of default values
	 */
	public function set_values(array $values) {
		$this->values = $values;
	}
}
