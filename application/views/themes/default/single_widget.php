<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>
