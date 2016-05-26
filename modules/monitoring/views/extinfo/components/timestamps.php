<div class="information-component">
	<div class="information-component-title">
		Timestamps
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Last check'); ?>
		</div>
<?php
	$utc_offset = date::utc_offset(date_default_timezone_get());
	$last_check = $object->get_last_check();
	$last_check_raw = $last_check;
	if (!$last_check) {
		$last_check = 'N/A';
		$last_check_raw = 'N/A';
	} else {
		$last_check_raw = date(date::date_format(), $last_check);
		$last_check = time::to_string(time() - $last_check);
	}

?>
		<div class="information-cell-value">
			<?php echo $last_check; ?>
		</div>
		<div class="information-cell-raw faded">
			<?php echo $last_check_raw; ?>
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Next check'); ?>
		</div>
<?php
	$next_check = 'N/A';
	$next_check_raw = '';
	if ($object->get_next_check() && $object->get_active_checks_enabled()) {
		$source = $object->get_source_type();
		if ($source != 'poller' && $source != 'peer') {
			$next_check_raw = date(date::date_format(), $object->get_next_check() + $utc_offset);
			$next_check = time::to_string($object->get_next_check() - time());
		} else {
			$next_check = "Remotely checked";
			$next_check_raw = 'By' . $object->get_source_node();
		}
	} else {
		$next_check = 'N/A';
		$next_check_raw = 'N/A';
	}
?>
		<div class="information-cell-value">
			<?php echo $next_check; ?>
		</div>
		<div class="information-cell-raw faded">
			<?php echo $next_check_raw; ?>
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Last change'); ?>
		</div>
<?php
	$last_change = $object->get_last_state_change();
	$last_change_raw = $last_change;
	if (!$last_change) {
		$last_change = 'N/A';
		$last_change_raw = 'N/A';
	} else {
		$last_change_raw = date(date::date_format(), $last_change);
		$last_change = time::to_string(time() - $last_change);
	}
?>
		<div class="information-cell-value">
			<?php echo $last_change; ?>
		</div>
		<div class="information-cell-raw faded">
			<?php echo $last_change_raw; ?>
		</div>
	</div>
	<div class="information-cell">
		<div class="information-cell-header">
			<?php echo _('Last notification'); ?>
		</div>
<?php
	$last_notification = $object->get_last_notification();
	$last_notification_raw = $last_notification;
	if (!$last_notification) {
		$last_notification = 'N/A';
		$last_notification_raw = 'N/A';
	} else {
		$last_notification_raw = date(date::date_format(), $last_notification);
		$last_notification = time::to_string(time() - $last_notification);
	}
?>
		<div class="information-cell-value">
			<?php echo $last_notification; ?>
		</div>
		<div class="information-cell-raw faded">
			<?php echo $last_notification_raw; ?>
		</div>
	</div>
</div>
