<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Option_Model */

$value = $form->get_value($field->get_name(), "");
$render = $field->get_force_render();
$options = $field->get_options();

/**
 * Render the options as a select autopmatically if the amount of options
 * exceeds 3, otherwise render as radio, you can force a select rendering by
 * setting $force = "select"
 */
if ($render === "select" || ($render === false && count($options) > 3)) {
	View::factory('form/select', array(
		"value" => $value,
		"field" => $field
	))->render(true);
} else {
	View::factory('form/radiobuttons', array(
		"value" => $value,
		"field" => $field
	))->render(true);
}
