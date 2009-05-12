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
<div class="widget collapsable left" id="extinfo_current" style="width: 370px">
	<div class="widget-header"><?php echo $this->translate->_('Host State Information'); ?></div>
	<table style="border-spacing: 1px">
		<tr>
			<td style="width: 170px"><?php echo $lable_current_status ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($current_status_str).'.png',$current_status_str);?></td>
			<td class="<?php echo $current_status_str ?>"><?php echo $current_status_str ?>
				(<?php echo $lable_for ?> <?php echo $duration ? $duration : $na_str ?>)
			</td>
		</tr>
		<tr>
			<td><?php echo $lable_status_information ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $status_info ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_perf_data ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $perf_data ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_current_attempt ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $current_attempt ?>/<?php echo $max_attempts ?>(<?php echo $state_type ?>)</td>
		</tr>
		<tr>
			<td><?php echo $lable_last_check ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $last_check ? date($date_format_str, $last_check) : $na_str ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_check_type ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($check_type).'.png',$check_type);?></td>
			<td><?php echo $check_type ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_check_latency_duration ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $check_latency ?> / <?php echo $execution_time ?> <?php echo $lable_seconds ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_next_scheduled_check ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $next_check ? date($date_format_str, $next_check) : $na_str ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_last_state_change ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $last_state_change ? date($date_format_str, $last_state_change) : $na_str ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_last_notification ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $last_notification ?>&nbsp;(<?php echo $lable_notifications ?> <?php echo $current_notification_number ?>)</td>
		</tr>
		<tr>
			<td><?php echo $lable_flapping ?></td>
			<td class="status icon">&nbsp;</td>
			<td class="notflapping"><?php echo "$flap_value &nbsp; $percent_state_change_str"; ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_in_scheduled_dt ?></td>
			<td class="status icon">&nbsp;</td>
			<td><div class="downtime inactive"><?php echo $scheduled_downtime_depth ?></div></td>
		</tr>
		<tr>
			<td><?php echo $lable_last_update ?></td>
			<td class="status icon">&nbsp;</td>
			<td><?php echo $last_update ? date($date_format_str, $last_update) : $na_str ?> <?php echo $last_update_ago ?></td>
		</tr>
	</table>
</div>

<div class="widget collapsable left" id="extinfo_checks" style="width: 370px">
<div class="widget-header"><?php echo $this->translate->_('Information'); ?></div>
	<table style="border-spacing: 1px">
		<tr>
			<td style="width: 170px"><?php echo $lable_active_checks ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($active_checks_enabled)) ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_passive_checks ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($passive_checks_enabled)) ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_obsessing ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($obsessing)) ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_notifications ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($notifications_enabled)) ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_event_handler ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($event_handler_enabled)) ?></td>
		</tr>
		<tr>
			<td><?php echo $lable_flap_detection ?></td>
			<td class="status icon"><?php echo html::image('/application/views/themes/default/images/icons/16x16/shield-'.strtolower($active_checks_enabled).'.png',$active_checks_enabled);?></td>
			<td><?php echo ucfirst(strtolower($flap_detection_enabled)) ?></td>
		</tr>
	</table>
</div>

<?php
if (isset($comments))
	echo $comments;
?>