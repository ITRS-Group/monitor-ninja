<div class="center">
	<?php
		echo form::open(
			$login_page,
			array(
				'id' => 'login_form',
				'class' => 'renderable'
			)
		);
	?>
	<p>
		<span class="brand"><?php echo brand::get(); ?></span>
	</p>
	<h2>Login to op5 Monitor</h2>

	<?php
		if ($message) {
			printf('<p class="alert %s">%s</p>', $message->get_typename(), $message->get_message());
		}
	?>

	<hr>

	<fieldset>
		<label for="username">Username</label>
		<input type="text" name="username" id="username" placeholder="Username" autofocus>
	</fieldset>

	<fieldset>
		<label for="password">Password</label>
		<input type="password" name="password" id="password" placeholder="Password">
	</fieldset>

	<?php

		$auth = Auth::instance();
		$default_auth = $auth->get_default_auth();
		if (!empty($auth_modules) && is_array($auth_modules) && count($auth_modules) > 1) {
			echo '<fieldset>';
			echo form::label('auth_method', _('Login method'));
			echo form::dropdown(array(
				'name' => 'auth_method'
			), array_combine($auth_modules, $auth_modules), $default_auth);
			echo '</fieldset>';
		}
	?>

	<hr>

	<fieldset type="buttons">
		<input type="submit" value="Login">
	</fieldset>

	<?php
		echo form::close()
	?>
</div>
