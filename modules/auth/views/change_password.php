<?php defined('SYSPATH') OR die('No direct access allowed.');

	if ($message) {
		printf("<div class=\"alert %s\">%s</div>", $message->get_typename(), $message->get_message());
	}

	echo form::open();

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

			$row = '<tr>
				<td style="border: none; padding-right: 10px; width: 100px">%s</td>
				<td style="border: none;">%s</td>
			</tr>';

			foreach ($fields as $label => $field) {
				printf($row, form::label($label, $labels[$label]), $field);
			}

		?>
		<tr>
			<td style="border: none;"></td>
			<td style="border: none;">
				<?php echo form::submit('change_password', _('Change password')); ?>
			</td>
		</tr>
	</table>

	<?php

	echo form::close();

?>
