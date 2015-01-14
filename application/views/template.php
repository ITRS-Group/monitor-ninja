<?php defined('SYSPATH') OR die('No direct access allowed.');

	$authorized = false;
	if (Auth::instance()->logged_in()) {
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

?><!DOCTYPE html>
<html>
	<?php
		require __DIR__.'/template_head.php';
	?>
	<body>

		<div class="container">
			<?php
			if (!empty($print_notifications)) {
				?> <div class="print-notification-bar"> <?php
				foreach ($print_notifications as $print_notification) {
					echo $print_notification;
				}
			?> </div> <?php
			}

			if(!isset($hide_header)) {
				require __DIR__.'/template_header.php';
			} ?>
			<div class="content" tabindex="0" id="content">

					<?php

						if (isset($content)) {
							if($content instanceof View) {
								$content->render(true);
							} else {
								echo $content;
							}
						} else {
							echo 'Page does not have any content';
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
			if (isset($context_menu)) {
				if($context_menu instanceof View) {
					$context_menu->render(true);
				} else {
					echo $context_menu;
				}
			}
		?>

		<?php
			if ( isset( $global_notifications ) && is_array( $global_notifications ) && count( $global_notifications ) >= 1 ) {
				echo "<script>\n";
				foreach ( $global_notifications as $note ) {
					printf("Notify.message('%s', \n\t{type: 'error', nag: true});\n", addcslashes($note[0], "'"));
				}
				echo "</script>";
			}
		?>

	</body>
</html>
