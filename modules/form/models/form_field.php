<?php

/**
 * Represents a form field, or a set of form fields
 * (@see Form_Field_Group_Model).
 */
abstract class Form_Field_Model {
	private $help;
	private $name;
	private $pretty_name;

	/**
	 * Initialize the name and pretty name for the field.
	 *
	 * There is (alsmost) no fields that shouldn't take options. Thus (almost)
	 * all field types needs to override this method. Thus not overriding this
	 * methods is (most likely) an error. And those can do it anyway. Thus
	 * protected.
	 *
	 * @param $name Name
	 *        	of the field
	 * @param $pretty_name Visible
	 *        	name of the field
	 */
	protected function __construct($name, $pretty_name) {
		$this->name = $name;
		$this->pretty_name = $pretty_name;
	}

	/**
	 * Get the field name.
	 * This maps to the field name both in processed and unprocessed data
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the name as it should be visisble in the UI.
	 * Can be null for some fields.
	 *
	 * @return string
	 */
	public function get_pretty_name() {
		return $this->pretty_name;
	}

	/**
	 * Get the view of this field, relative to form/ directory in the views
	 * folder.
	 *
	 * It should be possible to do "skins" for forms by adding a subdirectory
	 * in the form/ folder, which should re-implement the views.
	 *
	 * @return string
	 */
	public abstract function get_type();

	/**
	 * Process the input data from a raw request array. This should validate
	 * the input and return an associative array of the form data for this field.
	 *
	 * For most fields, this is an array of only one value, but can be more if
	 * field is of type fieldset, conditional or similar
	 *
	 * This methods may also look objects up in the case of validation. For
	 * example, an object selector can actually return an object, a set
	 * selection can return a set.
	 *
	 * If not matching, this methods throws an FormException or derivative.
	 *
	 * @param $raw_data an
	 *        	array of data formatted as $_POST
	 * @throws FormException
	 */
	public abstract function process_data(array $raw_data, Form_Result_Model $result);

	/**
	 * Add a more descriptive help string for this specific form element.
	 * This proxies the help::render() interface.
	 *
	 * @param $key string
	 * @param $controller string
	 */
	public function set_help($key, $controller) {
		$this->help = array($key, $controller);
	}
}
