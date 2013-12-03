<?php defined('SYSPATH') OR die('No direct access allowed.');

	$authorized = false;
	if (Auth::instance()->logged_in()) {
		$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');

		$auth = op5auth::instance();
		if ($auth->authorized_for('host_view_all')) {
			$authorized = true;
		}
	}

	if (!isset($keycommands_disabled) || $keycommands_disabled !== true) {
		$keycommands_active = (int)(bool)config::get('keycommands.activated', '*');
	} else {
		$keycommands_active = 0;
	}

	if (isset($this) && isset($this->template->js_header))
		$this->template->js_header->js = array_unique($this->xtra_js);

?>
<!DOCTYPE html>
<html>

	<?php
		require __DIR__.'/template_head.php';
	?>

	<body>

		<div class="container">
			<?php if(!isset($hide_header)) {
				require __DIR__.'/template_header.php';
			} ?>
			<div class="content" id="content">

					<?php

						if (isset($content)) {
							echo $content;
						} else {
							return url::redirect(Kohana::config('routes.logged_in_default'));
						}

						require __DIR__ . '/template_notifications.php';

					?>

			</div>

			<?php

				if (isset($saved_searches) && !empty($saved_searches)) {
					echo $saved_searches;
				}

			?>

		</div>
		<?php
			if(!isset($no_dojo)) {
				echo html::script('application/media/js/dojo.js');
			}
			if (isset($context_menu))
				echo $context_menu;
		?>


	</body>
</html>
