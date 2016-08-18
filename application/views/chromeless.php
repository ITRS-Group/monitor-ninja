<?php
defined('SYSPATH') OR die('No direct access allowed.');
$keycommands_active = false;
$disable_refresh = true;
?><!DOCTYPE html>
<html>
	<?php
		require __DIR__.'/template_head.php';
	?>
	<body>

		<div class="container">
			<div class="content" tabindex="0" id="content">

<?php
if($content instanceof View) {
	$content->render(true);
} else {
	echo $content;
}
?>

			</div>
		</div>
	</body>
</html>
