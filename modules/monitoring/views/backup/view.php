<?php defined('SYSPATH') OR die('No direct access allowed.');

if (isset($error)) {
	echo '<div class="alert error">' . $error . '</div>';
	return;
}

?>
<p>
	<?php echo _("This backup contains the following files:"); ?>
</p>
<table class="padd-table" class="white-table">
	<?php foreach ($files as $file): ?>
	<tr>
	  <td><?php echo $file; ?></td>
	</tr>
	<?php endforeach; ?>
</table>
