<?php defined('SYSPATH') OR die('No direct access allowed.');

function make_title($titlify) {
	return mb_convert_case(str_replace('_', ' ', $titlify), MB_CASE_TITLE);
};

?>

<style>
table {margin: 2em}
table td {vertical-align: top;border: none;}
input, select {margin: 0 0 6px 0;}
b {margin: 0 0 6px 0; display: inline-block;}
</style>

<?php
if($error) {
	echo "<p>$error</p>";
	return;
}
?>

<div style="margin: 4px 8px">
	<h2><?php echo make_title($command); ?></h2>
	<p><?php echo $command_info['description']; ?></p>
</div>

<?php
echo form::open('exec_cmd/obj', array('id' => 'command_form', 'method' => 'post'));

// object table name
echo form::hidden('t', $object->get_table());
// object key
echo form::hidden('o', $object->get_key());
// command
echo form::hidden('c', $command);
?>

<table>

<?php
// TODO remove
$cmd_typ = false;
foreach ($command_info['parameters'] as $pname => $ary) {
	$dflt = false;

	echo "<tr><td>";
	if($ary['type'] === 'immutable') {
		echo $ary['name'];
	} else {
		echo "<label for='field_$pname'>".make_title($pname)."</label>";
	}
	echo "</td><td>";


	switch ($ary['type']) {
		case 'select':
			if (!is_array($dflt) && $cmd_typ != 'DEL_ALL_HOST_COMMENTS' && $cmd_typ != 'DEL_ALL_SVC_COMMENTS') {
				if ($dflt && false !== array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $pname, 'id' => 'field_'.$pname, 'style' => 'width: auto;'), $ary['options'], $dflt);
			} elseif ($cmd_typ == 'DEL_ALL_SVC_COMMENTS' || $cmd_typ == 'DEL_ALL_HOST_COMMENTS') {
				if ($dflt && false !== array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $pname.'[]', 'id' => 'field_'.$pname, 'multiple' => 'multiple', 'style' => 'width: auto;'), $ary['options'], $dflt);
			} else if (!empty($dflt)) {
				$tmp_obj = false;
				foreach($dflt as $tmp) {
					$tmp_obj[$tmp] = isset($ary['options'][$tmp]) ? $ary['options'][$tmp] : $tmp;
				}
				$size = count($tmp_obj);
				if($size > 15) {
					$size = 15;
				}
				echo '<br />' . form::dropdown(array('name' => $pname.'[]', 'multiple' => 'multiple', 'id' => 'field_'.$pname, 'size' => $size, 'style' => 'width: 350px;'), $tmp_obj);
			}
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
			$checked = (bool)$dflt;
			echo form::checkbox(array('name' => $pname, 'id' => 'field_'.$pname), true, $checked, 'class="checkbox"');
			break;
		case 'float':
		case 'int':
			echo form::input(array('name' => $pname, 'id' => 'field_'.$pname), $dflt, 'size="10"');
			break;
		case 'immutable':
			if(is_array($dflt)) {
				$dflt = current($dflt);
			}
			echo form::hidden($pname, $dflt);
			echo '<b style="font-weight: bold;">'.$dflt.'</b>';
			break;
		case 'string':
		default:
			if ($pname == 'comment')
				echo form::input(
					array(
						'class' => 'autotest-required',
						'id' => 'field_'.$pname,
						'name' => $pname,
						'title' => _('Required field'),
						'style' => 'width: 280px'
					),
					$dflt,
					''
				);
			else {
				switch($pname) {
					case  "start_time":
					case  "end_time":
					case  "check_time":
						$classname = 'date';
						break;
					case "duration":
						$classname = 'float';
						break;
					default:
						$classname = 'required';
				}
				echo form::input(array('class' => "input-wide autotest-$classname", 'name' => $pname, 'title' => _('Required field'), 'id' => 'field_'.$pname), $dflt, '');
			}
			break;
	}

	echo "</td></tr>\n";
}
?>

<tr>
	<td colspan='2'>
	<?php echo form::submit('Commit', _('Submit'), 'class="submit"'); ?>
	</td>
</tr>
</table>
<?php echo form::close();
