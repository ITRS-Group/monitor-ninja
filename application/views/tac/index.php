<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>

<?php
function render_placeholder( $widgets, $placeholder, $style='' ) {
	$widget_list = isset($widgets[$placeholder])?$widgets[$placeholder]:array();
	$placeholder_id = 'widget-placeholder';
	if( $placeholder > 0 )
		$placeholder_id .= $placeholder;
	echo '<div class="widget-place" id="'.$placeholder_id.'" style="'.htmlentities($style).'">';
	foreach ($widget_list as $idx => $widget) {
		echo $widget;
	}
	echo "</div>";
}

$i = 0;
foreach( $tac_column_count as $count ) {
	for( $j=0; $j<$count; $j++ ) {
		render_placeholder($widgets, $i, 'width: '.(100/$count).'%;' );
		$i++;
	}
	echo '<div style="clear: both;"></div>';
}