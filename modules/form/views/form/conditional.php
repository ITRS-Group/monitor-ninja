<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Conditional_Model */

$subfield = $field->get_field();

echo '<div class="nj-form-conditional" data-njform-rel="'.html::specialchars($field->get_rel()).'" data-njform-value="'.html::specialchars($field->get_value()).'">';
$form->get_field_view($subfield)->render(true);
echo '</div>';
