<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div class="widget-place">
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>
</div>