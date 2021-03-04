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
        $auth = op5auth::instance();
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

	<?php
		// Add Keycloak link
		$keycloak_modules = $auth->get_modules_by_driver('Keycloak');
		if (!empty($keycloak_modules) && is_array($keycloak_modules) && count($keycloak_modules) == 1) {
			$uri = '/monitor/index.php/keycloak';
			// If the user tried to access the a uri without begin logged in we pass
			// that uri along so we can redirect back to that page after authenticating.
			// The uri is caught and handled in keycloak.php.
			if (array_key_exists('uri', $_GET)) {
				$uri = $uri . "?uri=" . $_GET['uri'];
			}
			echo '<fieldset>';
			foreach ($keycloak_modules as $module) {
				$module_name = $module->get_modulename();
				echo "<a href='" . $uri . "'>Sign in with $module_name</a>\n";
			}
			echo '</fieldset>';
		}
	?>

	<p class="rights">&#169;2019 ITRS GROUP LTD, ALL RIGHTS RESERVED</p>

	<?php
		echo form::close()
	?>

	<script>
		$(document).ready(function(){
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
