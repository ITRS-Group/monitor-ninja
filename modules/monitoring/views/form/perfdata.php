<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Perfdata_Model */

$default = $form->get_value($field->get_name(), "");

$element_id = 'element_id_'.uniqid();
echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label"><label for="'.$element_id.'">' . html::specialchars($field->get_pretty_name()) . '</label></div>';
echo '<select class="nj-form-option" data-njform-target="'.html::specialchars($field->get_target_model()).'" id="'.$element_id.'" name="' . html::specialchars($field->get_name()) . '" />';
foreach ($field->get_options() as $value => $label ) {
	$selectstr = ($default == $value) ? ' selected="selected"' : '';
	echo '<option value="'.html::specialchars($label).'"'.$selectstr.'>'.html::specialchars($label).'</option>';
}
echo '</select>';
echo '</label>';
echo '</div>';
