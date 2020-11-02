<?php

/**
 * This class solves the issue of being able to inject HTML between form
 * elements, while still being able to do a simple `$form->get_view()` and rely
 * on the standard view rendering the form as you'd like to. In contrast, had
 * this class not existed, you would need to create custom form views for every
 * form that wants to inject a piece of text between form elements.
 */
class Form_Field_HtmlDecorator_Model extends Form_Field_Model {

	/**
	 * @param $html string The HTML to inject
	 */
	public function __construct($html) {
		$this->html = $html;
	}

	/**
	 * @return string
	 */
	public function get_html() {
		return $this->html;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return 'htmldecorator';
	}

	/**
	 * @param $raw_data array
	 * @param $result Form_Result_Model
	 */
	public function process_data(array $raw_data, Form_Result_Model $result) {
		// no nothing, this class is purely for decoration when
		// rendering
	}
}
