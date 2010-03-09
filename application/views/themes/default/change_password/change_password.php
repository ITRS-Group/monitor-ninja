<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w32">
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
<br />
<?php
	echo form::open('change_password/change_password');
	echo form::open_fieldset();
	echo form::legend('Change password');

	$fields = array(
		'new_password' => form::password('new_password'),
		'confirm_password' => form::password('confirm_password'),
	);?>
	<table cellpadding="0" cellspacing="0">
	<?php
		foreach ($fields as $label => $field) {
			echo '
			<tr>
				<td><strong>'.form::label($label).'</strong>: </td>
				<td>'.$field.'</td>
			</tr>';
		} ?>
	</table>
	<?php
	echo form::submit('change_password', 'Change password');
	echo form::close_fieldset();
	echo form::close();
?>
</div>
