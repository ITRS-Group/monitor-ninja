<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<?php
if (!empty($widget_order)) {
	foreach ($widget_order as $placeholder => $widget_list) {
		if ($placeholder == 'widget-placeholder3') {
		?><div class="widget-place w98" id="<?php echo $placeholder ?>"><?php
		} else {
		?><div class="widget-place" id="<?php echo $placeholder ?>"><?php
		}
		foreach ($widget_list as $widget_name) {
			foreach ($widgets as $widget) {
				if (preg_match('/id="'.$widget_name.'"/', $widget)) {
					echo $widget;
				}
			}
		}
		echo "</div>";
	}
} else { ?>
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
	} ?>
	</div>
	<div class="widget-place w98" id="widget-placeholder3"></div>
<?php }