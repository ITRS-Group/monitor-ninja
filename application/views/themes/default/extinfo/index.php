<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget left w33" id="page_links">
	<ul>
	<li><?php echo $this->translate->_('View').', '.$label_view_for.':'; ?></li>
	<?php
	if (isset($page_links)) {
		foreach ($page_links as $label => $link) {
			?>
			<li><?php echo html::anchor($link, $label) ?></li>
			<?php
		}
	}
	?>
	</ul>
</div>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div class="widget left" id="extinfo_host-info" style="width: 495px">
	<table>
		<tr>
			<td class="white" colspan="2" style="padding: 7px 0px" >
				<?php echo !empty($icon_image) ? html::image('application/media/images/logos/'.$icon_image, array('alt' => $icon_image_alt, 'title' => $icon_image_alt, 'style' => 'width: 32px; margin: -5px 7px 0px 0px; float: left')) : ''?>
				<h1 style="display: inline"><?php echo $main_object_alias.' ('.$main_object.')' ?></h1>
			</td>
		</tr>
		<?php
			if ($type == 'service') {
				echo '<tr>';
				echo '<td class="white" style="width: 80px"><strong>'.$lable_on_host.'</strong></td>';
				echo '<td class="white">'.(isset($host) ? $host : '');
				echo isset($host_alias) ? ' ('.$host_alias.')' : '';
				echo !empty($host_link) ? ' ('.$host_link.')' : '';
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td class="white" style="width: 80px"><strong><?php echo $this->translate->_('Address');?></strong></td>
			<td class="white"><?php echo isset($host_address) ? $host_address : ''; ?></td>
		</tr>
		<?php if ($parents !== false && count($parents)) { ?>
		<tr>
			<td class="white"><strong><?php echo $label_parents ?></strong></td>
			<td class="white">
				<?php
					$cnt = 0;
					foreach ($parents as $parent) {
						$cnt++;
						echo html::anchor('status/service/'.$parent->host_name, $parent->host_name);
						echo $cnt < count($parents) ? ', ': '';
					}
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="white"><strong><?php echo $lable_member_of ?></strong></td>
			<td class="white" style="white-space: normal"><?php echo !empty($groups) ? implode(', ', $groups) : $no_group_lable ?></td>
		</tr>
		<tr>
			<td class="white"><strong><?php echo $label_notifies_to ?></strong></td>
			<td class="white">
				<?php	if (!empty($contactgroups)) {
					$c = 0;
					foreach ($contactgroups as $cgroup) {
						echo '<a title="'.$label_contactgroup.': '.$cgroup.', '.$lable_click_to_view.'" class="extinfo_contactgroup" id="extinfo_contactgroup_'.$c.'">';
						echo $cgroup.'</a>';
				?>
				<table id="extinfo_contacts_<?php echo $c ?>" style="display:none;" class="extinfo_contacts">
					<tr>
						<th style="border: 1px solid #cdcdcd"><?php echo $lable_contact_name ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo $lable_contact_alias ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo $lable_contact_email ?></th>
					</tr>
					<?php	foreach ($contacts[$cgroup] as $cmember) { ?>
					<tr class="<?php echo ($c%2 == 0) ? 'even' : 'odd' ?>">
						<td><?php echo $cmember->contact_name ?></td>
						<td><?php echo $cmember->alias ?></td>
						<td><?php echo $cmember->email ?></td>
					</tr>
					<?php	} ?>
				</table>
					<?php	# needed to assign unique IDs to extinfo_contacts_ table
						$c++; 	# and extinfo_contactgroup_ table cells
					}
				} else {
					echo $label_no_contactgroup;
				}
			?>
			</td>
		</tr>
		<?php if (!empty($notes)) {?>
		<tr>
			<td class="white"><strong><?php echo $label_notes ?></strong></td>
			<td class="white"><?php echo $notes ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td class="white" colspan="2"style="padding-top: 7px">
				<?php
					if (!empty($action_url)) {
						echo '<a href="'.$action_url.'" style="border: 0px" target="_blank">';
						echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => $this->translate->_('Perform extra host actions'),'title' => $this->translate->_('Perform extra host actions'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a href="'.$action_url.'" target="_blank">'.$label_action_url.'</a>';
					}
					if (!empty($notes_url)) {
						echo '&nbsp; <a target="_blank" href="'.$notes_url.'" style="border: 0px">';
						echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => $this->translate->_('View extra host notes'),'title' => $this->translate->_('View extra host notes'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a target="_blank" href="'.$notes_url.'">'.$label_notes_url.'</a>';
					}
					foreach ($extra_action_links as $label => $ary) {
						echo '&nbsp; <a href="'.$ary['url'].'" style="border: 0px">';
						if (!empty($ary['img']))
					}
				?><div id="pnp_area" style="display:none"></div>
			</td>
		</tr>
	</table>
</div>

<?php $this->session->set('back_extinfo',$back_link);?>


<div style="clear:both"></div>
<div class="widget left" id="extinfo_current" style="width: 495px">
	<?php
	if (isset($pending_msg)) {
		echo $title."<br /><br />";
		echo $pending_msg;
	} else { ?>
	<table class="ext">
		<tr>
			<th colspan="2" class="headerNone" style="border: 0px"><?php echo $title ?></th>
		</tr>
		<tr>
			<td style="width: 160px" class="dark bt"><?php echo $lable_current_status ?></td>
			<td class="bt">
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($current_status_str).'.png'), array('alt' => $current_status_str, 'style' => 'margin-bottom: -2px; margin-right: 2px'));?>
				<?php echo ucfirst(strtolower($current_status_str)) ?>
				(<?php echo $lable_for ?> <?php echo $duration ? time::to_string($duration) : $na_str ?>)
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_status_information ?></td>
			<td style="white-space: normal"><?php echo $status_info ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_perf_data ?></td>
			<td style="white-space: normal"><?php echo $perf_data ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_current_attempt ?></td>
			<td><?php echo $current_attempt ?>/<?php echo $max_attempts ?> (<?php echo $state_type ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_check ?></td>
			<td><?php echo $last_check ? date($date_format_str, $last_check) : $na_str ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_check_type ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($check_type).'.png'),array('alt' => $check_type, 'style' => 'margin-bottom: -2px; margin-right: 2px'));?>
				<?php echo ucfirst(strtolower($check_type)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_check_latency_duration ?></td>
			<td><?php echo $check_latency ?> / <?php echo number_format($execution_time, 3) ?> <?php echo $lable_seconds ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_next_scheduled_check ?></td>
			<td><?php echo $next_check ? date($date_format_str, $next_check) : $na_str ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_state_change ?></td>
			<td><?php echo $last_state_change ? date($date_format_str, $last_state_change) : $na_str ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_notification ?></td>
			<td><?php echo $last_notification ?>&nbsp;(<?php echo $lable_notifications ?> <?php echo $current_notification_number ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_flapping ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/flapping-'.str_replace('/','',strtolower($flap_value)).'.png'),$flap_value);?>
				<?php echo ucfirst(strtolower($flap_value)).' '.$percent_state_change_str; ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_in_scheduled_dt ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/sched-downtime-'.strtolower($scheduled_downtime_depth).'.png'),$scheduled_downtime_depth);?>
				<?php echo ucfirst(strtolower($scheduled_downtime_depth)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_update ?></td>
			<td><?php echo $last_update ? date($date_format_str, $last_update) : $na_str ?> <?php echo $last_update_ago ?></td>
		</tr>
		<tr>
			<td  class="dark" style="width: 160px"><?php echo $lable_active_checks ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($active_checks_enabled).'.png'),$active_checks_enabled);?>
				<?php echo ucfirst(strtolower($active_checks_enabled)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_passive_checks ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($passive_checks_enabled).'.png'),$passive_checks_enabled);?>
				<?php echo ucfirst(strtolower($passive_checks_enabled)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_obsessing ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($obsessing).'.png'),$obsessing);?>
				<?php echo ucfirst(strtolower($obsessing)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_notifications ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($notifications_enabled).'.png'),$notifications_enabled);?>
				<?php echo ucfirst(strtolower($notifications_enabled)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_event_handler ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($event_handler_enabled).'.png'),$event_handler_enabled);?>
				<?php echo ucfirst(strtolower($event_handler_enabled)) ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_flap_detection ?></td>
			<td>
				<?php echo html::image($this->add_path('icons/12x12/shield-'.strtolower($flap_detection_enabled).'.png'),$flap_detection_enabled);?>
				<?php echo ucfirst(strtolower($flap_detection_enabled)) ?>
			</td>
		</tr>
	</table>
	<?php } ?>
</div>

<?php
if (!empty($commands))
	echo $commands;
?>

<?php
if (isset($comments))
	echo $comments;
?>
