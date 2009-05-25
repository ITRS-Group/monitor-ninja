<?php defined('SYSPATH') OR die('No direct access allowed.');
echo Kohana::debug($info);

if (!$info) {
	echo "Unknown command: $requested_command<br />\n";
	return;
}

echo "<h2>$brief</h2>\n";
echo "<h3>$description</h3>\n";

$params = $info['params'];
echo "<table>\n";
foreach ($params as $param_name => $ary) {
	$dflt = false;
	if (isset($ary['default']))
		$dflt = $ary['default'];

	echo "<tr><td>$ary[name]</td><td>\n";
	switch ($ary['type']) {
	 case 'select':
		echo "<select name='$param_name'>\n";
		foreach ($ary['options'] as $k => $v) {
			if ($k == $dflt || $v == $dflt) {
				echo "ZOMG it's THE CHOSEN ONE<br />\n";
				echo "<option selected value='$k'>";
			} else {
				echo "<option value='$k'>";
			}
			echo "$v</option>\n";
		}
		break;
	 case 'checkbox':
		if (isset($ary['options'])) {
			foreach ($ary['options'] as $k => $v) {
				echo "<input type='checkbox' name='" . $param_name . "[$k]'>";
			}
			break;
		}
		# fallthrough
	 case 'bool':
		echo "<input type='checkbox' name='$param_name'>";
		break;
	 case 'float':
	 case 'int':
		echo "<input type='text' size=10 name='$param_name' />\n";
		break;
	 case 'immutable':
		echo "<input type='hidden' name='$param_name' value='$dflt'>$dflt";
		break;
	 case 'string':
	 default:
		echo "<input type='text' name='$param_name' ";
		if ($dflt)
			echo "value='$dflt'";
		echo "/>\n";
		break;
	}
	echo "$ary[type]\n";
	echo "</td><td>helptext here</td></tr>\n";
}
echo "</table>\n";
