<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Fieldset_Model */

if($field->get_pretty_name() !== false) {
	echo '<div class="njform-title">' . html::specialchars($field->get_pretty_name()) . '</div>';
}
foreach($field->get_fields() as $subfield) {
	$form->get_view($subfield)->render(true);
}