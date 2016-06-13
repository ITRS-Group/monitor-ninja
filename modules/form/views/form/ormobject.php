<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$tables = $field->get_tables();

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<div class="nj-form-field-autocomplete" data-autocomplete="' . implode(',', $tables) . '">';

$first = array_pop($tables);
$types = implode(', ', $tables);
$types .= ' or ' . $first;

if ($default) {

	echo '<input type="hidden" class="nj-form-option" value="' . $default->get_table() . '" name="'.$field->get_name().'[table]">';
	echo '<input placeholder="Enter name of '.$types.'..." autocomplete="off" type="text" class="nj-form-option" name="'.$field->get_name().'[value]" value="'.html::specialchars($default->get_readable_name()).'" />';
} else {
	echo '<input type="hidden" class="nj-form-option" value="' . $tables[0] . '" name="'.$field->get_name().'[table]">';
	echo '<input placeholder="Enter name of '.$types.'..." autocomplete="off" type="text" class="nj-form-option" name="'.$field->get_name().'[value]" value="'.html::specialchars($default).'" />';
}
//echo '<select class="nj-form-option" data-autocomplete="' . implode(',', $tables) . '"></select>';
echo '<ul class="nj-form-field-autocomplete-items"></ul>';
echo '</div>';
echo '</label>';
echo '</div>';
