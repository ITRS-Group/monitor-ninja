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
	private $values = array();
	private $optional = array();
	private $buttons = array();

	/**
	 * Create a form with a given set of fields
	 *
	 * @param $action string
	 * @param $renderable_children array of From_Field_Model|Form_Button_Model
	 */
	public function __construct($action, array $renderable_children = array()) {
		static $index = 0;
		$index++;
		$this->action = $action;
		$this->id = 'nj-form-' . uniqid() . '-' . $index;
		foreach ($renderable_children as $child_node) {
			if($child_node instanceof Form_Button_Model) {
				$this->add_button($child_node);
			} else {
				$this->add_field($child_node);
			}
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
	 * @param $raw_data array of parameters fetched as $_POST
	 * @return array
	 */
	public function process_data(array $raw_data) {

		$result = new Form_Result_Model();

		foreach ($this->fields as $field) {
			/* @var $field Form_Field_Model */

			$field_name = $field->get_name();

			try {
				$field->process_data($raw_data, $result);
			} catch (MissingValueException $e) {
				$field_name = $e->get_field()->get_name();
				if (!in_array($field_name, $this->optional)) {
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
	 * Set the default values for the form
	 *
	 * @param $values array
	 */
	public function set_values(array $values) {
		$this->values = $values;
	}

	/**
	 * Set which fields are optional.
	 *
	 * Optional fields are handle in such a way that if the process_data
	 * function of that field throws a MissingValueException, that exception is ignored.
	 *
	 * Optional fields that throw MissingValueException are NOT part of the
	 * array result of the form->process_data call.
	 *
	 * @param $fieldnames array
	 */
	public function set_optional(array $fieldnames) {
		$this->optional = $fieldnames;
	}

	/**
	 * Returns an array of the fields that are optional.
	 *
	 * @return array
	 */
	public function get_optional() {
		return $this->optional;
	}

	/**
	 * Returns whether a Form_Field_Model is required within this form
	 *
	 * @param $field Form_Field_Model
	 * @return bool
	 */
	public function is_field_required (Form_Field_Model $field) {
		return !in_array($field->get_name(), $this->optional, true);
	}
}
