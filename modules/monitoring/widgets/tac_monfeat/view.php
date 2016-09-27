<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="list-table" id="mmm">
	<colgroup>
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
		<col style="width: 20%" />
	</colgroup>
	<tr>
	<td>
		<ul>
<?php
			if ($status->get_enable_flap_detection()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_flap_link, _("Flap detection enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_flap_link, _("Flap detection disabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($host_status->flapping_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[hosts] flap_detection_enabled = 0')), $host_status->flapping_disabled . " Host(s) disabled") . "</li>";
			if ($host_status->flapping)
				echo "<li>" . html::icon('shield-unreachable') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[hosts] is_flapping = 1')), $host_status->flapping . " Host(s) flapping") . "</li>";
			else echo "<li>" . html::icon('shield-enabled') . "No Host flapping</li>";

			if ($service_status->flapping_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[services] flap_detection_enabled = 0')), $service_status->flapping_disabled . " Service(s) disabled") . "</li>";
			if ($service_status->flapping)
				echo "<li>" . html::icon('shield-unknown') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[services] is_flapping = 1')), $service_status->flapping . " Service(s) flapping") . "</li>";
			else echo "<li>" . html::icon('shield-enabled') . "No Service flapping</li>";
?>
		</ul>
	</td>
	<td>
		<ul>
<?php
			if ($status->get_enable_notifications()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_notification_link, _("Notifications enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_notification_link, _("Notifications disabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($host_status->notifications_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[hosts] notifications_enabled = 0')), $host_status->notifications_disabled . " Host(s) disabled") . "</li>";
			if ($service_status->notifications_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url('listview', 'index', array('q' => '[services] notifications_enabled = 0')), $service_status->notifications_disabled . " Service(s) disabled") . "</li>";
?>
		</ul>
	</td>
	<td>
		<ul>
<?php
			if ($status->get_enable_event_handlers()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_event_link, _("Event handlers enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_event_link, _("Event handlers disabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($host_status->eventhandler_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url("listview", "index", array('q' => '[hosts] event_handler_enabled = 0')), $host_status->eventhandler_disabled . " Host(s) disabled") . "</li>";
			if ($service_status->eventhandler_disabled)
				echo "<li>" . html::icon('shield-pending') . html::href($linkprovider->get_url("listview", "index", array('q' => '[services] event_handler_enabled = 0')), $service_status->eventhandler_disabled . " Services(s) disabled") . "</li>";
?>
		</ul>
	</td>
	<td>
		<ul>
<?php
			if ($status->get_execute_host_checks()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_check_host_link, _("Active Host checks enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_check_host_link, _("Active Host checks disabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($host_status->active_checks_disabled_active) {
				echo "<li>" . html::icon('shield-disabled') . html::href($linkprovider->get_url("listview", "index", array('q' => '[hosts] active_checks_enabled = 0')), $host_status->active_checks_disabled_active . " Host(s) disabled") . "</li>";
			} else {
				echo "<li>" . html::icon('shield-enabled') . "All Hosts enabled</li>";
			}

			if ($status->get_execute_service_checks()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_check_service_link, _("Active Service checks enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_check_service_link, _("Active Service checks disabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($service_status->active_checks_disabled_active) {
				echo "<li>" . html::icon('shield-disabled') . html::href($linkprovider->get_url("listview", "index", array('q' => '[services] active_checks_enabled = 0')), $service_status->active_checks_disabled_active . " Services(s) disabled") . "</li>";
			} else {
				echo "<li>" . html::icon('shield-enabled') . "All Services enabled</li>";
			}
?>
		</ul>
	</td>
	<td>
		<ul>
<?php
			if ($status->get_accept_passive_host_checks()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_passive_host_link, _("Passive Host checks enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-disabled') . html::href($cmd_passive_host_link, _("Passive Host checks enabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($host_status->passive_checks_disabled) {
				echo "<li>" . html::icon('shield-disabled') . html::href($linkprovider->get_url("listview", "index", array('q' => '[hosts] accept_passive_checks = 0')), $host_status->passive_checks_disabled . " Hosts(s) disabled") . "</li>";
			} else {
				echo "<li>" . html::icon('shield-enabled') . "All Hosts enabled</li>";
			}

			if ($status->get_accept_passive_service_checks()) {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_passive_service_link, _("Passive Service checks enabled"), array('title' => "Click to disable", 'class' => 'command-ajax-link')) . "</li>";
			} else {
				echo "<li>" . html::icon('shield-enabled') . html::href($cmd_passive_service_link, _("Passive Service checks enabled"), array('title' => "Click to enable", 'class' => 'command-ajax-link')) . "</li>";
			}

			if ($service_status->passive_checks_disabled) {
				echo "<li>" . html::icon('shield-disabled') . html::href($linkprovider->get_url("listview", "index", array('q' => '[services] accept_passive_checks = 0')), $service_status->passive_checks_disabled . " Services(s) disabled") . "</li>";
			} else {
				echo "<li>" . html::icon('shield-enabled') . "All Services enabled</li>";
			}
?>
		</ul>
	</td>
	</tr>
</table>
