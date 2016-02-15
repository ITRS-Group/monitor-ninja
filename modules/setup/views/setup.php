<div class="center">
	<?php
		echo form::open(
			$linkprovider->get_url('setup', 'configure'),
			array(
				"class" => "renderable"
			)
		);
	?>
		<p>
			<span class="brand"><?php echo brand::get(); ?></span>
		</p>
		<h2>Create your op5 Monitor administrator account</h2>
		<p>
			You should save this username and password at a safe location so that the account is not lost.
		</p>
		<?php
			if ($message) {
				printf(
					"<p class=\"alert %s\">%s</p>",
					$message->get_typename(),
					$message->get_message()
				);
			}
		?>
		<hr>
		<fieldset>
			<label for="username">Username*</label>
			<input required type="text" value="administrator" id="username" name="username">
		</fieldset>
		<fieldset>
			<label for="password">Password*</label>
			<input required type="password" name="password">
		</fieldset>
		<fieldset>
			<label for="password-repeat">Confirm password*</label>
			<input required type="password" name="password-repeat">
		</fieldset>
		<hr>
		<fieldset type="buttons">
			<input type="submit" value="Create account">
		</fieldset>
	<?php
		echo form::close();
	?>
</div>
