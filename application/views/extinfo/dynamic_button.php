<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $object NaemonMonitoredObject_Model */

$commands = $object->list_custom_commands();

if(count($commands) > 0) {
?>
<div class="right width-33" id="extinfo_info">
	<h2>Custom commands</h2>
	<table class="ext">
<?php
foreach($commands as $cmd => $cmdinfo) {
?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-command" title="<?php echo html::specialchars($cmd) ?>"></span>
			</td>
			<td class="bt"><?php echo $cmd; ?></td>
		</tr>
<?php } ?>
	</table>
</div>
<?php } ?>