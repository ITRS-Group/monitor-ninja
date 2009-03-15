<?php defined('SYSPATH') OR die('No direct access allowed.');

if (isset($error_msg)) echo $error_msg; ?>
<br />
<?php
	echo form::open('default/do_login');
	echo form::label('dologin', $form_title).':<br/>';
	echo $username.": ".form::input('username').'<br/>';
	echo $password.": &nbsp;".form::password('password').'<br/>';
	echo csrf::form_field();
	echo form::submit('login', $login_btn_txt);
	echo form::close();
?>
