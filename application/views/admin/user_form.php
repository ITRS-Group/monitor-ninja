<?php defined('SYSPATH') OR die('No direct access allowed.');

if (isset($error_msg)) echo $error_msg; ?>
<?php echo $status_msg ?>
<br />
<?php
	echo form::open('admin/user_validate');
	echo form::open_fieldset();
	echo form::legend($form_title);
	#echo form::label('adduser', $form_title).':<br/>';

	$fields = array(
		$realname => form::input('realname', isset($user_details->realname) ? $user_details->realname : ''),
		$email => form::input('email', isset($user_details->email) ? $user_details->email : ''),
		$username => isset($user_details->username) ? $user_details->username : form::input('username'),
		$password => form::password('password'),
		$confirm_password => form::password('password_confirm'),
	);?>
	<table cellpadding="0" cellspacing="0">
	<?php
		foreach ($fields as $label => $field) {
			if (isset($user_details) && stristr($label, 'password')) {
				continue;
			}
			echo '
			<tr>
				<td><strong>'.form::label($label).'</strong>: </td>
				<td>'.$field.'</td>
			</tr>';
		} ?>
	</table>
	<?php
	if ($user_details->id) {
		echo form::hidden('user_id', $user_details->id);
	}
	echo csrf::form_field();
	echo form::submit('add_user', $submit_btn_txt);
	echo form::close_fieldset();
	echo form::close();
?>
