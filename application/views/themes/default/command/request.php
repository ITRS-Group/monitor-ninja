<?php defined('SYSPATH') OR die('No direct access allowed.');
//echo Kohana::debug($info);

echo '<div class="widget left w98">';
if (!$info) {
	echo "Unknown command: $requested_command<br />\n";
	return;
}

echo "<p><strong>$brief</strong></p>\n";
echo "<p style=\"width: 550px\">$description</p>\n";
echo form::open('command/commit', array('id' => 'command_form'));

$params = $info['params'];
echo "<table>\n";
echo '<tr><th colspan="2" class="headerNone">Name</th><th class="headerNone">Option</th></tr>';
foreach ($params as $pname => $ary) {
	$form_name = "cmd_param[$pname]";
	$dflt = false;
	if (isset($ary['default']))
		$dflt = $ary['default'];
	echo '<tr class="even">';
	//echo '<td style="width: 12px">'.html::image('application/views/themes/default/icons/12x12/shield-info.png',array('alt' => $this->translate->_('View help'), 'title' => $this->translate->_('View help'), 'style' => 'float: left')).'</td>';
	echo '<td style="width: 12px">'.(isset($ary['help']) ? $ary['help'] : '').'</td>';
	echo '<td style="width: 200px" id="'.$pname.'">'.$ary['name'].'</td><td>';

	switch ($ary['type']) {
	 case 'select':
		if ($dflt && array_search($dflt, $ary['options'])) {
			$dflt = array_search($dflt, $ary['options']);
		}
		echo form::dropdown($form_name, $ary['options'], $dflt);
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
		echo form::checkbox($form_name, $dflt, 'class="checkbox"');
		break;
	 case 'float':
	 case 'int':
		echo form::input($form_name, $dflt, 'size="10"');
		break;
	 case 'immutable':
		echo form::hidden($form_name, $dflt);
		echo $dflt;
		break;
	 case 'string':
	 default:
		echo form::input(array('name' => $form_name, 'title' => $this->translate->_('Required field')), $dflt, '');
		break;
	}

	echo "</td></tr>\n";
}
echo "</table><br />\n";
echo form::hidden('requested_command', $requested_command);
echo form::submit('Commit', $this->translate->_('Submit'), 'class="submit"');
echo "<input type='reset' value='" . $this->translate->_("Reset") . "'>\n";
echo form::close();
echo '</div>';
