<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");

$element_id = 'element_id_'.uniqid();

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label"><label for="'.$element_id.'">' . html::specialchars($field->get_pretty_name()) . '</label></div>';
echo '<input type="text" class="nj-form-option" id="'.$element_id.'" name="'.html::specialchars($field->get_name()).'" value="'.html::specialchars($default).'" />';
echo '</label>';
echo '</div>';
