<?php
	/**
		Collects db field data from a MySQL copy of the Merlin
		db and generates data for the Oracle bits in the
		PDO-based Ninja/Kohana driver. This is a kludge.
		Don't try this at home, kids.

		Usage:

		php this_script.php > system/libraries/drivers/Database/merlin_get_kohana_db_field_metadata.php

		That generates PHP code which initializes the field
		information in a way used by the pre-existing Kohana
		driver code.

		The generated file then gets included by
		system/libraries/drivers/Database/Pdogeneric.php to
		kludge the db field information into the Pdogeneric
		driver. This hack is Merlin/Ninja-specific.

*/

$dbConf = array(
	'dsn'=>'mysql:dbname=merlin;host=localhost;dbname=merlin',
	'user'=>'merlin',
	'password' => 'merlin'
);


/**
	Fetches db field information from the given db/table
	combination and returns an array containing
	stdClass objects, each with the following fields:

	obj->Field = field name

	obj->Type = field type (as reported by MySQL)

	obj->Null = 'YES' or 'NO'

	obj->Default = default value (or empty string)

	obj->Key = 'PRI' for primary key, else unspecified non-PRI value.

	$oj->Extra = "extra" field information (e.g. auto_increment).

	The format is derived from the (apparently undocumented)
	expectations of the Kohana db driver layer.
*/
function table_to_field_info(PDO $pdo, $tbl)
{
	$res = $pdo->query("DESCRIBE $tbl");
	$cols = array();
	while( ($row = $res->fetch(PDO::FETCH_NUM) ) ) {
	       $obj = new stdClass();
	       $obj->Field = $row[0];
	       $obj->Type = $row[1];
	       $obj->Null = $row[2] ? 'NO' : 'YES';
	       $obj->Key = $row[3] ? 'PRI' : NULL;
	       $obj->Default = $row[4];
	       $obj->Extra = $row[5];
	       $cols[] = $obj;
	}
	$res->closeCursor();
	return $cols;
}

/**
	Expects the output of multiple table_to_field_info() calls,
	packed together in a array in the form (tableName=>fieldInfo),
	where fieldInfo is an array of stdClass objects returned
	from table_to_field_info().
*/
function table_field_info_to_src($list) {
	 echo "function merlin_get_kohana_db_field_metadata() {\n";
	 echo "\t\$tables = array();\n";
	 echo "\t\$obj = false;\n";
	 foreach( $list as $tblName => $tables ) {
		  echo "\t\$fieldList = array();\n";
		  foreach( $tables as $ndx => $fieldInfo ) {
		   	  echo "\t\$fldObj = new stdClass();\n";
			  echo "\t\t\$fldObj->Field = '".$fieldInfo->Field."';\n";
			  echo "\t\t\$fldObj->Type = '".$fieldInfo->Type."';\n";
			  echo "\t\t\$fldObj->Null = '".$fieldInfo->Null."';\n";
			  echo "\t\t\$fldObj->Default = ".($fieldInfo->Default ? ("'".$fieldInfo->Default."'") : 'NULL').";\n";
			  echo "\t\t\$fldObj->Key = ".(($fieldInfo->Key==='PRI') ? "'PRI'" : "NULL").";\n";
			  echo "\t\t\$fldObj->Extra = ".($fieldInfo->Extra ? ("'".$fieldInfo->Extra."'") : "NULL").";\n";
			  echo "\t\t\$fieldList[] = \$fldObj;\n";
		  }
	 	  echo "\t\$tables['".$tblName."'] = \$fieldList;\n";
	 }
	 echo "\treturn \$tables;\n";
	 echo "} /* merlin_get_kohana_db_field_metadata() */\n";
}



////////////////////////////////////////////////////////////////////////
// Main app logic goes down here...
$pdo = new PDO($dbConf['dsn'], $dbConf['user'], $dbConf['password'],
          array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

unset($dbConf);

$res = $pdo->query("SHOW TABLES");
$tableList = array();
while( ($row = $res->fetch(PDO::FETCH_NUM)) ) {
       $tableList[] = $row[0];
}
$res->closeCursor();

$fieldData = array();
foreach( $tableList as $ndx => $tableName) {
	 $fieldData[$tableName] =  table_to_field_info($pdo,$tableName);
}

#print_r($fieldData);
echo '<'.'?'.'php'."\n";
table_field_info_to_src($fieldData);
echo '?'.">\n";

unset($pdo)

?>
