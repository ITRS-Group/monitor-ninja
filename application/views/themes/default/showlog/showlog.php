<?php defined('SYSPATH') or die('No direct access allowed.');

echo html::script('application/media/js/jscalendar/calendar.js');
echo html::script('application/media/js/jscalendar/lang/calendar-en.js');
echo html::script('application/media/js/jscalendar/calendar-setup.js');
echo html::script('application/media/js/calendar-extras.js');
echo html::stylesheet('application/media/js/jscalendar/skins/aqua/theme.css');

$x = $this->translate;
echo "<br />\n";

echo "\$options = " . Kohana::debug($options);

echo form::open();
echo "<table><tr><th width=10></th><th width=200></th></tr>\n";
echo '<tr><td>' . $x->_('State type options') . "</td></tr>\n";

$set = isset($options['state_types']['soft']);
echo '<tr><td>&nbsp; &nbsp;' . form::checkbox('state_types[soft]', $set) . "</td>\n";
echo "<td>" . $x->_('Soft states') . "</td></tr>\n";

$set = isset($options['state_types']['hard']);
echo '<tr><td>&nbsp; &nbsp' . form::checkbox('state_types[hard]', $set) . "</td>\n";
echo '<td>' . $x->_('Hard states') . "</td></tr>\n";
echo '<tr><td>' .  $x->_('Host state options') . "</td></tr>\n";
foreach ($host_state_options as $k => $v) {
	$set = isset($options['host_state_options'][$v]);
	$name = 'host_state_options[' . $v . ']';
	echo '<tr><td>&nbsp; &nbsp;' . form::checkbox($name, $set) . "</td>\n";
	echo "<td>$k</td></tr>\n";
}
echo '<tr><td>' . $x->_('Service state options') . "</td></tr>\n";
foreach ($service_state_options as $k => $v) {
	$set = isset($options['service_state_options'][$v]);
	$name = 'service_state_options[' . $v . ']';
	echo '<tr><td>&nbsp; &nbsp;' . form::checkbox($name, $set) . "</td>\n";
	echo "<td>$k</td></tr>\n";
}

echo '<tr><td>' . form::checkbox('hide_flapping') . "</td>\n";
echo '<td>' . $x->_('Hide flapping alerts') . "</td></tr>\n";

echo '<tr><td>' . form::checkbox('hide_downtime') . "</td>\n";
echo '<td>' . $x->_('Hide downtime alerts') . "</td></tr>\n";

echo '<tr><td>' . form::checkbox('hide_process') . "</td>\n";
echo '<td>' . $x->_('Hide process messages') . "</td></tr>\n";

echo '<tr><td>' . form::checkbox('parse_forward') . "</td>\n";
echo '<td>' . $x->_('Older entries first') . "</td></tr>\n";

echo '<tr><td>' . $x->_('First time') . '</td>';
echo '<td>' . form::input('first') . "</td></tr>\n";
?>
<script type="text/javascript">
Calendar.setup({
	inputField      : 'first',           // id of the input field
	ifFormat        : '%Y-%m-%d %H:%M',  // format of the input field
	daFormat        : '%Y-%m-%d %H:%M',  // format of the displayed field
	button          : 'f_trigger_start', // trigger for the calendar (button ID)
	align           : 'Bl',              // alignment (defaults to "Bl")
	timeFormat      : '24',
	showsTime       : true,
	displayArea     : 'start_time_tmp',
	firstDay        : 1,
	onClose         : store_start_date,
	singleClick     : true
});
</script>

<?php
echo '<tr><td>' . $x->_('Last time') . '</td>';
echo '<td>' . form::input('last') . "</td></tr>\n";
?>
<script type="text/javascript">
Calendar.setup({
	inputField      : 'last',            // id of the input field
	ifFormat        : '%Y-%m-%d %H:%M',  // format of the input field
	daFormat        : '%Y-%m-%d %H:%M',  // format of the displayed field
	button          : 'f_trigger_end',   // trigger for the calendar (button ID)
	align           : 'Bl',              // alignment (defaults to "Bl")
	timeFormat      : '24',
	showsTime       : true,
	displayArea     : 'end_time_tmp',
	firstDay        : 1,
	onClose         : check_start_date,
	singleClick     : true
});
</script>

<?php
echo "</table>\n";
# this hidden thing marks "no options chosen" as a chosen set of options
# so we avoid overriding "no options" with the default ones
echo form::hidden('have_options', 1);
echo form::submit('Update', 'Update');
echo form::close();
$this->_show_log_entries();
