 <?php defined('SYSPATH') OR die('No direct access allowed.');
$label_na = $this->translate->_('N/A');
?>

<div class="widget left w98" id="search_result">
<?php echo help::render('search_help') ?>&nbsp;
<?php echo isset($no_data) ? $no_data : '<strong>'.$limit_str.'</strong><br><br>';
# show host data if available
if (isset($host_result) ) {
	if (isset($host_pagination)) {?><div id="host_pagination"><?php echo $host_pagination ?></div><?php } ?>
<?php echo form::open('command/multi_action'); ?>
<table id="host_table">
	<caption><?php echo $this->translate->_('Host results for').': &quot;'.$query.'&quot'; ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo $this->translate->_('Select Multiple Items') ?></a><br /></caption>
	<tr>
		<th class="header"><em><?php echo $this->translate->_('Status'); ?></em></th>
		<th class="item_select"><input type="checkbox" class="select_all_items" title="'.$this->translate->_('Click to select/unselect all').'"></th>
		<th class="header"><?php echo $this->translate->_('Host'); ?></th>
		<th class="no-sort"><?php echo $this->translate->_('Actions'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<th class="header" style="width: 70px"><?php echo $this->translate->_('Address'); ?></th>
		<th class="header"><?php echo $this->translate->_('Status Information'); ?></th>
		<th class="header"><?php echo $this->translate->_('Display Name'); ?></th>
	</tr>
<?php	$i = 0; foreach ($host_result as $host) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="icon bl <?php echo strtolower(Current_status_Model::status_text($host->current_state)); ?>"><em><?php echo Current_status_Model::status_text($host->current_state); ?></em></td>
		<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $host->host_name); ?></td>
		<td>
			<div style="float: left"><?php echo html::anchor('extinfo/details/host/'.$host->host_name, $host->host_name) ?></div>
			<?php	$host_comments = Comment_Model::count_comments($host->host_name);
				if ($host_comments!=0) { ?>
			<span style="float: right">
				<?php echo html::anchor('extinfo/details/host/'.$host->host_name.'#comments',
						html::image($this->add_path('icons/16x16/add-comment.png'),
						array('alt' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments),
						'title' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments))), array('style' => 'border: 0px')); ?>
			</span>
			<?php } ?>
			<div style="float: right"><?php
				$properties = 0;
				if ($host->problem_has_been_acknowledged) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px'));
					$properties++;
				}
				if (empty($host->notifications_enabled)) {
					$properties += 2;
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $this->translate->_('Notification enabled'), 'title' => $this->translate->_('Notification disabled'))), array('style' => 'border: 0px'));
				}
				if (!$host->active_checks_enabled) {
					$properties += 4;
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks disabled'))), array('style' => 'border: 0px'));
				}
				if (isset($host->is_flapping) && $host->is_flapping) {
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
				}
				if ($host->scheduled_downtime_depth > 0) {
					$properties += 8;
					echo html::anchor('extinfo/details/host/'.$host->host_name, html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px'));
				}
				if ($host->current_state == Current_status_Model::SERVICE_CRITICAL || $host->current_state == Current_status_Model::SERVICE_UNKNOWN || $host->current_state == Current_status_Model::SERVICE_WARNING ) {
					$properties += 16;
				}

				 ?>
			</div><span class="obj_prop" style="display:none"><?php echo $properties ?></span>
		</td>
		<td style="text-align: left">
			<?php
				echo html::anchor('status/service/'.$host->host_name,html::image($this->add_path('icons/16x16/service-details.gif'), $this->translate->_('View service details for this host')), array('style' => 'border: 0px')).' &nbsp;';
				if (isset ($nacoma_link))
					echo html::anchor($nacoma_link.'host/'.$host->host_name, html::image($this->img_path('icons/16x16/nacoma.png'), array('alt' => $label_nacoma, 'title' => $label_nacoma)), array('style' => 'border: 0px')).' &nbsp;';
				if (Kohana::config('config.pnp4nagios_path')!==false)
					echo (pnp::has_graph($host->host_name))  ? '<a href="' . url::site() . 'pnp/?host='.urlencode($host->host_name).'&srv=_HOST_" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => $this->translate->_('Show performance graph'), 'title' => $this->translate->_('Show performance graph'), 'class' => 'pnp_graph_icon')).'</a> &nbsp;' : '';
				if (!empty($host->action_url)) {
					echo '<a href="'.nagstat::process_macros($host->action_url, $host).'" style="border: 0px" target="_blank">';
					echo html::image($this->add_path('icons/16x16/host-actions.png'), $this->translate->_('Perform extra host actions'));
					echo '</a> &nbsp;';
				}
				if (!empty($host->notes_url)) {
					echo '<a href="'.nagstat::process_macros($host->notes_url, $host).'" style="border: 0px" target="_blank">';
					echo html::image($this->add_path('icons/16x16/host-notes.png'), $this->translate->_('View extra host notes'));
					echo '</a>';
				}

				$output = $host->output;
			?>
		</td>
		<td style="white-space: normal"><?php echo $host->alias ?></td>
		<td><?php echo $host->address ?></td>
		<td style="white-space	: normal"><?php echo str_replace('','', $output) ?></td>
		<td><?php echo $host->display_name ?></td>
	</tr>
<?php	$i++; } ?>
</table><br />
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
			'ENABLE_HOST_CHECK' => $this->translate->_('Enable Active Checks'),
			'SCHEDULE_HOST_CHECK' => $this->translate->_('Reschedule Host Checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => $this->translate->_('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?><br /><br /><?php
}

