<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");

echo '<div class="nj-form-field nj-form-field-checkbox">';
echo '<label>';
/* Order of input a nj-form-label matters */
echo '<input type="checkbox" class="nj-form-option" name="'.$field->get_name().'" ' . ($default ? 'checked' : '') . ' />';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '</label>';
echo '</div>';
