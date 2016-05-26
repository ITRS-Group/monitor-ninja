<div class="information-component">
	<div class="information-component-title">Operating status</div>
<?php

$toggle = View::factory('extinfo/components/toggle', array(
	'object' => $object
));

?>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_active_checks_enabled() ? 'disable_check' : 'enable_check';
	$toggle->render(true);
?>
	<div class="information-cell-header">Active checks</div>
</div>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_accept_passive_checks() ? 'stop_accept_passive_checks' : 'start_accept_passive_checks';
	$toggle->render(true);
?>
	<div class="information-cell-header">Passive checks</div>
</div>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_obsess() ? 'stop_obsessing' : 'start_obsessing';
	$toggle->render(true);
?>
	<div class="information-cell-header">Obsessing</div>
</div>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_notifications_enabled() ? 'stop_notifications' : 'start_notifications';
	$toggle->render(true);
?>
	<div class="information-cell-header">Notifications</div>
</div>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_event_handler_enabled() ? 'stop_event_handler' : 'start_event_handler';
	$toggle->render(true);
?>
	<div class="information-cell-header">Event handler</div>
</div>
<div class="information-cell-inline">
<?php
	$toggle->command = $object->get_flap_detection_enabled() ? 'stop_flap_detection' : 'start_flap_detection';
	$toggle->render(true);
?>
	<div class="information-cell-header">Flap detection</div>
</div>
</div>
