<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div id="response"></div>

<?php
$i = 0;
foreach( $tac_column_count as $row_number => $count ) {
	if (count($tac_column_count) - 1 === $row_number) {
		echo '<div class="widget-row" style="padding-bottom: 32px;">';
	} else {
		echo '<div class="widget-row">';
	}
	for( $j=0; $j<$count; $j++ ) {
		$widget_list = isset($widgets[$i])?$widgets[$i]:array();
		$placeholder_id = 'widget-placeholder' . $i;
		$width = (100/$count);
		echo '<div class="widget-place" style="-webkit-flex-basis: '.$width.'%;-ms-flex-basis: '.$width.'%;flex-basis: '.$width.'%; max-width: '.$width.'%;" id="'.$placeholder_id.'">';
		foreach ($widget_list as $idx => $widget) {
			echo $widget->render('index', true);
		}
		echo "</div>";
		$i++;
	}
	echo '</div>';
}
?>
