<?php

$parents = $object->get_parents();

if (count($parents)) {
	echo '<li>';
	echo "<h3>Parents:</h3>";
	foreach ($parents as $parent) {
		echo '<p>'.html::anchor('extinfo/details/host/'.$parent, $parent)."</p>";
	}
	echo '</li>';
}

