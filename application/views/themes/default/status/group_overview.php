<?php defined('SYSPATH') OR die('No direct access allowed.');?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
<div class="widget left w32" id="page_links">
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

	<div id="filters" class="left">
	<?php
	if (isset($filters) && !empty($filters)) {
		echo $filters;
	}
	?>
	</div>
</div>

<div class="widget left w98" id="status_group-overview">
<?php if (nacoma::link()===true)
	echo sprintf($this->translate->_('Add new %sgroup'), ucfirst($grouptype)).': &nbsp;'.nacoma::link('configuration/configure/'.$grouptype.'group/', 'icons/16x16/nacoma.png', sprintf($this->translate->_('Add new %sgroup'), $grouptype));
	echo (isset($pagination)) ? $pagination : '';
	$j = 0;

	echo form::open('command/multi_action');
	echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo $this->translate->_('Select Multiple Items') ?></a>
	<?php
	# make sure we have something to iterate over
	if (!empty($group_details))
	foreach ($group_details as $group) { ?>

		<table class="group_overview_table">
			<caption>
			<?php echo html::anchor('status/'.$grouptype.'group/'.$group->groupname.'?style=detail', $group->group_alias) ?>
			(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.$group->groupname, $group->groupname) ?>)
			<?php if (nacoma::link()===true)
				echo nacoma::link('configuration/configure/'.$grouptype.'group/'.urlencode($group->groupname), 'icons/16x16/nacoma.png', sprintf($this->translate->_('Configure this %sgroup'), $grouptype));?>
		</caption>
			<tr>
				<th><em><?php echo $this->translate->_('Status');?></em></th>
				<th class="item_select">&nbsp;</th>
				<th colspan="2"><?php echo $lable_host ?></th>
				<th class="no-sort"><?php echo $lable_services ?></th>
				<th class="no-sort"><?php echo $lable_actions ?></th>
			</tr>
			<?php $i=0; if (!empty($group->hostinfo))
				foreach ($group->hostinfo as $host => $details) { ?>
			<tr class="<?php echo ($i % 2 == 0) ? 'even' : 'odd' ?>">
				<td class="icon bl <?php echo strtolower($details['state_str']); ?>"><em><?php echo $details['state_str'];?></em></td>
				<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $host); ?></td>
				<td style="width: 180px"><?php echo $details['status_link'] ?></td>
				<td class="icon"><?php echo !empty($details['host_icon']) ? $details['host_icon'] : '' ?></td>
				<td>
					<?php if (!empty($group->service_states[$host]))
						foreach ($group->service_states[$host] as $svc_state) {
							echo html::image($this->add_path('icons/12x12/shield-'.strtolower(str_replace('miniStatus','',$svc_state['class_name'])).'.png'), array('alt' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'title' => strtolower(str_replace('miniStatus','',$svc_state['class_name'])), 'style' => 'margin-bottom: -2px'));
							echo '&nbsp; '.strtolower(ucfirst($svc_state['status_link'])).' &nbsp; ';
						}
					?>
				</td>
				<td style="text-align: left; width: 133px">
					<?php
						echo !empty($svc_state['nacoma_link']) ? $svc_state['nacoma_link'].'&nbsp;' : '';
						echo !empty($svc_state['pnp_link']) ? $svc_state['pnp_link'].'&nbsp;' : '';
						echo !empty($svc_state['extinfo_link']) ? $svc_state['extinfo_link'].'&nbsp;' : '';
						echo !empty($svc_state['statusmap_link']) ? $svc_state['statusmap_link'].'&nbsp;' : '';
						echo !empty($svc_state['svc_status_link']) ? $svc_state['svc_status_link'].'&nbsp;' : '';
						echo !empty($details['action_link']) ? $details['action_link'].'&nbsp;' : '';
						echo !empty($details['notes_link']) ? $details['notes_link'].'&nbsp;' : '';
					?>
				</td>
			</tr>
			<?php $i++; } ?>
		</table>

<?php $j++; }
	else { ?>
		<table class="group_overview_table">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th><?php echo $lable_host ?></th>
					<th class="no-sort"><?php echo $lable_services ?></th>
					<th class="no-sort"><?php echo $lable_actions ?></th>
				</tr>
			</thead>
			<tbody>
			<tr class="even">
				<td colspan="4"><?php echo $error_message ?></td>
			</tr>
			</tbody>
		</table>

<?php } ?>
<?php echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select', 'id' => 'multi_action_select'),
		array(
			'' => $this->translate->_('Select Action'),
			'SCHEDULE_HOST_DOWNTIME' => $this->translate->_('Schedule Downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM' => $this->translate->_('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT' => $this->translate->_('Remove Problem Acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS' => $this->translate->_('Disable Host Notifications'),
			'ENABLE_HOST_NOTIFICATIONS' => $this->translate->_('Enable Host Notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS' => $this->translate->_('Disable Notifications For All Services'),
			'DISABLE_HOST_CHECK' => $this->translate->_('Disable Active Checks'),
			'ENABLE_HOST_CHECK' => $this->translate->_('Enable Active Checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => $this->translate->_('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
