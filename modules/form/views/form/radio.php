<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Options_Model */

$default = $form->get_default($field->get_name(), "");

echo '<div class="nj-form-field">';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
foreach ( $field->get_options() as $value => $label ) {
	echo '<div class="nj-form-field-radio">';
	echo '<label>';
	$selectstr = ($default == $value) ? ' checked' : '';
	/* Order of input and nj-form-label matters! */
	echo '<input name="' . $field->get_name() . '" type="radio" value="' . html::specialchars($value) . '"'.$selectstr.'>';
	echo '<div class="nj-form-label">' . html::specialchars($label) . '</div>';
	echo '</label>';
	echo '</div>';
}
echo '</div>';
