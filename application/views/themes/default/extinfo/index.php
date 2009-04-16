<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div class="widget left w98" id="extinfo_info">
	<div id="extinfo_object_main_info">
		<strong><?php echo ucfirst($lable_type) ?></strong>:
		<?php echo $main_object ?><br />
		<?php echo !empty($main_object_alias) ? '<strong>Alias</strong>: '.$main_object_alias : '' ?><br />
		<strong><?php echo $this->translate->_('IP address');?></strong> <?php echo isset($host_address) ? $host_address : ''; ?><br />
		<?php
			if ($type == 'service') {
				echo $lable_on_host.'<br />';
				echo (isset($host) ? ucfirst($host) : '').'<br />';
				echo isset($host_alias) ? '('.$host_alias.')' : '';
				echo !empty($host_link) ? '('.$host_link.')' : '';
			}
		?>
		<strong><?php echo $lable_member_of ?></strong>:
		<?php echo !empty($groups) ? implode(', ', $groups) : $no_group_lable ?><br />
	</div>
</div>

<?php
if (!empty($commands))
	echo $commands;
?>
<div class="widget collapsable left w48" id="extinfo_current">
	<table style="border-spacing: 1px">
		<tr class="odd">
			<td><?php echo $lable_current_status ?></td>
			<td class="<?php echo $current_status_str ?>"><?php echo $current_status_str ?>
				(<?php echo $lable_for ?> <?php echo $duration ? $duration : $na_str ?>)
			</td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_status_information ?></td>
			<td><?php echo $status_info ?></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_perf_data ?></td>
			<td><?php echo $perf_data ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_current_attempt ?></td>
			<td><?php echo $current_attempt ?>/<?php echo $max_attempts ?>(<?php echo $state_type ?>)</td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_last_check ?></td>
			<td><?php echo $last_check ? date($date_format_str, $last_check) : $na_str ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_check_type ?></td>
			<td><?php echo $check_type ?></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_check_latency_duration ?></td>
			<td><?php echo $check_latency ?> / <?php echo $execution_time ?> <?php echo $lable_seconds ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_next_scheduled_check ?></td>
			<td><?php echo $next_check ? date($date_format_str, $next_check) : $na_str ?></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_last_state_change ?></td>
			<td><?php echo $last_state_change ? date($date_format_str, $last_state_change) : $na_str ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_last_notification ?></td>
			<td><?php echo $last_notification ?>&nbsp;(<?php echo $lable_notifications ?> <?php echo $current_notification_number ?>)</td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_flapping ?></td>
			<td class="notflapping"><?php echo "$flap_value &nbsp; $percent_state_change_str"; ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_in_scheduled_dt ?></td>
			<td><div class="downtime inactive"><?php echo $scheduled_downtime_depth ?></div></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_last_update ?></td>
			<td><?php echo $last_update ? date($date_format_str, $last_update) : $na_str ?> <?php echo $last_update_ago ?></td>
		</tr>
	</table>
</div>

<div class="widget collapsable left w49" id="extinfo_checks">
	<table style="border-spacing: 1px">
		<tr class="odd">
			<td><?php echo $lable_active_checks ?></td>
			<td class="<?php echo $active_checks_enabled ?>"><?php echo $active_checks_enabled ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_passive_checks ?></td>
			<td class="<?php echo $passive_checks_enabled ?>"><?php echo $passive_checks_enabled ?></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_obsessing ?></td>
			<td class="<?php echo $obsessing ?>"><?php echo $obsessing ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_notifications ?></td>
			<td class="<?php echo $notifications_enabled ?>"><?php echo $notifications_enabled ?></td>
		</tr>
		<tr class="odd">
			<td><?php echo $lable_event_handler ?></td>
			<td class="<?php echo $event_handler_enabled ?>"><?php echo $event_handler_enabled ?></td>
		</tr>
		<tr class="even">
			<td><?php echo $lable_flap_detection ?></td>
			<td class="<?php echo $flap_detection_enabled ?>"><?php echo $flap_detection_enabled ?></td>
		</tr>
	</table>
</div>
