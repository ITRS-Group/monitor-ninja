<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Conditional_Model */

$subfield = $field->get_field();

echo '<div class="njform-conditional" data-njform-rel="'.$field->get_rel().'" data-njform-value="'.$field->get_value().'">';
$form->get_view($subfield)->render(true);
echo '</div>';