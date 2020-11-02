<?php

$groups = $object->get_groups();
$group_table = ($object->get_table() === 'services') ? 'servicegroups' : 'hostgroups';

if (count($groups)) {
	echo '<li>';
	echo "<h3>Member of:</h3>";
	foreach ($groups as $group) {
		echo "<p><a href='" . listview::querylink('[' . $group_table . '] name="' . $group . '"') . "'>$group</a></p>";
	}
	echo '</li>';
}

