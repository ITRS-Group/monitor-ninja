<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Option_Model */

$default = $form->get_value($field->get_name(), "");
$render = $field->get_force_render();
$options = $field->get_options();

if ($render === "select" || ($render === false && count($options) > 3)) {
	$element_id = 'element_id_'.uniqid();
	echo '<div class="nj-form-field">';
	echo '<label>';
	echo '<div class="nj-form-label"><label for="'.$element_id.'">' . html::specialchars($field->get_pretty_name()) . '</label></div>';
	echo '<select class="nj-form-option" id="'.$element_id.'" name="' . $field->get_name() . '">';
	foreach ( $field->get_options() as $value => $label ) {
		$selectstr = ($default == $value) ? ' selected="selected"' : '';
		echo '<option value="' . html::specialchars( $value ) . '"'.$selectstr.'>' . html::specialchars( $label ) . '</option>';
	}
	echo '</select>';
	echo '</label>';
	echo '</div>';
} else {
	echo '<div class="nj-form-field">';
	echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
	echo '<div class="nj-form-field-radio-buttons">';
	foreach ( $field->get_options() as $value => $label ) {
		$element_id = 'element_id_'.uniqid();
		echo '<div class="nj-form-field-radio">';
		echo '<label>';
		$selectstr = ($default == $value) ? ' checked' : '';
		/* Order of input and nj-form-label matters! */
		echo '<input id="'.$element_id.'" name="' . $field->get_name() . '" type="radio" value="' . html::specialchars($value) . '"'.$selectstr.'>';
		echo '<div class="nj-form-label"><label for="'.$element_id.'">' . html::specialchars($label) . '</label></div>';
		echo '</label>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}
