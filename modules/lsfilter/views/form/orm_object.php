<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

function singularize($plural) {
	if($plural == 'status')
		return 'status';
	if(substr($plural, -1) == 's')
		return substr($plural, 0, -1);
	return $plural;
}

$default = $form->get_value($field->get_name(), null);
if($default !== null) {
	$default = $default->get_key();
}


echo '<div class="nj-form-field nj-form-object-selector">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<select data-filterable data-type="'.singularize($field->get_table()).'" name="'.$field->get_name().'">';
if($default) {
	echo '<option value="'.html::specialchars($default).'">'.html::specialchars($default).'</option>';
}
echo '</select>';
echo '</label>';
echo '</div>';
