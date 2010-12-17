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

	$pnp4nagios_path = Kohana::config('config.pnp4nagios_path');
	$nacoma_link = nacoma::link();
	$t = $this->translate;
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
	$check = false;
	$i = 0;
	if (!empty($group_details))
	foreach ($group_details as $group_info) {
		$groupname = $group_info->{$grouptype.'group_name'};
		$group_res = Group_Model::group_overview($grouptype, $groupname, $this->hostprops, $this->serviceprops, $this->hoststatustypes, $this->servicestatustypes); ?>
		<table class="group_overview_table">
			<caption>
			<?php echo html::anchor('status/'.$grouptype.'group/'.$groupname.'?style=detail', $group_info->alias) ?>
			(<?php echo html::anchor('extinfo/details/'.$grouptype.'group/'.$groupname, $groupname) ?>)
			<?php if ($nacoma_link===true)
				echo nacoma::link('configuration/configure/'.$grouptype.'group/'.urlencode($groupname), 'icons/16x16/nacoma.png', sprintf($this->translate->_('Configure this %sgroup'), $grouptype));?>
		</caption>
			<tr>
				<th><em><?php echo $this->translate->_('Status');?></em></th>
				<th class="item_select">&nbsp;</th>
				<th colspan="2"><?php echo $lable_host ?></th>
				<th class="no-sort"><?php echo $lable_services ?></th>
				<th class="no-sort"><?php echo $lable_actions ?></th>
			</tr>
			<?php
		if ($group_res !== false)
			foreach ($group_res as $group ) {
				if ($group === false) {
					continue;
				}
				$host_icon = false;
				if (!empty($group->icon_image)) {
					$host_icon = html::image('application/media/images/logos/'.$group->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $group->icon_image_alt, 'title' => $group->icon_image_alt));
				} ?>
			<tr class="<?php echo ($i % 2 == 0) ? 'even' : 'odd' ?>">
				<td class="icon bl <?php echo strtolower(Current_status_Model::status_text($group->current_state, 'host')); ?>"><em><?php echo Current_status_Model::status_text($group->current_state, 'host');?></em></td>
				<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $group->host_name); ?></td>
				<td style="width: 180px"><?php echo html::anchor('status/service/'.urlencode($group->host_name).'?hoststatustypes='.$this->hoststatustypes.'&servicestatustypes='.(int)$servicestatustypes, html::specialchars($group->host_name), array('title' => $group->address)) ?></td>
				<td class="icon"><?php echo !empty($host_icon) ? $host_icon : '' ?></td>
				<td><?php
					if (!empty($group->services_ok)) {
						echo html::image($this->add_path('icons/12x12/shield-ok.png'), array('alt' => '', 'title' => $this->translate->_('OK'), 'class' => 'status-default'));
						echo html::anchor('status/service/'.urlencode($group->host_name).'?servicestatustypes='.nagstat::SERVICE_OK.'&hoststatustypes='.$this->hoststatustypes.'&hostproperties='.$this->hostprops.'&serviceprops='.$this->serviceprops,
							$group->services_ok.' '.$this->translate->_('OK'), array('class' => 'status-ok')).' &nbsp; ';
					}
					if (!empty($group->services_warning)) {
						echo html::image($this->add_path('icons/12x12/shield-warning.png'), array('alt' => '', 'title' => $this->translate->_('Warning'), 'class' => 'status-default'));
						echo html::anchor('status/service/'.urlencode($group->host_name).'?servicestatustypes='.nagstat::SERVICE_WARNING.'&hoststatustypes='.$this->hoststatustypes.'&hostproperties='.$this->hostprops.'&serviceprops='.$this->serviceprops,
							$group->services_warning.' '.$this->translate->_('Warning'), array('class' => 'status-warning')).' &nbsp; ';
					}
					if (!empty($group->services_critical)) {
						echo html::image($this->add_path('icons/12x12/shield-critical.png'), array('alt' => '', 'title' => $this->translate->_('Critical'), 'class' => 'status-default'));
						echo html::anchor('status/service/'.urlencode($group->host_name).'?servicestatustypes='.nagstat::SERVICE_CRITICAL.'&hoststatustypes='.$this->hoststatustypes.'&hostproperties='.$this->hostprops.'&serviceprops='.$this->serviceprops,
							$group->services_critical.' '.$this->translate->_('Critical'), array('class' => 'status-critical')).' &nbsp; ';
					}
					if (!empty($group->services_unknown)) {
						echo html::image($this->add_path('icons/12x12/shield-unknown.png'), array('alt' => '', 'title' => $this->translate->_('Unknown'), 'class' => 'status-default'));
						echo html::anchor('status/service/'.urlencode($group->host_name).'?servicestatustypes='.nagstat::SERVICE_UNKNOWN.'&hoststatustypes='.$this->hoststatustypes.'&hostproperties='.$this->hostprops.'&serviceprops='.$this->serviceprops,
							$group->services_unknown.' '.$this->translate->_('Unknown'), array('class' => 'status-unknown')).' &nbsp; ';
					}
					if (!empty($group->services_pending)) {
						echo html::image($this->add_path('icons/12x12/shield-pending.png'), array('alt' => '', 'title' => $this->translate->_('Pending'), 'class' => 'status-default'));
						echo html::anchor('status/service/'.urlencode($group->host_name).'?servicestatustypes='.nagstat::SERVICE_PENDING.'&hoststatustypes='.$this->hoststatustypes.'&hostproperties='.$this->hostprops.'&serviceprops='.$this->serviceprops,
							$group->services_pending.' '.$this->translate->_('Pending'), array('class' => 'status-pending')).' &nbsp; ';
					} ?>
				</td>
				<td style="text-align: left; width: 133px">
					<?php
					if ($nacoma_link===true) {
						$lable_nacoma = $t->_('Configure this host using NACOMA (Nagios Configuration Manager)');
						echo html::anchor('configuration/configure/host/'.urlencode($group->host_name), html::image($this->img_path('icons/16x16/nacoma.png'), array('alt' => $lable_nacoma, 'title' => $lable_nacoma)), array('style' => 'border: 0px'));
					}

					if ($pnp4nagios_path!==false && pnp::has_graph($group->host_name)) {
						echo '<a href="'.url::base(true) . 'pnp/?host='.urlencode($group->host_name).'&srv=_HOST_" style="border: 0px">'.html::image($this->img_path('icons/16x16/pnp.png'), array('alt' => $t->_('Show performance graph'), 'title' => $t->_('Show performance graph'), 'class' => 'pnp_graph_icon')).'</a>';
					}

					$lable_extinfo_host = $t->_('View Extended Information For This Host');
					echo html::anchor('extinfo/details/host/'.urlencode($group->host_name), html::image($this->img_path('icons/16x16/extended-information.gif'), array('alt' => $lable_extinfo_host, 'title' => $lable_extinfo_host)), array('style' => 'border: 0px') );

					$lable_statusmap = $t->_('Locate Host On Map');
					echo html::anchor('statusmap/host/'.urlencode($group->host_name), html::image($this->img_path('icons/16x16/locate-host-on-map.png'), array('alt' => $lable_statusmap, 'title' => $lable_statusmap)), array('style' => 'border: 0px') );

					$lable_svc_status = $t->_('View Service Details For This Host');
					echo html::anchor('status/service/'.urlencode($group->host_name), html::image($this->img_path('icons/16x16/service-details.gif'), array('alt' => $lable_svc_status, 'title' => $lable_svc_status)), array('style' => 'border: 0px') );

					if (!is_null($group->action_url)) {
						$lable_host_action = $t->_('Perform Extra Host Actions');
						echo '<a href="'.nagstat::process_macros($group->action_url, $group).'" style="border: 0px">'.html::image($this->img_path('icons/16x16/host-actions.png'), array('alt' => $lable_host_action, 'title' => $lable_host_action)).'</a>';
					}

					if (!is_null($group->notes_url)) {
						$lable_host_notes = $t->_('View Extra Host Notes');
						echo '<a href="'.nagstat::process_macros($group->notes_url, $group).'" style="border: 0px">'.html::image($this->img_path('icons/16x16/host-notes.png'), array('alt' => $lable_host_notes, 'title' => $lable_host_notes)).'</a>';
					} ?>
				</td>
			</tr>
			<?php $i++;	} ?>
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
