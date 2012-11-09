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
	//echo "<table style=\"margin-top: 20px;\">\n";
}
foreach ($params as $pname => $ary) {
	$form_name = "cmd_param[$pname]";
	$dflt = false;
	if (isset($ary['default']))
		$dflt = $ary['default'];
//	echo '<tr>';

	# help column only printed if we really have a help key

	echo '<div class="" style="padding: 2px 0 0 10px; " id="'.$pname.'">';
	echo $use_help ? (isset($ary['help']) ? '<span style="width: 16px">'.$ary['help'].'</span>&nbsp;' : '') : '';
	echo '<label><span style="display: inline-block; margin-right: 8px;">'.$ary['name'].'</span>';

	switch ($ary['type']) {
		case 'select':
			if (!is_array($dflt) && $requested_command != 'DEL_ALL_HOST_COMMENTS' && $requested_command != 'DEL_ALL_SVC_COMMENTS') {
				if ($dflt && false !== array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $form_name, 'id' => 'field_'.$pname, 'style' => 'width: auto;'), $ary['options'], $dflt);
			} elseif ($requested_command == 'DEL_ALL_SVC_COMMENTS' || $requested_command == 'DEL_ALL_HOST_COMMENTS') {
				if ($dflt && false !== array_search($dflt, $ary['options'])) {
					$dflt = array_search($dflt, $ary['options']);
				}
				echo form::dropdown(array('name' => $form_name.'[]', 'id' => 'field_'.$pname, 'multiple' => 'multiple', 'style' => 'width: auto;'), $ary['options'], $dflt);
			} else if (!empty($dflt)) {
				$tmp_obj = false;
				foreach($dflt as $tmp) {
					$tmp_obj[$tmp] = isset($ary['options'][$tmp]) ? $ary['options'][$tmp] : $tmp;
				}
				$size = count($tmp_obj);
				if($size > 15) {
					$size = 15;
				}
				echo '<br />' . form::dropdown(array('name' => $form_name.'[]', 'multiple' => 'multiple', 'id' => 'field_'.$pname, 'size' => $size, 'style' => 'width: 350px;'), $tmp_obj);
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
			echo '<b style="font-weight: bold;">'.$dflt.'</b>';
			break;
		case 'string':
		default:
			if ($form_name == 'cmd_param[comment]')
				echo form::input(array('name' => $form_name, 'title' => _('Required field'), 'style' => 'width: 280px'), $dflt, '');
			else
				echo form::input(array('name' => $form_name, 'title' => _('Required field'), 'id' => 'field_'.$pname), $dflt, '');
			break;
	}

	echo "</label></div>\n";
}

echo '<div class="left" style="border: none;">&nbsp;</div><div class="clear"></div><div class="left width-50">';
echo form::hidden('requested_command', $requested_command);
echo form::submit('Commit', _('Submit'), 'class="submit"');
if (!empty($params)) {
	echo " &nbsp;<input type='reset' value='" . _("Reset") . "'>\n";
}
echo '</div>';
echo form::close();
echo '</div>';
