<?php defined('SYSPATH') OR die('No direct access allowed.');
//echo Kohana::debug($info);

echo '<div>';
if (!$info) {
	echo "Unknown command: $requested_command<br />\n";
	return;
}

echo "<h2>$brief</h2>\n";
echo "<p>$description</p>\n";
echo form::open('command/commit', array('id' => 'command_form'));

$params = $info['params'];

# check if we need to make room for help icon
$use_help = false;
foreach ($params as $pname => $ary) {
	if (array_key_exists('help', $ary)) {
		$use_help = true;
		break;
	}
}

if (!empty($params)) {
	echo "<table style=\"margin-top: 20px;\">\n";
}
foreach ($params as $pname => $ary) {
	$form_name = "cmd_param[$pname]";
	$dflt = false;
	if (isset($ary['default']))
		$dflt = $ary['default'];
	echo '<tr>';

	# help column only printed if we really have a help key
	echo $use_help ? '<td style="width: 16px">'.(isset($ary['help']) ? $ary['help'] : '').'</td>' : '';

	echo '<td style="padding-right: 30px" id="'.$pname.'">'.$ary['name'].'</td><td>';

	switch ($ary['type']) {
		case 'select':
			if (!is_array($dflt) && $requested_command != 'DEL_ALL_HOST_COMMENTS' && $requested_command != 'DEL_ALL_SVC_COMMENTS') {
				if ($dflt && array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $form_name, 'id' => 'field_'.$pname), $ary['options'], $dflt);
			} elseif ($requested_command == 'DEL_ALL_SVC_COMMENTS' || $requested_command == 'DEL_ALL_HOST_COMMENTS') {
				if ($dflt && array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $form_name.'[]', 'id' => 'field_'.$pname, 'multiple' => 'multiple'), $ary['options'], $dflt);
			} else {
				if (!empty($dflt)) {
					$tmp_obj = false;
					foreach($dflt as $tmp) {
						$tmp_obj[$tmp] = isset($ary['options'][$tmp]) ? $ary['options'][$tmp] : $tmp;
					}
					$size = count($tmp_obj);
					if($size > 15) {
						$size = 15;
					}
					echo form::dropdown(array('name' => $form_name.'[]', 'multiple' => 'multiple', 'id' => 'field_'.$pname, 'size' => $size), $tmp_obj);
				}
			}
			break;
		case 'checkbox':
			if (isset($ary['options'])) {
				foreach ($ary['options'] as $k => $v) {
					echo form::checkbox($form_name . "[$k]", 'class="checkbox"');
				}
				break;
			}
			# fallthrough
		case 'bool':
			$checked = (bool)$dflt;
			echo form::checkbox(array('name' => $form_name, 'id' => 'field_'.$pname), true, $checked, 'class="checkbox"');
			break;
		case 'float':
		case 'int':
			echo form::input(array('name' => $form_name, 'id' => 'field_'.$pname), $dflt, 'size="10"');
			break;
		case 'immutable':
			if(is_array($dflt)) {
				$dflt = current($dflt);
			}
			echo form::hidden($form_name, $dflt);
			echo $dflt;
			break;
		case 'string':
		default:
			if ($form_name == 'cmd_param[comment]')
				echo form::input(array('name' => $form_name, 'title' => _('Required field'), 'style' => 'width: 280px'), $dflt, '');
			else
				echo form::input(array('name' => $form_name, 'title' => _('Required field'), 'id' => 'field_'.$pname), $dflt, '');
			break;
	}

	echo "</td></tr>\n";
}

echo '<tr><td colspan="'.($use_help ? 2 : 1).'">&nbsp;</td><td>';
echo form::hidden('requested_command', $requested_command);
echo form::submit('Commit', _('Submit'), 'class="submit"');
if (!empty($params)) {
	echo " &nbsp;<input type='reset' value='" . _("Reset") . "'>\n";
}
echo '</td></tr></table>';
echo form::close();
echo '</div>';
