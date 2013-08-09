<?php defined('SYSPATH') OR die('No direct access allowed.');

echo '<table style="table-layout: fixed;">';

echo '<thead>';
echo '<tr>';
foreach( $this->table as $col ) {
	echo '<th>';
	echo '<a href="'.htmlentities(listview::querylink($col['filter'])).'">';
	echo $col['name'];
	echo '</a>';
	echo '</th>';
}
echo '</tr>';
echo '</thead>';

echo '<tbody>';
echo '<tr>';
foreach( $this->table as $col ) {
	echo '<td>';
	echo '<table class="no_border">';
	$count = 0;
	foreach( $col['cells'] as $cell ) {
		if( !$cell['hide'] ) {
			$count++;
			echo '<tr>';
			echo '<td class="icon dark"><span class="icon-16 x16-'.$cell['icon'].'"></span></td>';
			echo '<td><a href="'.htmlentities(listview::querylink($cell['filter'])).'">';
			echo $cell['text'];
			echo '</a></td>';
			echo '</tr>';
		}
	}
	if( $count == 0 ) {
		echo '<tr>';
		echo '<td class="icon dark"><span class="icon-16 x16-'.$col['na_icon'].'"></span></td>';
		echo '<td>'._('N/A').'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</td>';
}
echo '</tr>';
echo '</tbody>';

echo '</table>';
