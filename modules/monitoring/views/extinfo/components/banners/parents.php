<?php

$parents = $object->get_parents();
$table = $object->get_table();
$name = $object->get_name();
$parent_count = count($parents);

if ($parent_count) {
	if ($parent_count <= 3) {
		echo '<li>';
		echo "<h3>Parents:</h3>";
			foreach ($parents as $parent) {
                echo "<p><a href=\"" . listview::querylink("[{$table}] name=\"{$parent}\"") . "\">{$parent}</a></p>";
			}
		echo '</li>';
	}
	elseif ($parent_count > 3) {
		echo '<li>';
		echo "<h3>Parents:</h3>";
        echo "<p><a href=\"" . listview::querylink("[{$table}] childs >=\"{$name}\"") . "\">{$parent_count} parents</a></p>";
		echo '</li>';
	}
}
