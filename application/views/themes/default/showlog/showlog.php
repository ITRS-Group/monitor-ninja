<?php defined('SYSPATH') or die('No direct access allowed.');

$x = $this->translate;
echo "<br />\n";

echo "\$options = " . Kohana::debug($options);

echo form::open();
echo "<table><th width=10></th><th width=200></th><tr><td>\n";
echo $x->_('State type options') . "</td></tr><tr><td>\n";
$set = isset($options['state_types']['soft']);
echo "&nbsp; &nbsp;" . form::checkbox('state_types[soft]', $set) . "</td><td>" . $x->_('Soft states');
echo "</td></tr><tr><td>\n";
$set = isset($options['state_types']['hard']);
echo "&nbsp; &nbsp;" . form::checkbox('state_types[hard]', $set) . "</td><td>" . $x->_('Hard states');
echo "</td></tr><tr><td>\n";
echo $x->_('Host state options') . "</td></tr><tr><td>\n";
foreach ($host_state_options as $k => $v) {
	$set = isset($options['host_state_options'][$v]);
	$name = 'host_state_options[' . $v . ']';
	echo "&nbsp; &nbsp;" . form::checkbox($name, $set) . $k . "</td><td>";
	echo "</td></tr><tr><td>\n";
}
echo $x->_('Service state options') . "</td></tr><tr><td>\n";
foreach ($service_state_options as $k => $v) {
	$set = isset($options['service_state_options'][$v]);
	$name = 'service_state_options[' . $v . ']';
	echo "&nbsp; &nbsp;" . form::checkbox($name, $set) . $k . "</td><td>";
	echo "</td></tr><tr><td>\n";
}

echo form::checkbox('hide_flapping') . "</td><td>\n";
echo $x->_('Hide flapping alerts');
echo "</td></tr><tr><td>\n";
echo form::checkbox('hide_downtime') . "</td><td>\n";
echo $x->_('Hide downtime alerts');
echo "</td></tr><tr><td>\n";
echo form::checkbox('hide_process') . "</td><td>\n";
echo $x->_('Hide process messages');
echo "</td></tr><tr><td>\n";
echo form::checkbox('parse_forward') . "</td><td>\n";
echo $x->_('Older entries first');
echo "</td></tr></table>\n";
# this hidden thing marks "no options chosen" as a chosen set of options
# so we avoid overriding "no options" with the default ones
echo form::hidden('have_options', 1);
echo form::submit('Update', 'Update');
echo form::close();
$this->_show_log_entries();
