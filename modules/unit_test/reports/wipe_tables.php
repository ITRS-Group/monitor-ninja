#!/usr/bin/php
<?php
if (!($db = mysql_connect('localhost'))) {
	echo "failed to connect: " . mysql_error() . "\n";
}
$ret = mysql_select_db('merlin', $db);
var_dump($ret);
$result = mysql_query('SHOW TABLES', $db);
var_dump($result);
while ($row = mysql_fetch_array($result)) {
	$table = $row[0];
	if (strlen($table) >= 45) {
		echo "Dropping table $table\n";
		if (!mysql_query("drop table $table"))
			echo "Failed to drop table $table:" . mysql_error() . "\n";
	}
}
?>
