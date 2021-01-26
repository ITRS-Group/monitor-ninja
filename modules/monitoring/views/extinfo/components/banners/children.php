<?php

$childs = $object->get_childs();
$table = $object->get_table();
$name = $object->get_name();
$child_count = count($childs);

if ($child_count) {
	if ($child_count <= 3) {
		echo '<li>';
		echo "<h3>Children:</h3>";
			foreach ($childs as $child) {
                echo "<p><a href=\"" . listview::querylink("[{$table}] name=\"{$child}\"") . "\">{$child}</a></p>";
			}
		echo '</li>';
	}
	elseif ($child_count > 3) {
		echo '<li>';
		echo "<h3>Children:</h3>";
        echo "<p><a href=\"" . listview::querylink("[{$table}] parents >=\"{$name}\"") . "\">{$child_count} parents</a></p>";
		echo '</li>';
	}
}
