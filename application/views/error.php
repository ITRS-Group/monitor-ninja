<?php defined('SYSPATH') OR die('No direct access allowed.');
if (isset($title, $message)) {

	$code = isset($code) ? $code : 400;
	request::send_header($code);

	echo '<div class="alert error">'
	echo '<h1>' . $title . '</h1>';
	echo $message;
	if (isset($messages)) {
		echo '<ul>';
		foreach ($messages as $message) {
			echo '<li>' . $message . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
} else {
	/* Legacy error view */
?>

<p>
	<div class='errorMessage'>
		<?php echo $error_message ?>
	</div>
</p>
<?php
}
