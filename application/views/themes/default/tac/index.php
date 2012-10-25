<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>
<?php
foreach ($widgets as $placeholder => $widget_list) {
	if ($placeholder == 'widget-placeholder5') {
		?><div class="clear"></div><div class="widget-place w98" id="<?php echo $placeholder ?>"><?php
	} elseif ($placeholder == 'widget-placeholder3' || $placeholder == 'widget-placeholder4') {
		if ($placeholder == 'widget-placeholder3') {
			?><div class="clear"></div><?php
		}
		?><div class="widget-place" id="<?php echo $placeholder ?>"><?php
	} else {
		?><div class="widget-place" id="<?php echo $placeholder ?>"><?php
	}
	foreach ($widget_list as $idx => $widget) {
		echo $widget;
	}
	echo "</div>";
}
