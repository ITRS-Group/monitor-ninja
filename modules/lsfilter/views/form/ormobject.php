<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$tables = $field->get_tables();

echo '<div class="nj-form-field">';
echo '<label>';
echo '<div class="nj-form-label">' . html::specialchars($field->get_pretty_name()) . '</div>';
echo '<div class="nj-form-field-autocomplete" data-autocomplete="' . implode(',', $tables) . '">';

$first = $tables[count($tables) - 1];
$types = $first;
if(count($tables) > 1) {
	$types = implode(', ', $tables);
	$types .= ' or ' . $first;
}

if ($default instanceof Object_Model) {
	echo '<input class="nj-form-option" type="hidden" value="'.html::specialchars($default->get_table()). '" name="'.html::specialchars($field->get_name()).'[table]">';
	echo '<input placeholder="Enter name of '.$types.'" data-njform-table="'.html::specialchars($default->get_table()).'" autocomplete="off" type="text" class="nj-form-field-autocomplete-input nj-form-option" name="'.html::specialchars($field->get_name()).'[value]" value="'.html::specialchars($default->get_key()).'" />';
} else {
	echo '<input class="nj-form-option" type="hidden" class="nj-form-option" value="'.html::specialchars($tables[0]).'" name="'.html::specialchars($field->get_name()).'[table]">';
	echo '<input class="nj-form-field-autocomplete-input nj-form-option" data-njform-table="'.html::specialchars($tables[0]).'" placeholder="Enter name of '.$types.'" autocomplete="off" type="text" name="'.html::specialchars($field->get_name()).'[value]" value="'.html::specialchars($default).'" />';
}
echo '<input class="nj-form-field-autocomplete-shadow" autocomplete="off" type="text" class="nj-form-option" />';
echo '<span class="nj-form-field-autocomplete-dropper">▼</span>';
echo '<ul class="nj-form-field-autocomplete-items"></ul>';
echo '</div>';
echo '</label>';
echo '</div>';
