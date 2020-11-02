<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
/* NOTE: Order of input a nj-form-label matters */

?>

<div class="nj-form-field nj-form-field-checkbox">
<label>
	<input type="checkbox" class="nj-form-option" name="<?php
		echo html::specialchars($field->get_name());
	?>" <?php
		echo ($default ? 'checked' : '');
	?> />
	<div class="nj-form-label"><?php
		echo html::specialchars($field->get_pretty_name());
	?></div>
</label>
</div>
