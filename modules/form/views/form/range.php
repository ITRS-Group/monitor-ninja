<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_default($field->get_name(), 0);

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<span class="nj-form-field-range">';
echo '<span class="nj-form-field-range-min">' . $field->get_min() . '</span>';
echo '<span class="nj-form-field-range-hover">' . html::specialchars($default) . '</span>';
echo '<input type="range" step="' . $field->get_step() . '" min="' . $field->get_min() . '" max="' . $field->get_max() . '" class="nj-form-option" name="'.$field->get_name().'" value="'.html::specialchars($default).'" />';
echo '<span class="nj-form-field-range-max">' . $field->get_max() . '</span>';
echo '</span>';
echo '</label>';
echo '</div>';
