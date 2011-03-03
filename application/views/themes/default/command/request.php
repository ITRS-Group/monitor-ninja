<?php defined('SYSPATH') OR die('No direct access allowed.');
//echo Kohana::debug($info);

echo '<div class="widget left w98">';
if (!$info) {
	echo "Unknown command: $requested_command<br />\n";
	return;
}

echo "<h2>$brief</h2>\n";
echo "<p style=\"width: 550px\">$description</p>\n";
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
	echo "<table style=\"margin-top: 20px; width: auto\" class=\"white-table\">\n";
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
						$tmp_obj[$tmp] = $tmp;
					}
					echo form::dropdown(array('name' => $form_name.'[]', 'multiple' => 'multiple', 'id' => 'field_'.$pname), $tmp_obj);
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
			$default_checked = array('fixed', '_services-too', 'sticky', 'notify', 'persistent', '_force');
			$checked = in_array($pname, $default_checked) ? true : false;
			echo form::checkbox(array('name' => $form_name, 'id' => 'field_'.$pname), $dflt, $checked, 'class="checkbox"');
			break;
		case 'float':
		case 'int':
			echo form::input(array('name' => $form_name, 'id' => 'field_'.$pname), $dflt, 'size="10"');
			break;
		case 'immutable':
			echo form::hidden($form_name, $dflt);
			echo $dflt;
			break;
		case 'string':
		default:
			if ($form_name == 'cmd_param[comment]')
				echo form::input(array('name' => $form_name, 'title' => $this->translate->_('Required field'), 'style' => 'width: 280px'), $dflt, '');
			else
				echo form::input(array('name' => $form_name, 'title' => $this->translate->_('Required field'), 'id' => 'field_'.$pname), $dflt, '');
			break;
	}

	echo "</td></tr>\n";
}

echo '<tr><td colspan="'.($use_help ? 2 : 1).'">&nbsp;</td><td>';
echo form::hidden('requested_command', $requested_command);
echo form::submit('Commit', $this->translate->_('Submit'), 'class="submit"');
if (!empty($params)) {
	echo " &nbsp;<input type='reset' value='" . $this->translate->_("Reset") . "'>\n";
}
echo '</td></tr></table>';
echo form::close();
echo '</div>';
