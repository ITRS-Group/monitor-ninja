<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Options_Model */

$default = $form->get_default($field->get_name(), "");

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<select class="nj-form-option" name="' . $field->get_name() . '" />';
foreach ( $field->get_options() as $value => $label ) {
	$selectstr = ($default == $value) ? ' selected="selected"' : '';
	echo '<option value="' . html::specialchars( $value ) . '"'.$selectstr.'>' . html::specialchars( $label ) . '</option>';
}
echo '</select>';
echo '</label>';
echo '</div>';
