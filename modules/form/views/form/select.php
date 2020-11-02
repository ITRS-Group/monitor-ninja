<?php
defined('SYSPATH') OR die('No direct access allowed.');
$element_id = 'element_id_'.uniqid();
$required = $form->is_field_required($field);
?>

<div class="nj-form-field">
	<label>
		<div class="nj-form-label">
			<?php echo html::specialchars($field->get_pretty_name()); ?>
		</div>
		<select <?php
			echo ($required) ? 'required' : '';
		?> class="nj-form-option" id="<?php
			echo $element_id;
		?>" name="<?php
			echo html::specialchars($field->get_name());
		?>">
<?php
foreach ($field->get_options() as $option_value => $label) {
	View::factory('form/option', array(
		"value" => $option_value,
		"label" => $label,
		"selected" => ($value === $option_value)
	))->render(true);
}
?>
		</select>
	</label>
</div>

