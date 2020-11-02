<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$required = $form->is_field_required($field);
?>

<div class="nj-form-field nj-form-field-listview-query">
<label>
	<div class="nj-form-label"><?php
		echo html::specialchars($field->get_pretty_name());
	?></div>
	<textarea <?php
		echo ($required) ? 'required' : '';
	?> data-table="hosts" class="nj-form-option" name="<?php
		echo html::specialchars($field->get_name());
	?>"><?php
		echo html::specialchars($default);
	?></textarea>
</label>
</div>

