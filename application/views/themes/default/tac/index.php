<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div class="widget-place" id="widget-placeholder">
<?php
	if (!empty($widgets)) {
		foreach ($widgets as $widget) {
			echo $widget;
		}
	}
?>
</div>