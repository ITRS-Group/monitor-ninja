<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php

if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}

if (!empty($errors)) {
	echo _("Your settings couldn't be saved since the following errors were encountered:");
	echo "<br /><ul>";
	foreach ($errors as $e) {
		echo '<li>'.$e.'</li>';
	}
	echo '</ul>';
	?>
		<br />
		Check your values entered for the fields above and try again.<br />
		<form><input type="button" onclick="history.back(1)" value="<?php echo _('Back') ?>"></form>
	<?php
}
