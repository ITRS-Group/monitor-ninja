<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<option value="<?php echo html::specialchars($value); ?>" <?php echo ($selected) ? ' selected="selected"' : ''; ?>>
	<?php echo html::specialchars($label); ?>
</option>
