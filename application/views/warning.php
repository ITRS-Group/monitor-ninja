<?php defined('SYSPATH') OR die('No direct access allowed.');
$code = isset($code) ? $code : 200;
request::send_header($code);

echo '<div class="alert warning">';
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
