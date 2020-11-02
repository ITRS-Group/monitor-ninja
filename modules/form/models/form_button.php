<?php

/**
 * Represents a form button
 */
abstract class Form_Button_Model {

	private $name;
	private $pretty_name;

	/**
	 * Initialize the name and pretty name for the field.
	 *
	 * There is (almost) no fields that shouldn't take options. Thus (almost)
	 * all field types needs to override this method. Thus not overriding this
	 * methods is (most likely) an error. And those can do it anyway. Thus
	 * protected.
	 *
	 * @param $name string
	 * @param $pretty_name string
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

}
