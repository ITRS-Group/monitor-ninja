<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Fieldset_Model */

echo '<fieldset>';
foreach($field->get_fields() as $subfield) {
	$form->get_field_view($subfield)->render(true);
}
echo '</fieldset>';
