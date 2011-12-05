<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="widget left w98">
<?php
if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
<?php
	echo form::open('change_password/change_password');
	echo '<h2>'.$this->translate->_('Change password').'</h2>';

	$fields = array(
		'current_password' => form::password(array('name' => 'current_password', 'autocomplete' => 'off')),
		'new_password' => form::password(array('name' => 'new_password', 'autocomplete' => 'off')),
		'confirm_password' => form::password(array('name' => 'confirm_password', 'autocomplete' => 'off'))
	);?>
	<table class="white-table">
	<?php
		foreach ($fields as $label => $field) {
			echo '
			<tr>
				<td style="padding-right: 10px; width: 100px">'.form::label($label).'</td>
				<td>'.$field.'</td>
			</tr>';
		} ?>
		<tr>
		  <td></td>
		  <td><?php echo form::submit('change_password', $this->translate->_('Change password')); ?></td>
		</tr>
	</table>
	<?php


	echo form::close();
?>
</div>
