<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (isset($error_msg)) echo $error_msg;
	echo $status_msg;

	echo form::open('change_password/change_password');

	$fields = array(
		'current_password' => form::password(array('name' => 'current_password', 'autocomplete' => 'off')),
		'new_password' => form::password(array('name' => 'new_password', 'autocomplete' => 'off')),
		'confirm_password' => form::password(array('name' => 'confirm_password', 'autocomplete' => 'off'))
	);

	$labels = array(
		'current_password' => _('Current password'),
		'new_password' => _('New password'),
		'confirm_password' => _('Repeat new password')
	);

	?>
	<table style="margin: 16px; border: none;" class="white-table">
	<?php
		foreach ($fields as $label => $field) {
			echo '
			<tr>
				<td style="border: none; padding-right: 10px; width: 100px">'.form::label($label, $labels[$label]).'</td>
				<td style="border: none;">'.$field.'</td>
			</tr>';
		}
	?>
		<tr>
			<td style="border: none;"></td>
			<td style="border: none;"><?php echo form::submit('change_password', _('Change password')); ?></td>
		</tr>
	</table>

	<?php
	echo form::close();
?>