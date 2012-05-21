<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
$state_map = array(
	Current_status_Model::SERVICE_OK => 'ok',
	Current_status_Model::SERVICE_WARNING => 'warning',
	Current_status_Model::SERVICE_CRITICAL => 'critical',
	Current_status_Model::SERVICE_UNKNOWN => 'unknown');
?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<div class="widget left w32" id="page_links">
		<ul>
		<li><?php echo _('View').', '.$label_view_for.':'; ?></li>
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
<div class="clearservice"> </div>

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
    <div class="clearservice"> </div>
</div>

<div class="widget left w98" id="status_group-grid">
<?php echo (isset($pagination)) ? $pagination : ''; ?>
<?php echo form::open('command/multi_action'); ?>
<?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a>

<?php if (count($group_details) && !empty($group_details))
	foreach ($group_details as $hostgroup) { ?>
	<table class="group_grid_table">
		<caption>
			<?php
				if (nacoma::link()===true)
					echo nacoma::link('configuration/configure/hostgroup/'.urlencode($hostgroup->hostgroup_name), 'icons/16x16/nacoma.png', _('Configure this hostgroup')).' &nbsp;';
				echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', html::specialchars($hostgroup->alias));
				echo '<span>('.html::anchor('extinfo/details/?type=hostgroup&host='.urlencode($hostgroup->hostgroup_name), html::specialchars($hostgroup->hostgroup_name)).')</span>';
			?>
		</caption>
		<tr>
			<th class="no-sort">&nbsp;</th>
			<th class="item_select">&nbsp;</th>
			<th class="no-sort"><?php echo $label_host ?></th>
			<th class="no-sort"><?php echo $label_services ?></th>
			<th class="no-sort"><?php echo $label_actions ?></th>
		</tr>
		<tbody>
		<?php
		$i = 0;
		$prev_host = false;
		$next = false;
		$tmp = 0;
		$j = 0;

		$hosts = Hostgroup_Model::get_group_hosts($hostgroup->hostgroup_name);
		foreach ($hosts as $host) {
		?>
		<tr class="<?php echo ($i%2 == 0) ? 'odd' : 'even' ?>">
			<td class="icon bl">
				<?php
					if (!empty($host['icon_image'])) {
						echo html::anchor('extinfo/details/?type=host&host='.$host['name'], html::image('application/media/images/logos/'.$host['icon_image'], array('style' => 'height: 16px; width: 16px', 'alt' => $host['icon_image_alt'], 'title' => $host['icon_image_alt'])),array('style' => 'border: 0px'));
					} ?>
			</td>
			<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $host['name']); ?></td>
			<td style="white-space: normal; width: 180px"><?php echo html::anchor('extinfo/details/?type=host&host='.$host['name'], html::specialchars($host['name'])) ?></td>
			<td style="white-space: normal; line-height: 20px">
			<?php

			$service_map = array('pending' => array(), 'ok' => array(), 'warning' => array(), 'critical' => array(), 'unknown' => array());
			foreach ($host['services_with_state'] as $service) {
				$state_name = '';
				if ($service[2] == 0) {
					$state_name = 'pending';
				} else {
					$state_name = $state_map[$service[1]];
				}
				$service_map[$state_name][] = $service[0];
			}
			foreach ($service_map as $service_state => $services) {
				if (empty($services))
					continue;
				?>
				<img src="<?php echo ninja::add_path('icons/12x12/shield-'.$service_state.'.png') ?>" alt="<?php echo $service_state ?>" title="<?php echo $service_state ?>" class="status=<?php echo $service_state ?>" />
				<?php foreach ($services as $service) { ?>
					<a href="extinfo/details/?type=service&host=<?php echo urlencode($host['name']) ?>&service=<?php echo urlencode($service)?>" class="status-<?php echo $service_state ?>"><?php echo $service ?></a>
				<?php } ?>
				<br />
			<?php } ?>
			</td>
			<td style="text-align: left; width: 133px">
				<?php
					if (isset($nacoma_path))
						echo html::anchor('configuration/configure/?type=host&name='.urlencode($host['name']), html::image($icon_path.'nacoma.png', array('alt' => $label_nacoma, 'title' => $label_nacoma)),array('style' => 'border: 0px')).'&nbsp;';
					if (isset($pnp_path) && $host['pnpgraph_present'])
						echo '<a href="'.$pnp_path.'host='.$host['name'].'&srv=_HOST_" style="border: 0px">'.html::image($icon_path.'pnp.png', array('alt' => $label_pnp, 'title' => $label_pnp, 'class' => 'pnp_graph_icon')).'</a>&nbsp;';
					echo html::anchor('extinfo/details/?type=host&host='.$host['name'], html::image($icon_path.'extended-information.gif', array('alt' => $label_host_extinfo, 'title' => $label_host_extinfo)), array('style' => 'border: 0px')).'&nbsp;';
					if ( Kohana::config('config.nagvis_path') ) {
						echo html::anchor('statusmap/host/'.$host['name'], html::image($icon_path.'locate-host-on-map.png', array('alt' => $label_status_map, 'title' => $label_status_map)), array('style' => 'border: 0px')).'&nbsp;';
					}
					echo html::anchor('status/host/?host='.urlencode($host['name']), html::image($icon_path.'service-details.gif', array('alt' => $label_service_status, 'title' => $label_service_status)), array('style' => 'border: 0px')).'&nbsp;';
					if (!empty($host['action_url'])) {
						echo '<a href="'.nagstat::process_macros($host['action_url'], $host).'" style="border: 0px" target="'.$action_url_target.'">';
						echo html::image($this->add_path('icons/16x16/host-actions.png'), array('alt' => _('Perform extra host actions'), 'title' => _('Perform extra host actions')));
						echo '</a>&nbsp;';
					}
					if (!empty($host['notes_url'])) {
						echo '<a href="'.nagstat::process_macros($host['notes_url'], $host).'" style="border: 0px" target="'.$notes_url_target.'">';
						echo html::image($this->add_path('icons/16x16/host-notes.png'), array('alt' => _('View extra host notes'), 'title' => _('View extra host notes')));
						echo '</a>';
					}

				?>
			</td>
			</tr> <?php } ?>
		</tbody>
	</table>

<?php
}	# end each group
else { ?>
	<table class="group_grid_table">
		<thead>
		<tr>
			<th class="no-sort"><?php echo $label_host ?></th>
			<th class="no-sort"><?php echo $label_services ?></th>
			<th class="no-sort"><?php echo $label_actions ?></th>
		</tr>
		</thead>
		<tbody>
		<tr class="even">
			<td colspan="3"><?php echo $error_message ?></td>
		</tr>
		</tbody>
	</table>
	<?php
} ?>
<?php echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select', 'id' => 'multi_action_select'),
		array(
			'' => _('Select action'),
			'SCHEDULE_HOST_DOWNTIME' => _('Schedule downtime'),
			'ACKNOWLEDGE_HOST_PROBLEM' => _('Acknowledge'),
			'REMOVE_HOST_ACKNOWLEDGEMENT' => _('Remove problem acknowledgement'),
			'DISABLE_HOST_NOTIFICATIONS' => _('Disable host notifications'),
			'ENABLE_HOST_NOTIFICATIONS' => _('Enable host notifications'),
			'DISABLE_HOST_SVC_NOTIFICATIONS' => _('Disable notifications for all services'),
			'DISABLE_HOST_CHECK' => _('Disable active checks'),
			'ENABLE_HOST_CHECK' => _('Enable active checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => _('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?>
	<br /><span id="multi_object_submit_progress" class="item_select"></span>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>