# show service data if available
if (isset($service_result) ) {
	if (isset($service_pagination)) {?><div id="service_pagination"><?php echo $service_pagination ?></div><?php } ?>
<?php echo form::open('command/multi_action'); ?>
<table>
<caption><?php echo $this->translate->_('Service results for').': &quot;'.$query.'&quot'; ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_service_items" style="font-weight: normal"><?php echo $this->translate->_('Select Multiple Items') ?></a></caption>
	<tr>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo $this->translate->_('Host'); ?></th>
		<th class="header">&nbsp;</th>
		<th class="item_select_service"><input type="checkbox" class="select_all_items_service" title="<?php echo $this->translate->_('Click to select/unselect all') ?>"></th>
		<th class="header"><?php echo $this->translate->_('Service'); ?></th>
		<th class="headerNone"><?php echo $this->translate->_('Actions'); ?></th>
		<th class="header"><?php echo $this->translate->_('Last Check'); ?></th>
		<th class="header"><?php echo $this->translate->_('Status Information'); ?></th>
		<th class="header"><?php echo $this->translate->_('Display name'); ?></th>
	</tr>
<?php
	$i = 0;
	$prev_host = false;
	$comments = Comment_Model::count_comments_by_object(true);
	foreach ($service_result as $service) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<?php if ($prev_host != $service->host_name) { ?>
		<td class="bl icon <?php echo strtolower(Current_status_Model::status_text($service->host_state)); ?>"><em><?php echo Current_status_Model::status_text($service->host_state); ?></em></td>
		<td><?php echo html::anchor('extinfo/details/host/'.$service->host_name, $service->host_name);
			if (nacoma::link()===true) {
				echo '&nbsp;'.nacoma::link('configuration/configure/host/'.$service->host_name, 'icons/16x16/nacoma.png', $this->translate->_('Configure this host')).' &nbsp;';
			} ?>
		</td>
		<?php } else { ?>
		<td colspan="2" class="white" style="background-color:#ffffff;border:0px; border-right: 1px solid #cdcdcd"></td>
		<?php } ?>
		<td class="icon <?php echo strtolower(Current_status_Model::status_text($service->current_state, 'service')); ?>"><em><?php echo Current_status_Model::status_text($service->current_state, 'service'); ?></em></td>
		<td class="item_select_service"><?php echo form::checkbox(array('name' => 'object_select[]'), $service->host_name.';'.$service->service_description); ?></td>
		<td><span style="float: left">
			<?php echo html::anchor('/extinfo/details/service/'.$service->host_name.'?service='.urlencode($service->service_description), $service->service_description) ?></span>
			<?php	if ($comments !== false && array_key_exists($service->host_name.';'.$service->service_description, $comments)) { ?>
					<span style="float: right">
						<?php echo html::anchor('extinfo/details/service/'.$service->host_name.'?service='.urlencode($service->service_description).'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf($this->translate->_('This service has %s comment(s) associated with it'), $comments[$service->host_name.';'.$service->service_description]),
								'title' => sprintf($this->translate->_('This service has %s comment(s) associated with it'), $comments[$service->host_name.';'.$service->service_description]))), array('style' => 'border: 0px', 'class' => 'host_comment')); ?>
					</span>
					<?php } ?>
			</span>
			<span style="float: right">
			<?php
			$properties = 0;
			if ($service->problem_has_been_acknowledged) {
				$properties++;
				echo html::anchor('extinfo/details/service/'.$service->host_name.'/?service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px'));
			}
			if (empty($service->notifications_enabled)) {
				$properties += 2;
				echo html::anchor('extinfo/details/service/'.$service->host_name.'/?service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $this->translate->_('Notification enabled'), 'title' => $this->translate->_('Notification disabled'))), array('style' => 'border: 0px'));
			}
			if (!$service->active_checks_enabled) {
				$properties += 4;
				echo html::anchor('extinfo/details/service/'.$service->host_name.'/?service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks disabled'))), array('style' => 'border: 0px'));
			}
			if (isset($service->service_is_flapping) && $service->service_is_flapping) {
				echo html::anchor('extinfo/details/service/'.$service->host_name.'/?service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'))), array('style' => 'border: 0px'));
			}
			if ($service->scheduled_downtime_depth > 0) {
				$properties += 8;
				echo html::anchor('extinfo/details/service/'.$service->host_name.'/?service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px'));
			}
			if ($service->current_state == Current_status_Model::SERVICE_CRITICAL || $service->current_state == Current_status_Model::SERVICE_UNKNOWN || $service->current_state == Current_status_Model::SERVICE_WARNING ) {
				$properties += 16;
			}
			?></span><span class="obj_prop_service" style="display:none"><?php echo $properties ?></span>
		</td>
		<td style="text-align: left">
			<?php
				if (nacoma::link()===true)
					echo nacoma::link('configuration/configure/service/'.$service->host_name.'?service='.urlencode($service->service_description), 'icons/16x16/nacoma.png', $this->translate->_('Configure this service')).' &nbsp;';
				if (Kohana::config('config.pnp4nagios_path')!==false) {
					if (pnp::has_graph($service->host_name, urlencode($service->service_description)))
						echo '<a href="' . url::site() . 'pnp/?host='.urlencode($service->host_name).'&srv='.urlencode($service->service_description).'" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => $this->translate->_('Show performance graph'), 'title' => $this->translate->_('Show performance graph'), 'class' => 'pnp_graph_icon')).'</a> &nbsp;';
				}
				if (!empty($service->action_url)) {
					echo '<a href="'.nagstat::process_macros($service->action_url, $service).'" style="border: 0px" target="_blank">';
					echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => $this->translate->_('Perform extra host actions'),'title' => $this->translate->_('Perform extra host actions')));
					echo '</a> &nbsp;';
				}
				if (!empty($service->notes_url)) {
					echo '<a href="'.nagstat::process_macros($service->notes_url, $service).'" style="border: 0px">';
					echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => $this->translate->_('View extra host notes'),'title' => $this->translate->_('View extra host notes')));
					echo '</a> &nbsp;';
				}
			?>
		</td>
		<td><?php echo $service->last_check ? date('Y-m-d H:i:s',$service->last_check) : $label_na ?></td>
		<td><?php echo $service->output ?></td>
		<td><?php echo $service->display_name ?></td>
	</tr>
