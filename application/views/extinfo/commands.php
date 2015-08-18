<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $object Object_Model */
$commands = $object->list_commands();

$command_categories = array();

foreach($commands as $cmd => $cmdinfo) {
	if($cmdinfo['enabled']) {
		if(!isset($command_categories[$cmdinfo['category']]))
			$command_categories[$cmdinfo['category']] = array();
		$command_categories[$cmdinfo['category']][$cmd] = $cmdinfo;
	}
}

?>
<div class="right width-33" id="extinfo_info">
	<table class="ext">
<?php
foreach($command_categories as $category => $category_commands) {
?>
		<tr>
			<td colspan="2" class="bt"><h2><?php echo html::specialchars($category) ?></h2></td>
		</tr>
<?php
	foreach($category_commands as $cmd => $cmdinfo) {
?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $cmdinfo['icon']; ?>" title="<?php echo  html::specialchars($cmdinfo['name']) ?>"></span>
			</td>
			<td class="bt"><?php echo cmd::cmd_link($object, $cmd, $cmdinfo['name']) ?></td>
		</tr>
<?php
	}
}

	if($object instanceof NaemonMonitoredObject_Model) {
		$dynamic_button_view = new View('extinfo/dynamic_button', array('object' => $object));
		$dynamic_button_view->render(true);
	}
?>
	</table>
</div>

