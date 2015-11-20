<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>

<?php
$i = 0;
foreach( $tac_column_count as $count ) {
	for( $j=0; $j<$count; $j++ ) {
		$widget_list = isset($widgets[$i])?$widgets[$i]:array();
		$placeholder_id = 'widget-placeholder' . ($i>0 ? $i : '');
		echo '<div class="widget-place" id="'.$placeholder_id.'" style="width: '.number_format(100/$count, 2).'%;">';
		foreach ($widget_list as $idx => $widget) {
			echo $widget->render('index', true);
		}
		echo "</div>";
		$i++;
	}
	echo '<div style="clear: both;"></div>';
}
