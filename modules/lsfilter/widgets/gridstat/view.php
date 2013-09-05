<?php defined('SYSPATH') OR die('No direct access allowed.');

echo '<table class="w-table">';
echo '<tbody>';
foreach($this->data as $row) {
	echo '<tr>';
	echo '<td class="icon dark">';
	echo '<span class="icon-16 x16-'.$row['icon'].'"></span>';
	echo '</td>';
	echo '<td><strong>'.htmlentities(strtoupper($row['title'])).'</strong><br />';
	echo implode(' + ', array_map(function($field) {
		return '<a href="' . htmlentities(listview::querylink($field['filter'])) . '">' . sprintf($field['text'], $field['count']) . '</a>';
	}, $row['fields']));
	echo '</td>';
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';