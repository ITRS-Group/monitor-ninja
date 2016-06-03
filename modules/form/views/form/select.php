<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Options_Model */

$default = $form->get_default($field->get_name(), "");

echo '<div class="njform-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<div class="njform-field">';
echo '<select class="njform-option" name="' . $field->get_name() . '" />';
foreach ( $field->get_options() as $value => $label ) {
	$selectstr = ($default == $value) ? ' selected="selected"' : '';
	echo '<option value="' . html::specialchars( $value ) . '"'.$selectstr.'>' . html::specialchars( $label ) . '</option>';
}
echo '</select>';
echo '</div>';