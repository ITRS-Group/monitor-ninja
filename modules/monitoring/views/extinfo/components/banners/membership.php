<?php

$groups = $object->get_groups();
$group_table = ($object->get_table() === 'services') ? 'servicegroups' : 'hostgroups';
$name = $object->get_name();
$group_count = count($groups);

if ($group_count) {
	if ($group_count <= 3) {
		echo '<li>';
		echo "<h3>Member of:</h3>";
		foreach ($groups as $group) {
			echo "<p><a href=\"" . listview::querylink("[{$group_table}] name=\"{$group}\"") . "\">{$group}</a></p>";
		}
		echo '</li>';
	}
	elseif ($group_count > 3) {
		echo '<li>';
		echo "<h3>Member of:</h3>";
		echo "<p><a href=\"" . listview::querylink("[{$group_table}] members >=\"{$name}\"") . "\">{$group_count} groups</a></p>";
		echo '</li>';
	}
}
