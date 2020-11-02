<?php defined('SYSPATH') OR die('No direct access allowed.');

/* @var $object NaemonMonitoredObject_Model */
$commands = $object->list_custom_commands();

if(!$commands) {
	return;
}
?>

<li>
<h2>Custom commands</h2>
<ul>
<?php
foreach($commands as $cmd => $cmdinfo) {
	$linktext = ucwords(strtolower(str_replace('_', ' ', $cmd)));
	$cmd = html::specialchars($cmd);
	$key = html::specialchars($object->get_key());
	$table = html::specialchars($object->get_table());
	$link = "<a href='#' title='$cmd' data-table='$table' data-key='$key' data-command='$cmd'>" . $linktext . "</a>";
	echo "<li>" . $link . "</li>";
}
?>
</ul>
<li>

