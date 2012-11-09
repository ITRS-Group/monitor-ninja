<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
?>
<div id="page_links">
	<em class="page-links-label"><?php echo _('View').', '.$label_view_for.':'; ?></em>
	<ul>
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
	<div class="clear"></div>
	<hr />
</div>

<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>

<div id="extinfo_host-info">
	<table>
		<tr>
			<th colspan="2" style="padding: 5px 0px" >
				<?php echo !empty($icon_image) ? html::image(Kohana::config('config.logos_path').$icon_image, array('alt' => $icon_image_alt, 'title' => $icon_image_alt, 'style' => 'width: 32px; margin: -5px 7px 0px 0px; float: left')) : ''?>
				<h1 style="display: inline"><?php echo ($main_object_alias ? $main_object_alias.' ('.$main_object.')' : $main_object) ?></h1>
			</td>
		</tr>
		<?php
			if ($type == 'service') {
				echo '<tr>';
				echo '<td style="width: 80px"><strong>'.$lable_on_host.'</strong></td>';
				echo '<td>'.(isset($host) ? $host : '');
				echo isset($host_alias) ? ' ('.$host_alias.')' : '';
				echo !empty($host_link) ? ' ('.$host_link.')' : '';
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td style="width: 80px"><strong><?php echo _('Address');?></strong></td>
			<td><?php echo isset($host_address) ? $host_address : ''; ?></td>
		</tr>
		<?php if ($parents !== false && count($parents)) { ?>
		<tr>
			<td><strong><?php echo _('Parents') ?></strong></td>
			<td>
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
			<td><strong><?php echo $lable_member_of ?></strong></td>
			<td style="white-space: normal"><?php echo !empty($groups) ? implode(', ', $groups) : $no_group_lable ?></td>
		</tr>
		<tr>
			<td><strong><?php echo _('Notifies to') ?></strong></td>
			<td>
				<?php	if (!empty($contactgroups)) {
					$c = 0;
					foreach ($contactgroups as $group => $members) {
						echo '<a title="'._('Contactgroup').': '.$group.', '._('Click to view contacts').'" class="extinfo_contactgroup" id="extinfo_contactgroup_'.(++$c).'">';
						echo $group.'</a>';
				?>
				<table id="extinfo_contacts_<?php echo $c ?>" style="display:none;width:75%" class="extinfo_contacts">
					<tr>
						<th style="border: 1px solid #cdcdcd"><?php echo _('Contact name') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Alias') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Email') ?></th>
						<th style="border: 1px solid #cdcdcd; border-left: 0px"><?php echo _('Pager') ?></th>
					</tr>
					<?php
					foreach ($members as $member) { ?>
					<tr class="<?php echo ($c%2 == 0) ? 'even' : 'odd' ?>">
						<td><?php echo $member['name'] ?></td>
						<td><?php echo $member['alias'] ?></td>
						<td><?php echo $member['email'] ?></td>
						<td><?php echo $member['pager'] ?></td>
					</tr>
					<?php	} ?>
				</table>
					<?php
					}
				} else {
					echo _('No contactgroup');
				}
			?>
			</td>
		</tr>
		<?php if (!empty($notes)) {?>
		<tr>
			<td><strong><?php echo _('Notes') ?></strong></td>
			<td><?php echo $notes ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2" style="padding-top: 7px">
				<?php
					if (!empty($action_url)) {
						echo '<a href="'.$action_url.'" style="border: 0px" target="'.$action_url_target.'">';
						echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => _('Perform extra host actions'),'title' => _('Perform extra host actions'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a href="'.$action_url.'" target="'.$action_url_target.'">'._('Extra actions').'</a>';
					}
					if (!empty($notes_url)) {
						echo '&nbsp; <a target="'.$notes_url_target.'" href="'.$notes_url.'" style="border: 0px">';
						echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => _('View extra host notes'),'title' => _('View extra host notes'),'style' => 'margin: 1px 5px 0px 0px')).'</a>';
						echo '<a target="'.$notes_url_target.'" href="'.$notes_url.'">'._('Extra notes').'</a>';
					}
					foreach ($extra_action_links as $label => $ary) {
						$img_class = isset($ary['img_class']) ? ' class="'.$ary['img_class'].'"' : '';
						echo '&nbsp; <a href="'.$ary['url'].'" style="border: 0px">';
						if (!empty($ary['img']))
							echo '<img src="'.$ary['img'].'" alt="" '.$img_class.' /></a> ';
						echo '<a href="'.$ary['url'].'">'.$label.'</a>'."\n";
					}
				?><div id="pnp_area" style="display:none"></div>
			</td>
		</tr>
	</table>
</div>

<?php $this->session->set('back_extinfo',$back_link);?>


<div class="clear"></div>

<br /><br />
<div class="left width-50" id="extinfo_current">
	<?php
	if (isset($pending_msg)) {
		echo $title."<br /><br />";
		echo $pending_msg;
	} else { ?>
	<table class="ext">
		<tr>
			<th colspan="2"><?php echo $title ?></th>
		</tr>
		<tr>
			<td style="width: 160px" class="dark bt"><?php echo $lable_current_status ?></td>
			<td class="bt">
				<span class="status-<?php echo strtolower($current_status_str) ?>"><span class="icon-12 x12-shield-<?php echo strtolower($current_status_str); ?>"></span><?php echo ucfirst(strtolower($current_status_str)) ?></span>
				(<?php echo $lable_for ?> <?php echo $duration ? time::to_string($duration) : _('N/A') ?>)
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_status_information ?></td>
			<td style="white-space: normal"><?php echo $status_info ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_perf_data ?></td>
			<td style="white-space: normal"><?php echo htmlspecialchars($perf_data) ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_current_attempt ?></td>
			<td><?php echo $current_attempt ?>/<?php echo $max_attempts ?> (<?php echo $state_type ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_check ?></td>
			<td><?php echo $last_check ? date($date_format_str, $last_check) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_check_type ?></td>
			<td>
				<span class="<?php echo strtolower($check_type) ?>"><?php echo ucfirst(strtolower($check_type)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_check_latency_duration ?></td>
			<td><?php echo $check_latency ?> / <?php echo number_format($execution_time, 3) ?> <?php echo $lable_seconds ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_next_scheduled_check ?></td>
			<td><?php echo $next_check && $active_checks_enabled_val ? date($date_format_str, $next_check) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_state_change ?></td>
			<td><?php echo $last_state_change ? date($date_format_str, $last_state_change) : _('N/A') ?></td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_last_notification ?></td>
			<td><?php echo $last_notification ?>&nbsp;(<?php echo $lable_notifications ?> <?php echo $current_notification_number ?>)</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_flapping ?></td>
			<td>
				<span class="flap-<?php echo strtolower($flap_value); ?>"><?php echo ucfirst(strtolower($flap_value)).'</span> '.$percent_state_change_str; ?>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_in_scheduled_dt ?></td>
			<td>
				<span class="downtime-<?php echo strtolower($scheduled_downtime_depth); ?>"><?php echo ucfirst(strtolower($scheduled_downtime_depth)) ?></span>
			</td>
		</tr>
		<tr>
			<td  class="dark" style="width: 160px"><?php echo $lable_active_checks ?></td>
			<td>
				<span class="<?php echo strtolower($active_checks_enabled); ?>"><?php echo ucfirst(strtolower($active_checks_enabled)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_passive_checks ?></td>
			<td>
				<span class="<?php echo strtolower($passive_checks_enabled); ?>"><?php echo ucfirst(strtolower($passive_checks_enabled)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_obsessing ?></td>
			<td>
				<span class="<?php echo strtolower($obsessing); ?>"><?php echo ucfirst(strtolower($obsessing)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_notifications ?></td>
			<td>
				<span class="<?php echo strtolower($notifications_enabled); ?>"><?php echo ucfirst(strtolower($notifications_enabled)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_event_handler ?></td>
			<td>
				<span class="<?php echo strtolower($event_handler_enabled); ?>"><?php echo ucfirst(strtolower($event_handler_enabled)) ?></span>
			</td>
		</tr>
		<tr>
			<td class="dark"><?php echo $lable_flap_detection ?></td>
			<td>
				<span class="<?php echo strtolower($flap_detection_enabled); ?>"><?php echo ucfirst(strtolower($flap_detection_enabled)) ?></span>
			</td>
		</tr>
		<?php if($custom_variables) {
			foreach($custom_variables as $custom_variable) { ?>
				<tr>
					<td class="dark"><?php echo $custom_variable['variable'] ?></td>
					<td><?php echo link::linkify($custom_variable['value']) ?></td>
				</tr>
		<?php
			}
		} ?>
			</table>
<?php } ?>
</div>

<?php
if (!empty($commands))
	echo $commands;
?>

<div class="clear"></div>
<br /><br />

<?php
if (isset($comments))
	echo $comments;
?>
