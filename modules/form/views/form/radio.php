<?php defined('SYSPATH') OR die('No direct access allowed.');
/* NOTE: Order of input and nj-form-label matters to CSS! */
$element_id = 'element_id_'.uniqid();
?>
<div class="nj-form-field-radio">
	<label>
		<input id="<?php echo $element_id; ?>" name="<?php echo $name; ?>" type="radio" value="<?php echo html::specialchars($value);?>" <?php echo ($selected) ? ' checked' : '' ?>>
		<div class="nj-form-label">
			<?php echo html::specialchars($label); ?>
		</div>
	</label>
</div>

