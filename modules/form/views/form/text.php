<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$required = $form->is_field_required($field);
$element_id = 'element_id_'.uniqid();

?>

<div class="nj-form-field">
	<label>
		<div class="nj-form-label">
			<?php echo html::specialchars($field->get_pretty_name()); ?>
		</div>
		<input type="text" class="nj-form-option" <?php
			echo ($required) ? 'required' : '';
		?> id="<?php
			echo $element_id;
		?>" name="<?php
			echo html::specialchars($field->get_name());
		?>" value="<?php
			echo html::specialchars($default);
		?>" placeholder="<?php
			echo html::specialchars($field->get_placeholder());
		?>">
	</label>
</div>
