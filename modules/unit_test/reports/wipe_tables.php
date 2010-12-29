#!/usr/bin/php
<?php

require(dirname(__FILE__).'/../../../NinjaPDO.inc.php');

$db = NinjaPDO::db();
/**
 FIXME: SHOW TABLES is not SQL-standard. i have no idea how we might do this
 portably!
 */
$res = $db->query('SHOW TABLES');
if( ! $res ) {
    throw new Exception("SHOW TABLES query failed!");
}
while( ($row = $res->fetch(PDO::FETCH_NUM)) ) {
    $table = $row[0];
    #echo "Checking table $table...\n";
    if (strlen($table) >= 40) {
        echo "Dropping table $table...\n";
            if (!$db->query("drop table $table")) {
                $errinf = $db->errorInfo();
                echo "Failed to drop table $table:" . $errinf[2] . "\n";
            }
    }
}
?>
