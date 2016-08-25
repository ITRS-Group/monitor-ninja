<?php
/**
 * Model a form, which can be rendered
 */
class Form_Model {

	/**
	 * Action, for where to submit the form
	 */
	private $action = '';
	private $fields = array();
	private $id = "";
	private $missing_fields_cb = array();
	private $values = array();
	private $buttons = array();

	/**
	 * Create a form with a given set of fields
	 *
	 * @param $action string
	 * @param $fields array of From_Field_Model
	 */
	public function __construct($action, array $fields = array()) {
		static $index = 0;
		$index++;
		$this->action = $action;
		$this->id = 'nj-form-' . uniqid() . '-' . $index;
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
	 * Get a view representing a given field in the form.
	 * If no field is specified, a view for the entire form is returned
	 *
	 * @param $field Form_Field_Model
	 * @return View
	 */
	public function get_field_view(Form_Field_Model $field) {
		return new View('form/' . $field->get_type(), array(
			'form' => $this,
			'field' => $field
		));
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
	 * get the id of this form
	 *
	 * @return string id of this Form_Model
	 */
	public function get_id () {
		return $this->id;
	}

	/**
	 * Add a new button to the end of the form
	 *
	 * @param $button Form_Button_Model
	 */
	public function add_button(Form_Button_Model $button) {
		$this->buttons[] = $button;
	}

	/**
	 * Get a view representing a given button in the form.
	 * If no button is specified, a view for the entire form is returned
	 *
	 * @param $button Form_Button_Model
	 * @return View
	 */
	public function get_button_view(Form_Button_Model $button) {
		return new View('form/' . $button->get_type(), array(
			'form' => $this,
			'button' => $button
		));
	}

	/**
	 * Get the list of buttons in the form
	 *
	 * @return array of Form_Button_Model
	 */
	public function get_buttons() {
		return $this->buttons;
	}

	/**
	 * Get a view representing a given field in the form.
	 * If no field is specified, a view for the entire form is returned
	 *
	 * @return View
	 */
	public function get_view() {
		return new View('form/render', array(
			'form' => $this,
			'action' => $this->action,
			'method' => 'GET'
		));
	}

	/**
	 * Process and validate raw input data to the form
	 *
	 * Map the raw data from for example $_POST (as input prameter) to a
	 * validated and processed set of elements.
	 *
	 * Elements not related to the form are not returned (might also be fields
	 * depending on other fields that are hidden)
	 *
	 * The result might also be processed to match a given format. For example,
	 * an object selector which takes a unique key string as raw argument can
	 * return an actual object instance.
	 *
	 * @see set_missing_fields
	 *
	 * @param $raw_data array of parameters fetched as $_POST
	 * @return array
	 */
	public function process_data(array $raw_data) {
		$result = new Form_Result_Model();
		foreach ($this->fields as $field) {
			/* @var $field Form_Field_Model */
			try {
				$field->process_data($raw_data, $result);
			} catch(MissingValueException $e) {
				$field_name = $e->get_field()->get_name();
				if(array_key_exists($field_name, $this->missing_fields_cb)) {
					if(is_callable($this->missing_fields_cb[$field_name])) {
						call_user_func($this->missing_fields_cb[$field_name], $raw_data, $result);
					}
					// the value can also be "", for
					// example, which just makes the
					// form field entirely optional
				} else {
					throw $e;
				}
			}
		}
		return $result->to_array();
	}

	/**
	 * Get the value of a field, formatted as result of process_data()
	 *
	 * It should be possible to recreate a form state as:
	 *
	 * $form_b->set_values( $form_a->process_data($rawdata) );
	 *
	 * @param $fieldname string
	 * @param $default mixed = null
	 * @return mixed
	 */
	public function get_value($fieldname, $default = null) {
		if (isset($this->values[$fieldname]))
			return $this->values[$fieldname];
		return $default;
	}

	/**
	 * When executing @see process_data(), we can detect a missing field
	 * and act on it. This method let's you specify what should happen when
	 * a field is missing.
	 *
	 * Making a field optional:
	 *     $form->set_missing_fields_cb(array('my_field_name' => ''));
	 *
	 * Set a custom result if field is missing:
	 *     $form->set_missing_fields_cb(array(
	 *       'my_field_name' => function($raw_data, Form_Result_Model $res) {
	 *         $res['my_field_name'] = 2;
	 *       }
	 *     ))
	 *
	 * The method tries to be versatile by making the default case ("ignore
	 * value") easy to write, and also generic by allowing a callback so
	 * that the API won't have to expose different methods for each
	 * solution to the "missing input value" situation.
	 *
	 * @param $fields array
	 */
	public function set_missing_fields_cb(array $fields) {
		$this->missing_fields_cb = $fields;
	}

	/**
	 * Set the default values for the form
	 *
	 * @param $values array
	 */
	public function set_values(array $values) {
		$this->values = $values;
	}
}
