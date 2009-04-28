<?php defined('SYSPATH') OR die('No direct access allowed.');

if (isset($error_msg)) echo $error_msg; ?>

<?php echo form::open('default/do_login'); ?>
<table id="login-table">
	<tr>
		<td colspan="2"><h3><?php echo $this->translate->_('Login') ?></h3></td>
	</tr>
	<tr>
		<td><?php echo $username ?></td>
		<td><cite><em></em><?php echo form::input('username','','class="text"') ?><em></em></cite></td>
	</tr>
	<tr>
		<td><?php echo $password ?></td>
		<td><cite><em></em><?php echo form::password('password','','class="text"') ?><em></em></cite></td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	<td>
		<?php
			echo csrf::form_field();
			echo form::submit('login', $login_btn_txt, 'class="bn"');
		?>
	</td>
	</tr>
</table>
<?php echo form::close() ?>
