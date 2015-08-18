<?php defined('SYSPATH') OR die('No direct access allowed.');

/* @var $object NaemonMonitoredObject_Model */
$commands = $object->list_custom_commands();

if(!$commands) {
	return;
}
?>
<tr>
	<td colspan="2" class="bt"><h2><?php echo _('Custom commands'); ?></h2></td>
</tr>
<?php
foreach($commands as $cmd => $cmdinfo) {
	$linktext = ucwords(strtolower(str_replace('_', ' ', $cmd)));
	$cmd = html::specialchars($cmd);
	$key = html::specialchars($object->get_key());
	$table = html::specialchars($object->get_table());
	$link = "<a href='#' data-table='$table' data-key='$key' data-command='$cmd'>" . $linktext . "</a>";
?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-cli" title="<?php echo html::specialchars($cmd) ?>"></span>
			</td>
			<td class="bt custom_command"><?php echo $link; ?></td>
		</tr>
<?php } ?>
