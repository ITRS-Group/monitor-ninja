<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Fieldset_Model */

echo '<fieldset>';
if($field->get_pretty_name() !== false) {
	echo '<div class="nj-form-title">' . html::specialchars($field->get_pretty_name()) . '</div>';
}
foreach($field->get_fields() as $subfield) {
	$form->get_view($subfield)->render(true);
}
echo '</fieldset>';
