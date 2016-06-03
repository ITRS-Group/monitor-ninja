<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_default($field->get_name(), "");

echo '<div class="njform-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<div class="njform-field">';
echo '<input type="text" class="njform-option" name="'.$field->get_name().'" value="'.html::specialchars($default).'" />';
echo '</div>';