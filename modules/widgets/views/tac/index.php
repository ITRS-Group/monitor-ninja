<?php defined('SYSPATH') OR die('No direct access allowed.');
$baseurl = url::base(true);
?>
<div id="response"></div>

<div style="display: none;">
<?php
echo form::open(url::site() . 'tac/on_delete_dashboard', array('id' => 'dashboard-delete-form'), array('dashboard_id' => $dashboard->get_id()));
echo '<h2>Delete dashboard</h2>';
echo '<hr>';
echo '<p>Are you sure you want to delete this dashboard?</p>';
echo '<p>Deleting a dashboard can\'t be undone.</p>';
echo form::submit(array(), 'Yes');
echo form::input(array('type' => 'button', 'class' => 'dashboard-form-cancel'), 'Cancel');
echo form::close();
?>
</div>

<div style="display: none;">
<?php
echo form::open(url::site() . 'tac/on_rename_dashboard', array('id' => 'dashboard-rename-form'), array('dashboard_id' => $dashboard->get_id()));
echo '<h2>Rename dashboard</h2>';
echo '<hr>';
echo form::input('name', $dashboard->get_name());
echo '<hr>';
echo form::submit(array(), 'Yes');
echo form::input(array('type' => 'button', 'class' => 'dashboard-form-cancel'), 'Cancel');
echo form::close();
?>
</div>

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
