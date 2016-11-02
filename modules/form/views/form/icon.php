<?php defined("SYSPATH") OR die("No direct access allowed.");
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$required = $form->is_field_required($field);
?>

<div class="nj-form-field">
	<div class="nj-form-label">
		<?php echo html::specialchars($field->get_pretty_name()); ?>
	</div>
	<div class="nj-form-icon-container">
		<input type="hidden" name="<?php echo html::specialchars($field->get_name()); ?>" />
		<?php foreach($field->get_icons() as $icon_name) { ?>
		<div class="nj-form-icon" data-icon="<?php echo $icon_name; ?>">
			<span class="icon-16 x16-<?php echo $icon_name; ?>"></span>
		</div>
		<?php } ?>
	</div>
</div>
