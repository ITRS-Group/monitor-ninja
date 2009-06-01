<?php defined('SYSPATH') OR die('No direct access allowed.');
echo Kohana::debug($info);

if (!$info) {
	echo "Unknown command: $requested_command<br />\n";
	return;
}

echo "<h2>$brief</h2>\n";
echo "<h3>$description</h3>\n";
echo form::open('command/commit');

$params = $info['params'];
echo "<table>\n";
foreach ($params as $pname => $ary) {
	$dflt = false;
	if (isset($ary['default']))
		$dflt = $ary['default'];

	echo "<tr><td>$ary[name]</td><td>\n";
	switch ($ary['type']) {
	 case 'select':
		if ($dflt && array_search($dflt, $ary['options'])) {
			$dflt = array_search($dflt, $ary['options']);
		}
		echo form::dropdown($pname, $ary['options'], $dflt);
		break;
	 case 'checkbox':
		if (isset($ary['options'])) {
			foreach ($ary['options'] as $k => $v) {
				echo form::checkbox($pname . "[$k]", 'class="checkbox"');
			}
			break;
		}
		# fallthrough
	 case 'bool':
		echo form::checkbox($pname, $dflt, 'class="checkbox"');
		break;
	 case 'float':
	 case 'int':
		echo form::input($pname, 'size="10"');
		break;
	 case 'immutable':
		echo form::hidden($pname, $dflt);
		echo $dflt;
		break;
	 case 'string':
	 default:
		echo form::input($pname, $dflt, 'class="text"');
		break;
	}
	echo "$ary[type]\n";
	echo "</td><td>helptext here</td></tr>\n";
}
echo "</table>\n";
echo form::submit('Commit', $this->translate->_('Submit'), 'class="submit"');
echo "<input type='reset' value='" . $this->translate->_("Reset") . "'>\n";
