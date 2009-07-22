<div class="widget left w33" id="page_links">
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
</div>

<?php if (!empty($action_url)) { ?>
<a href="<?php echo $action_url ?>" style="border: 0px">
			<?php echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => $this->translate->_('Perform extra host actions'),'title' => $this->translate->_('Perform extra host actions')))?></a>
<br />
<strong><?php echo $label_action_url ?></strong>
<?php } ?>
<br />

<?php if (!empty($notes_url)) { ?>
<a href="<?php echo $notes_url ?>" style="border: 0px">
			<?php echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => $this->translate->_('View extra host notes'),'title' => $this->translate->_('View extra host notes')))?></a>
<br />
<strong><?php echo $label_notes_url ?></strong>
<?php } ?>

<div class="widget left w98">
<table style="border-spacing: 1px; background-color: #dcdccd">
	<caption><?php echo $label_grouptype ?> <?php echo $label_commands.' '.$this->translate->_('for').': '.$group_alias.' ('.$groupname.')'; ?></caption>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/downtime.png'), array('alt' => $label_schedule_downtime_hosts.' '.$label_grouptype, 'title' => $label_schedule_downtime_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/downtime.png'), array('alt' => $label_schedule_downtime_services.' '.$label_grouptype, 'title' => $label_schedule_downtime_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_schedule_downtime_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_schedule_downtime_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify.png'), array('alt' => $label_enable.' '.$label_notifications_hosts.' '.$label_grouptype, 'title' => $label_enable.' '.$label_notifications_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => $label_disable.' '.$label_notifications_hosts.' '.$label_grouptype, 'title' => $label_disable.' '.$label_notifications_hosts.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_hosts.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_notifications_hosts." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify.png'), array('alt' => $label_enable.' '.$label_notifications_services.' '.$label_grouptype, 'title' => $label_enable.' '.$label_notifications_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_notifications_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/notify-disabled.png'), array('alt' => $label_disable.' '.$label_notifications_services.' '.$label_grouptype, 'title' => $label_disable.' '.$label_notifications_services.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_notifications_services.'&'.strtolower($label_grouptype).'='.$groupname, $label_notifications_services." ".$label_grouptype); ?>
		</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/enabled.png'), array('alt' => $label_enable.' '.$label_active_checks.' '.$label_grouptype, 'title' => $label_enable.' '.$label_active_checks.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_enable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_enable." ".$label_active_checks." ".$label_grouptype); ?>
	</td>
	</tr>
	<tr>
		<td class="status icon">
			<?php echo html::image($this->add_path('icons/16x16/disabled.png'), array('alt' => $label_disable.' '.$label_active_checks.' '.$label_grouptype, 'title' => $label_disable.' '.$label_active_checks.' '.$label_grouptype)); ?>
		</td>
		<td>
			<?php echo html::anchor('command/submit?cmd_typ='.$cmd_disable_active_checks.'&'.strtolower($label_grouptype).'='.$groupname, $label_disable." ".$label_active_checks." ".$label_grouptype); ?>
		</td>
	</tr>
</table>
</div>
