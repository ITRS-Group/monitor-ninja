<?php

$downtimes = null;
$title = "";
$href = "";

if ($object->get_table() === 'services') {
	if ($object->get_scheduled_downtime_depth()) {
		$title = "This service is in scheduled downtime, click here to go to " .
			"a list of the relevant downtimes";
		$downtimes = DowntimePool_Model::all()
			->reduce_by('host.name', $object->get_host()->get_name(), '=')
			->reduce_by('service.description', $object->get_description(), '=');
	} elseif ($object->get_host()->get_scheduled_downtime_depth()) {
		$title = "The host this service resides on is in scheduled downtime, " .
			"click here to go to a list of the relevant downtimes";
		$downtimes = DowntimePool_Model::all()
			->reduce_by('host.name', $object->get_host()->get_name(), '=');
	}
} elseif ($object->get_scheduled_downtime_depth()) {
	$title = "This host is in scheduled downtime, click here to go to a list " .
		"of the relevant downtimes";
	$downtimes = DowntimePool_Model::all()
		->reduce_by('host.name', $object->get_name(), '=');
}
?>
<li title="<?php echo $title; ?>">
<?php
if ($downtimes) {
	$href = listview::querylink($downtimes->get_query());
?>
	<h2>
		<?php echo icon::get('clock'); ?>
		<a href="<?php echo $href; ?>">in scheduled downtime</a>
	</h2>
<?php
} else {
	$href = $linkprovider->get_url('cmd', 'index', array(
		'command' => 'schedule_downtime',
		'object' => $object->get_key(),
		'table' => $object->get_table()
	));
	$type = $object->get_table() === 'hosts' ? 'host' : 'service';
?>
	<h2>
		<?php echo icon::get('clock'); ?>
		<a class="command-ajax-link" href="<?php echo $href; ?>" title="Schedule downtime for this <?php echo $type; ?>">Schedule downtime</a>
	</h2>
<?php
}
?>
</li>
<?php
