<?php defined('SYSPATH') OR die('No direct access allowed.');

	$authorized = false;
	if (Auth::instance()->logged_in()) {
		$ninja_menu_setting = Ninja_setting_Model::fetch_page_setting('ninja_menu_state', '/');

		$auth = Nagios_auth_Model::instance();
		if ($auth->view_hosts_root) {
			$authorized = true;
		}
	}

?>
<!DOCTYPE html>
<html>
	
	<?php
		include_once(__DIR__.'/dojo/head.php');
	?>

	<body>

		<div class="container">
			
			<div class="logo">
				<?php echo html::image('application/views/themes/default/icons/op5.gif', array('style' => 'float: left; margin-left: 15px;')); ?>
				<!--<img src="icons/op5.gif" style="float: left; margin-left: 15px" />-->
			</div>

			<?php

				include_once(__DIR__.'/dojo/header.php');
			?>

			<section class="navigation" id="navigation">
				<div class="menu" id="main-menu">

				<?php
					include_once(__DIR__.'/dojo/menu.php');
				?>

				</div>
				<div class="slider" id="slider" title="Collapse Navigation">
					<div class="slide-button">
						::
					</div>
				</div>

			</section>

			<section class="content" id="content">
				
				<div id="content"<?php echo (isset($nacoma) && $nacoma == true) ? ' class="ie7conf"' : ''?>>
					<?php if (isset($content)) { echo $content; } else { url::redirect(Kohana::config('routes.logged_in_default')); }?>
				</div>

		</div>
		<?php
			echo html::script('application/media/js/dojo.js');
		?>

	</body>
</html>