<?php	$i++;
	$prev_host = $service->host_name;
	} ?>
</table><br />
<?php echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select_service', 'id' => 'multi_action_select_service'),
		array(
			'' => $this->translate->_('Select Action'),
			'SCHEDULE_SVC_DOWNTIME' => $this->translate->_('Schedule Downtime'),
			'ACKNOWLEDGE_SVC_PROBLEM' => $this->translate->_('Acknowledge'),
			'REMOVE_SVC_ACKNOWLEDGEMENT' => $this->translate->_('Remove Problem Acknowledgement'),
			'DISABLE_SVC_NOTIFICATIONS' => $this->translate->_('Disable Service Notifications'),
			'ENABLE_SVC_NOTIFICATIONS' => $this->translate->_('Enable Service Notifications'),
			'DISABLE_SVC_CHECK' => $this->translate->_('Disable Active Checks'),
			'ENABLE_SVC_CHECK' => $this->translate->_('Enable Active Checks'),
			'SCHEDULE_SVC_CHECK' => $this->translate->_('Reschedule Service Checks')
			)
		); ?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select_service', 'value' => $this->translate->_('Submit'))); ?>
	<?php echo form::hidden('obj_type', 'service'); ?>
	<?php echo form::close(); ?>
<?php
}

# show servicegroup data if available
if (isset($servicegroup_result) ) {
	if (isset($servicegroup_pagination)) {?><div id="servicegroup_pagination"><?php echo $servicegroup_pagination ?></div><?php } ?>
<table>
<caption><?php echo $this->translate->_('Servicegroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo $this->translate->_('Servicegroup'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<th class="headerNone"><?php echo $this->translate->_('Actions'); ?></th>
	</tr>
<?php	$i = 0; foreach ($servicegroup_result as $servicegroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/servicegroup/'.$servicegroup->servicegroup_name, $servicegroup->servicegroup_name) ?></td>
		<td><?php echo html::anchor('status/servicegroup/'.$servicegroup->servicegroup_name.'?style=detail', $servicegroup->alias) ?></td>
		<td style="text-align: left">
		<?php
			echo html::anchor('status/servicegroup/'.$servicegroup->servicegroup_name.'?style=detail', html::image($this->add_path('icons/16x16/service-details.gif')), array('style' => 'border: 0px')).' &nbsp;';
			echo html::anchor('extinfo/details/servicegroup/'.$servicegroup->servicegroup_name, html::image($this->add_path('icons/16x16/extended-information.gif')), array('style' => 'border: 0px'));
		?>
		</td>
	</tr>
<?php $i++;	} ?>
</table><?php
}

# show hostgroup data if available
if (isset($hostgroup_result) ) {
	if (isset($hostgroup_pagination)) { ?><div id="hostgroup_pagination"><?php echo $hostgroup_pagination ?></div><?php } ?>
<table>
<caption><?php echo $this->translate->_('Hostgroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo $this->translate->_('Hostgroup'); ?></th>
		<th class="header"><?php echo $this->translate->_('Alias'); ?></th>
		<th class="headerNone"><?php echo $this->translate->_('Actions'); ?></th>
	</tr>
<?php	$i = 0; foreach ($hostgroup_result as $hostgroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/hostgroup/'.$hostgroup->hostgroup_name, $hostgroup->hostgroup_name) ?></td>
		<td><?php echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', $hostgroup->alias) ?></td>
		<td style="text-align: left">
		<?php
			echo html::anchor('status/hostgroup/'.$hostgroup->hostgroup_name.'?style=detail', html::image($this->add_path('icons/16x16/service-details.gif')), array('style' => 'border: 0px')).' &nbsp;';
			echo html::anchor('extinfo/details/hostgroup/'.$hostgroup->hostgroup_name, html::image($this->add_path('icons/16x16/extended-information.gif')), array('style' => 'border: 0px'));
		?>
		</td>
	</tr>
<?php $i++;	} ?>
</table><?php
}

if (isset($comment_result)) {
	$na_str = $this->translate->_('N/A');
	$label_yes = $this->translate->_('YES');
	$label_no = $this->translate->_('NO');
	$label_type_user = $this->translate->_('User');
	$label_type_downtime = $this->translate->_('Scheduled Downtime');
	$label_type_flapping = $this->translate->_('Flap Detection');
	$label_type_acknowledgement = $this->translate->_('Acknowledgement');

	if (isset($comment_pagination)) { ?><br /><div id="comment_pagination"><?php echo $comment_pagination ?></div><?php } ?>
	<table>
	<caption><?php echo $this->translate->_('Comment results for').': &quot;'.$query.'&quot'; ?></caption>
		<tr>
			<th class="header"><?php echo $this->translate->_('Host'); ?></th>
			<th class="header"><?php echo $this->translate->_('Service'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Entry time'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Author'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Comment'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('ID'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Persistent'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Type'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Expires'); ?></th>
		</tr>
	<?php
		foreach ($comment_result as $row) {
			#echo Kohana::debug($row);
			switch ($row->entry_type) {
				case Comment_Model::USER_COMMENT:
					$entry_type = $label_type_user;
					break;
				case Comment_Model::DOWNTIME_COMMENT:
					$entry_type = $label_type_downtime;
					break;
				case Comment_Model::FLAPPING_COMMENT:
					$entry_type = $label_type_flapping;
					break;
				case Comment_Model::ACKNOWLEDGEMENT_COMMENT:
					$entry_type = $label_type_acknowledgement;
					break;
				default:
					$entry_type =  '?';
			}
			$i = 0; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name) ?></td>
			<td><?php echo !empty($row->service_description) ? html::anchor('extinfo/details/service/'.$row->host_name.'?service='.urlencode($row->service_description), $row->service_description) : '' ?></td>
			<td style="white-space: normal"><?php echo !empty($row->entry_time) ? date(nagstat::date_format(), $row->entry_time) : '' ?></td>
			<td style="white-space: normal"><?php echo $row->author_name ?></td>
			<td style="white-space: normal"><?php echo $row->comment_data ?></td>
			<td style="white-space: normal"><?php echo $row->comment_id ?></td>
			<td style="white-space: normal"><?php
			if ($row->persistent === false) {
				echo $na_str;
			} else {
				echo $row->persistent ? $label_yes : $label_no;
			}
			?></td>
			<td style="white-space: normal"><?php echo $entry_type ?></td>
			<td style="white-space: normal"><?php echo $row->expires ? date(nagstat::date_format(), $row->expire_time) : $na_str ?></td>
		</tr>
	<?php $i++;	} ?>
	</table>
	<?php
}
