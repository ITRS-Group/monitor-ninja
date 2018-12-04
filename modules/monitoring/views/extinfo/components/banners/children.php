<?php

$children = $object->get_childs();

if (count($children)) {
	echo '<li>';
	echo "<h3>Children:</h3>";
	foreach ($children as $child) {
		echo '<p>'.html::anchor('extinfo/details/host/'.$child, $child)."</p>";
	}
	echo '</li>';
}

