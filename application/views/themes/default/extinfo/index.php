<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div id="extinfo_object_main_info">
	<span class='data'><?php echo ucfirst($lable_type) ?><br /></span>
	<?php echo $main_object ?><br />
	<?php echo !empty($main_object_alias) ? '('.$main_object_alias.')' : '' ?><br />
	<?php
		if ($type == 'service') { ?>
			<?php echo $lable_on_host ?><br />
			<?php echo isset($host) ? ucfirst($host) : ''; ?><br />
			<?php echo isset($host_alias) ? '('.$host_alias.')' : '' ?>
			<?php echo !empty($host_link) ? '('.$host_link.')' : '' ?>
			<?php
		}
	?>
	<br />
	<?php echo $lable_member_of ?><br />

	<?php echo !empty($groups) ? implode(', ', $groups) : $no_group_lable ?><br />
	<?php echo isset($host_address) ? $host_address : ''; ?>
</div>

<table border=1 cellpadding="0" cellspacing="0" id="extinfo_status_table">
	<tr>
		<td class='dataVar'><?php echo $lable_current_status ?>:</td>
		<td class='dataVal'>
			<div class='service<?php echo $current_status_str ?>'><?php echo $current_status_str ?></div>
			<?php echo $duration ? '('.$lable_for.' '.$duration.')' : '' ?>
		</td>
	</tr>

	<tr>
		<td class='dataVar' valign='top'><?php echo $lable_status_information ?>:</td>
		<td class='dataVal'><?php echo $status_info ?></td>
	</tr>
	<tr>
		<td class='dataVar' valign='top'><?php echo $lable_perf_data ?>:</td>
		<td class='dataVal'><?php echo $perf_data ?></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_current_attempt ?>:</td>
		<td class='dataVal'><?php echo $current_attempt ?>/<?php echo $max_attempts ?>&nbsp;&nbsp;(<?php echo $state_type ?>)</td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_last_check ?>:</td>
		<td class='dataVal'><?php echo $last_check ? date($date_format_str, $last_check) : $na_str ?></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_check_type ?>:</td>
		<td class='dataVal'><?php echo $check_type ?></td>
	</tr>
	<tr>
		<td class='dataVar' nowrap="nowrap"><?php echo $lable_check_latency_duration ?>:</td>
		<td class='dataVal'><?php echo $check_latency ?>&nbsp;/&nbsp;<?php echo $execution_time ?> <?php echo $lable_seconds ?></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_next_scheduled_check ?>:&nbsp;&nbsp;</td>
		<td class='dataVal'><?php echo $next_check ? date($date_format_str, $next_check) : $na_str ?></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_last_state_change ?>:</td>
		<td class='dataVal'><?php echo $last_state_change ? date($date_format_str, $last_state_change) : $na_str ?></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_last_notification ?>:</td>
		<td class='dataVal'><?php echo $last_notification ?>&nbsp;(<?php echo $lable_notifications ?> <?php echo $current_notification_number ?>)</td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_flapping ?></td>
		<td class='dataVal'>
			<div class='notflapping'>&nbsp;&nbsp;NO&nbsp;&nbsp;</div>&nbsp;<?php echo $percent_state_change_str ?>
		</td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_in_scheduled_dt ?></td>
		<td class='dataVal'>
			<div class='downtimeINACTIVE'>&nbsp;&nbsp;<?php echo $scheduled_downtime_depth ?>&nbsp;&nbsp;</div>
		</td>
	</tr>

	<tr>
		<td class='dataVar'><?php echo $lable_last_update ?>:</td>
		<td class='dataVal'><?php echo $last_update ? date($date_format_str, $last_update) : $na_str ?>&nbsp;&nbsp;<?php echo $last_update_ago ?></td>
	</tr>
</table>
<br />

<table border=1 cellpadding="0" cellspacing="0" id="extinfo_stateinfo_table">
	<tr>
		<td class='dataVar'><?php echo $lable_active_checks ?>:</td>
		<td class='dataVal'><div class='checks<?php echo $active_checks_enabled ?>'>&nbsp;&nbsp;<?php echo $active_checks_enabled ?>&nbsp;&nbsp;</div></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_passive_checks ?>:</td>
		<td class='dataVal'><div class='checks<?php echo $passive_checks_enabled ?>'>&nbsp;&nbsp;<?php echo $passive_checks_enabled ?>&nbsp;&nbsp;</div></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_obsessing ?>:</td>
		<td class='dataVal'><div class='checks<?php echo $obsessing ?>'>&nbsp;&nbsp;<?php echo $obsessing ?>&nbsp;&nbsp;</div></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_notifications ?>:</td>
		<td class='dataVal'><div class='notifications<?php echo $notifications_enabled ?>'>&nbsp;&nbsp;<?php echo $notifications_enabled ?>&nbsp;&nbsp;</div></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_event_handler ?>:</td>
		<td class='dataVal'><div class='eventhandlers<?php echo $event_handler_enabled ?>'>&nbsp;&nbsp;<?php echo $event_handler_enabled ?>&nbsp;&nbsp;</div></td>
	</tr>
	<tr>
		<td class='dataVar'><?php echo $lable_flap_detection ?>:</td>
		<td class='dataVal'><div class='flapdetection<?php echo $flap_detection_enabled ?>'>&nbsp;&nbsp;<?php echo $flap_detection_enabled ?>&nbsp;&nbsp;</div></td>
	</tr>
</table>


<?php
if (!empty($commands))
	echo $commands;