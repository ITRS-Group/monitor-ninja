<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div>
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
<?php
	echo form::open('change_password/change_password');
	echo '<h2>'._('Change password').'</h2>';

	$fields = array(
		'current_password' => form::password(array('name' => 'current_password', 'autocomplete' => 'off')),
		'new_password' => form::password(array('name' => 'new_password', 'autocomplete' => 'off')),
		'confirm_password' => form::password(array('name' => 'confirm_password', 'autocomplete' => 'off'))
	);?>
	<table style="border: none;" class="white-table">
	<?php
		foreach ($fields as $label => $field) {
			echo '
			<tr>
				<td style="border: none; padding-right: 10px; width: 100px">'.form::label($label).'</td>
				<td style="border: none;">'.$field.'</td>
			</tr>';
		} ?>
		<tr>
		  <td style="border: none;"></td>
		  <td style="border: none;"><?php echo form::submit('change_password', _('Change password')); ?></td>
		</tr>
	</table>
	<?php


	echo form::close();
?>
</div>
