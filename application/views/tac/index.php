<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div id="response"></div>

<?php
function render_placeholder( $widgets, $placeholder, $style='' ) {
	$widget_list = isset($widgets[$placeholder])?$widgets[$placeholder]:array();
	echo '<div class="widget-place" id="widget-placeholder'.$placeholder.'" style="'.htmlentities($style).'">';
	foreach ($widget_list as $idx => $widget) {
		echo $widget;
	}
	echo "</div>";
}

render_placeholder($widgets, 0, 'width: 33%;' );
render_placeholder($widgets, 1, 'width: 33%;' );
render_placeholder($widgets, 2, 'width: 33%;' );
echo '<div style="clear: both;"></div>';
render_placeholder($widgets, 6, 'width: 99%;' );
echo '<div style="clear: both;"></div>';
render_placeholder($widgets, 3, 'width: 49.5%;' );
render_placeholder($widgets, 4, 'width: 49.5%;' );
echo '<div style="clear: both;"></div>';
render_placeholder($widgets, 5, 'width: 99%;' );
echo '<div style="clear: both;"></div>';