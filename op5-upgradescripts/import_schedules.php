<?php

$gui_db_opt['type'] = 'mysql'; # mysql is the only one supported for now.
$gui_db_opt['host'] = 'localhost';
$gui_db_opt['user'] = 'monitor';
$gui_db_opt['passwd'] = 'monitor';
$gui_db_opt['database'] = 'monitor_gui';
$gui_db_opt['persistent'] = true;	# set to false if you're using php-cgi
$gui_db_opt['new_db_version'] = '5.0';
$gui_dbh = false; # database resource
$DEBUG = true;

# connects to and selects database. false on error, true on success
function gui_db_connect() {
	global $gui_dbh;
	global $gui_db_opt;

	if($gui_db_opt['type'] !== 'mysql') {
		die("Only mysql is supported as of yet.<br />\n");
	}

	if(!empty($gui_db_opt['persistent'])) {
		# use persistent connections
		$gui_dbh = mysql_pconnect($gui_db_opt['host'],
								  $gui_db_opt['user'],
								  $gui_db_opt['passwd']);
	} else {
		$gui_dbh = mysql_connect($gui_db_opt['host'],
								  $gui_db_opt['user'],
								  $gui_db_opt['passwd']);
	}

	if($gui_dbh === false) return(false);

	return(mysql_select_db($gui_db_opt['database']));
}

# fetch a single row to associative array
function sql_fetch_array($resource) {
	return(mysql_fetch_array($resource, MYSQL_ASSOC));
}

function sql_escape_string($string)
{
	return mysql_real_escape_string($string);
}

# execute an SQL query with error handling
function sql_exec_query($query) {
	global $gui_dbh, $DEBUG;

	if(empty($query)) return(false);

	if($gui_dbh === false) {
		gui_db_connect();
	}

	$result = mysql_query($query, $gui_dbh);
	if($result === false) {
		echo "SQL query failed with the following error message;\n" .
		  mysql_error() . "\n";
		if($DEBUG) echo "Query was:\n".$query."\n";
	}

	return($result);
}

function fetch_and_import()
{
	global $gui_db_opt;

	$version = get_db_version();
	if ($version === false) {
		echo "Nothing to import\n";
		return true;
	}
	if ($version >= $gui_db_opt['new_db_version']) {
		# return if already imported and updated
		echo "Schedules seems to be already imported.\n";
		return true;
	}

	$sql = "SELECT * FROM ".$gui_db_opt['database'].".auto_reports_scheduled";

	$res = sql_exec_query($sql);
	if ($res === false) {
		return false;
	}
	while ($row = sql_fetch_array($res)) {
		import_schedule($row);
	}

	# upgrade db version in old database
	upgrade_db_version($gui_db_opt['new_db_version']);

	# truncate old table if all seems OK
	compare_and_truncate();

	echo "Done importing old schedules\n";
	return true;
}

function import_schedule($row)
{
	# new database and table
	$database = "monitor_reports";
	$table = "scheduled_reports";
	unset($row['id']);
	$fields = implode(',', array_keys($row));
	$values = "'".implode("', '", array_values($row))."'";
	$sql = "INSERT INTO ".$database.".".$table." (".$fields.") VALUES(".$values.")";
	sql_exec_query($sql);
	#echo $sql."\n";
}

function get_db_version()
{
	global $gui_db_opt;
	$ok = db_exists($gui_db_opt['database']);
	if (!$ok)
		return false;

	$sql = "SELECT version FROM ".$gui_db_opt['database'].".auto_reports_db_version";
	$res = sql_exec_query($sql);
	if ($res === false) {
		return false;
	}
	$row = sql_fetch_array($res);
	return isset($row['version']) ? $row['version'] : false;
}

function db_exists($db=false)
{
    if (empty($db))
        return false;
    global $gui_db_opt;
    $db_selected = mysql_select_db($db);
    if (!$db_selected) {
        return false;
    }
    return true;
}

function upgrade_db_version($version=false)
{
	global $gui_db_opt;
	if (empty($version)) {
		$version = $gui_db_opt['new_db_version'];
	}
	$sql = "UPDATE ".$gui_db_opt['database'].".auto_reports_db_version SET version='".$version."'";
	sql_exec_query($sql);
}

# check that we really have imported the old schedules
# and truncate the old table in that case
function compare_and_truncate()
{
	global $gui_db_opt;

	# new database and table
	$database = "monitor_reports";
	$table = "scheduled_reports";

	# check nr of schedules in old database
	$sql = "SELECT COUNT(id) AS cnt FROM ".$gui_db_opt['database'].".auto_reports_scheduled";
	$res = sql_exec_query($sql);
	$cnt_old = 0;
	if ($res !== false) {
		$row = sql_fetch_array($res);
		$cnt_old = $row['cnt'];
	}
	if ($cnt_old) {
		# only compare (and truncate) if the old table actually had any data
		$sql = "SELECT COUNT(id) AS cnt FROM ".$database.".".$table;
		$res = sql_exec_query($sql);
		if ($res !== false) {
			$row = sql_fetch_array($res);
			$cnt_new = $row['cnt'];
			if ($cnt_new >= $cnt_old) {
				# at least the same nr of schedules exists
				# go ahead and empty the old table
				$sql = "TRUNCATE ".$gui_db_opt['database'].".auto_reports_scheduled";
				sql_exec_query($sql);
				return true;
			}
		}
	}
	return false;
}

$return = fetch_and_import() === true ? 0 :1 ;
exit($return);
?>