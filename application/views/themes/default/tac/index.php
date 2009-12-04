<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<div class="widget-place" id="widget-placeholder">
<?php
	if (!empty($widgets)) {
		$a = 0;
		foreach ($widgets as $widget) {
			echo $widget;
			$a++;
			if ($a == round(count($widgets)/3))
				echo '</div><div class="widget-place" id="widget-placeholder1">';
			elseif ($a == round(count($widgets)/3*2))
				echo '</div><div class="widget-place" id="widget-placeholder2">';
		}
	}
?>
</div>
<div class="widget-place w98" id="widget-placeholder3"></div>