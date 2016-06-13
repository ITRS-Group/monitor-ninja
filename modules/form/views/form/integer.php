<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), 0);

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<input type="number" patter="^\d+$" class="nj-form-option" name="'.$field->get_name().'" value="'.html::specialchars($default).'" />';
echo '</label>';
echo '</div>';
