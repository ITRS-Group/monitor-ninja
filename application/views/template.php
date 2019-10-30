<?php defined('SYSPATH') OR die('No direct access allowed.');

	$authorized = false;
	if (Auth::instance()->logged_in()) {
		$auth = op5auth::instance();
		if ($auth->authorized_for('host_view_all')) {
			$authorized = true;
		}
	}

	if (!isset($keycommands_disabled) || $keycommands_disabled !== true) {
		$keycommands_active = (int)(bool)config::get('keycommands.activated');
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
				<div class="content <?php echo $content_class; ?>" tabindex="0" id="content">

					<?php

						if (isset($content)) {
							if($content instanceof View) {
								$content->render(true);
							} else {
								echo $content;
							}
						} else {
							$services_down = 'services LMD and Naemon are not running';

							// Check LMD.
							exec('service lmd status', $output, $return_var_lmd);
							// Check Livestatus.
							exec('service naemon status', $output, $return_var_le);

							if($return_var_lmd !== 0 && $return_var_le === 0){
								$services_down = 'service LMD is not running';
							}
							echo '<div class=" info-notice info-notice-error info-notice-service-error">';
								echo 'The OP5 Monitor '.$services_down.', please contact your administrator.';
							echo '</div>';
						}
					?>
				</div>

			<?php

				require __DIR__ . '/template_notifications.php';
				if (isset($saved_searches) && !empty($saved_searches)) {
					echo $saved_searches;
				}

			?>

		</div>
		<?php

			if (isset($context_menu)) {
				if($context_menu instanceof View) {
					$context_menu->render(true);
				} else {
					echo $context_menu;
				}
			}

		?>

	</body>
</html>
