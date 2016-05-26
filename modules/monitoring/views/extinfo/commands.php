<?php defined('SYSPATH') OR die('No direct access allowed.');

/* @var $object Object_Model */
$commands = $object->list_commands();
$command_categories = array();

foreach($commands as $cmd => $cmdinfo) {
	if ($cmdinfo['category'] === 'Operations') continue;
	if($cmdinfo['enabled']) {
		if(!isset($command_categories[$cmdinfo['category']]))
			$command_categories[$cmdinfo['category']] = array();
		$command_categories[$cmdinfo['category']][$cmd] = $cmdinfo;
	}
}

?>
<div class="left width-20 extinfo-commands">
	<ul class="extinfo-commands-list linklist">
<?php
foreach($command_categories as $category => $category_commands) {
?>
	<li>
		<h2 class="<?php echo ($category === 'Links' || $category === 'Actions') ? 'active' : ''; ?>" onclick="$(this).toggleClass('active')"><?php echo html::specialchars($category) ?></h2>
		<ul>
<?php
	foreach($category_commands as $cmd => $cmdinfo) {
		printf(
			'<li><a href="%s">%s</a></li>',
			$linkprovider->get_url('cmd', null, array(
				'command' => $cmd,
				'table' => $object->get_table(),
				'object' => $object->get_key()
			)),
			html::specialchars($cmdinfo['name'])
		);
	}
?>
		</ul>
	</li>
<?php }
	if($object instanceof NaemonMonitoredObject_Model) {
		$dynamic_button_view = new View('extinfo/dynamic_button', array('object' => $object));
		$dynamic_button_view->render(true);
	}
?>
	</ul>
</div>
