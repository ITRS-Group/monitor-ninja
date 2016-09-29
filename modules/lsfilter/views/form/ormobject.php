<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$required = $form->is_field_required($field);
$tables = $field->get_tables();
$types = html::get_delimited_string($tables, false, 'or');

if ($default instanceof Object_Model) {
	$table = $default->get_table();
	$default = $default->get_key();
} else {
	$table = $tables[0];
}

?>

<div class="nj-form-field">
<label>
	<div class="nj-form-label"><?php
		echo html::specialchars($field->get_pretty_name());
	?></div>
	<div class="nj-form-field-autocomplete" data-autocomplete="<?php
		echo implode(',', $tables);
	?>">
		<input class="nj-form-option" type="hidden" value="<?php
			echo html::specialchars($table);
		?>" name="<?php
			echo html::specialchars($field->get_name());
		?>[table]">
		<input <?php
			echo ($required) ? 'required' : '';
		?> class="nj-form-field-autocomplete-input nj-form-option" data-njform-table="<?php
			echo html::specialchars($table);
		?>" placeholder="Select an object from <?php
			echo $types;
		?>" autocomplete="off" type="text" name="<?php
			echo html::specialchars($field->get_name());
		?>[value]" value="<?php
			echo html::specialchars($default);
		?>" />
		<span class="nj-form-field-autocomplete-dropper">▼</span>
		<ul class="nj-form-field-autocomplete-items"></ul>
	</div>
</label>
</div>
