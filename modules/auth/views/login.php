<div class="center">
	<?php
		echo form::open(
			$login_page,
			array(
				'id' => 'login_form',
				'class' => 'renderable-login'
			)
		);
	?>
	<p>
		<span class="brand"><?php echo brand::get(); ?></span>
	</p>
	<?php
		if ($message) {
			printf('<p class="alert %s">%s</p>', $message->get_typename(), $message->get_message());
		}
	?>

	<h1>Log in to ITRS OP5 Monitor</h1>
	
	<fieldset>
		<label for="username">Username</label>
		<input type="text" name="username" id="username" autofocus>
	</fieldset>


	<fieldset>
		<label for="password">Password</label>
		<input type="password" name="password" id="password" >
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


	<fieldset type="buttons">
		<input type="submit" value="Log in">
	</fieldset>

	<p class="rights">&#169;2019 ITRS GROUP LTD, ALL RIGHTS RESERVED</p>

	<?php
		echo form::close()
	?>

	<script>
		$(function(){
			// Resize the ITRS OP5 Monitor logo just for this page.
			$(".brand-icon").css({
				"height": "99px",
				"width": "203px"
			});

			// Change the background-color just for this page.
			$("#content").css("background", "rgb(245,245,245)"); 
		});
	</script>
</div>
