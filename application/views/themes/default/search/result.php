<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_chars = config::get('config.show_notes_chars', '*');
$show_passive_as_active = config::get('checks.show_passive_as_active', '*');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');?>

<div class="widget left w98" id="search_result">
<?php echo help::render('search_help') ?>&nbsp;
<?php echo isset($no_data) ? $no_data.'<br />' : '<strong>'.$limit_str.'</strong><br><br>';

$save_id = isset($save_id) ? (int)$save_id : false;
$save_label = $save_id ? _('Update this search') : _('Save this search');

echo help::render('saved_search_help').'&nbsp';
echo '<span id="save_search">'.
	html::image($this->add_path('icons/24x24/add_save_search.png'), array('title' => $save_label)).'</span><br /><br />';

# show host data if available
if (isset($host_result) ) {
	if (isset($host_pagination)) {?><div id="host_pagination"><?php echo $host_pagination ?></div><?php } ?>
<?php echo form::open('command/multi_action'); ?>
<table id="host_table">
	<caption><?php echo _('Host results for').': &quot;'.$query.'&quot'; ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a><br /></caption>
	<tr>
		<th class="header"><em><?php echo _('Status'); ?></em></th>
		<th class="item_select"><input type="checkbox" class="select_all_items" title="<?php echo _('Click to select/unselect all') ?>"></th>
		<th class="header"><?php echo _('Host'); ?></th>
		<th class="no-sort"><?php echo _('Actions'); ?></th>
		<th class="header"><?php echo _('Alias'); ?></th>
		<th class="header" style="width: 70px"><?php echo _('Address'); ?></th>
		<th class="header"><?php echo _('Status Information'); ?></th>
	<?php if ($show_display_name) { ?>
		<th class="header"><?php echo _('Display Name'); ?></th>
	<?php }
		 if ($show_notes) { ?>
		<th class="header"><?php echo _('Notes'); ?></th>
	<?php } ?>
	</tr>
<?php	$i = 0; foreach ($host_result as $host) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="icon bl <?php echo strtolower(Current_status_Model::status_text($host->current_state)); ?>"><em><?php echo Current_status_Model::status_text($host->current_state); ?></em></td>
		<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $host->host_name); ?></td>
		<td>
			<div style="float: left"><?php echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), $host->host_name) ?></div>
			<?php	$host_comments = Comment_Model::count_comments($host->host_name);
				if ($host_comments!=0) { ?>
			<span style="float: right">
				<?php echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name).'#comments',
						html::image($this->add_path('icons/16x16/add-comment.png'),
						array('alt' => sprintf(_('This host has %s comment(s) associated with it'), $host_comments),
						'title' => sprintf(_('This host has %s comment(s) associated with it'), $host_comments))), array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $host->host_name)); ?>
			</span>
			<?php } ?>
			<div style="float: right"><?php
				$properties = 0;
				if ($host->problem_has_been_acknowledged) {
					echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => _('Acknowledged'), 'title' => _('Acknowledged'))), array('style' => 'border: 0px'));
					$properties++;
				}
				if (empty($host->notifications_enabled)) {
					$properties += 2;
					echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => _('Notification enabled'), 'title' => _('Notification disabled'))), array('style' => 'border: 0px'));
				}
				if (!$host->active_checks_enabled && !$show_passive_as_active) {
					$properties += 4;
					echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => _('Active checks enabled'), 'title' => _('Active checks disabled'))), array('style' => 'border: 0px'));
				}
				if (isset($host->is_flapping) && $host->is_flapping) {
					$properties += 32;
					echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => _('Flapping'), 'title' => _('Flapping'), 'style' => 'margin-bottom: -2px')), array('style' => 'border: 0px'));
				}
				if ($host->scheduled_downtime_depth > 0) {
					$properties += 8;
					echo html::anchor('extinfo/details/?type=host&host='.urlencode($host->host_name), html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => _('Scheduled downtime'), 'title' => _('Scheduled downtime'))), array('style' => 'border: 0px'));
				}
				if ($host->current_state == Current_status_Model::SERVICE_CRITICAL || $host->current_state == Current_status_Model::SERVICE_UNKNOWN || $host->current_state == Current_status_Model::SERVICE_WARNING ) {
					$properties += 16;
				}

				 ?>
			</div><span class="obj_prop" style="display:none"><?php echo $properties ?></span>
		</td>
		<td style="text-align: left">
			<?php
				echo html::anchor('status/service/?name='.urlencode($host->host_name),html::image($this->add_path('icons/16x16/service-details.gif'), _('View service details for this host')), array('style' => 'border: 0px')).' &nbsp;';
				if (isset ($nacoma_link))
					echo html::anchor($nacoma_link.'/?type=host&name='.urlencode($host->host_name), html::image($this->img_path('icons/16x16/nacoma.png'), array('alt' => _('Configure this object using NACOMA (Nagios Configuration Manager)'), 'title' => _('Configure this object using NACOMA (Nagios Configuration Manager)'))), array('style' => 'border: 0px')).' &nbsp;';
				if (Kohana::config('config.pnp4nagios_path')!==false)
					echo (pnp::has_graph($host->host_name))  ? '<a href="' . url::site() . 'pnp/?host='.urlencode($host->host_name).'&srv=_HOST_" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => _('Show performance graph'), 'title' => _('Show performance graph'), 'class' => 'pnp_graph_icon')).'</a> &nbsp;' : '';
				if (!empty($host->action_url)) {
					echo '<a href="'.nagstat::process_macros($host->action_url, $host).'" style="border: 0px" target="'.$action_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-actions.png'), _('Perform extra host actions'));
					echo '</a> &nbsp;';
				}
				if (!empty($host->notes_url)) {
					echo '<a href="'.nagstat::process_macros($host->notes_url, $host).'" style="border: 0px" target="'.$notes_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-notes.png'), _('View extra host notes'));
					echo '</a>';
				}

				$output = $host->output;
			?>
		</td>
		<td style="white-space: normal"><?php echo $host->alias ?></td>
		<td><?php echo $host->address ?></td>
		<td style="white-space	: normal"><?php echo str_replace('','', $output) ?></td>
	<?php if ($show_display_name) { ?>
		<td><?php echo $host->display_name ?></td>
	<?php }
		 if ($show_notes) { ?>
		<td <?php if (!empty($host->notes)) { ?> style="white-space: normal" class="notescontainer"<?php } ?> title="<?php echo $host->notes ?>"><?php echo !empty($notes_chars) ? text::limit_chars($host->notes, $notes_chars, '...') : $host->notes ?></td>
	<?php } ?>
	</tr>
<?php	$i++; } ?>
</table><br />
<?php
	$options = array(
		'' => _('Select action'),
		'SCHEDULE_HOST_DOWNTIME' => _('Schedule downtime'),
		'DEL_HOST_DOWNTIME' => _('Cancel Scheduled downtime'),
		'ACKNOWLEDGE_HOST_PROBLEM' => _('Acknowledge'),
		'REMOVE_HOST_ACKNOWLEDGEMENT' => _('Remove problem acknowledgement'),
		'DISABLE_HOST_NOTIFICATIONS' => _('Disable host notifications'),
		'ENABLE_HOST_NOTIFICATIONS' => _('Enable host notifications'),
		'DISABLE_HOST_SVC_NOTIFICATIONS' => _('Disable notifications for all services'),
		'DISABLE_HOST_CHECK' => _('Disable active checks'),
		'ENABLE_HOST_CHECK' => _('Enable active checks'),
		'SCHEDULE_HOST_CHECK' => _('Reschedule host checks'),
		'ADD_HOST_COMMENT' => _('Add host comment')
		);

	if (nacoma::allowed()) {
		$options['NACOMA_DEL_HOST'] = _('Delete selected host(s)');
	}
	echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select', 'id' => 'multi_action_select'), $options);
?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => _('Submit'))); ?>
	<br /><span id="multi_object_submit_progress" class="item_select"></span>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?><br /><br /><?php
}

# show service data if available
if (isset($service_result) ) {
	if (isset($service_pagination)) {?><div id="service_pagination"><?php echo $service_pagination ?></div><?php } ?>
<?php echo form::open('command/multi_action'); ?>
<table>
<caption><?php echo _('Service results for').': &quot;'.$query.'&quot'; ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_service_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a></caption>
	<tr>
		<th class="header">&nbsp;</th>
		<th class="header"><?php echo _('Host'); ?></th>
		<th class="header">&nbsp;</th>
		<th class="item_select_service"><input type="checkbox" class="select_all_items_service" title="<?php echo _('Click to select/unselect all') ?>"></th>
		<th class="header"><?php echo _('Service'); ?></th>
		<th class="headerNone"><?php echo _('Actions'); ?></th>
		<th class="header"><?php echo _('Last Check'); ?></th>
		<th class="header"><?php echo _('Status Information'); ?></th>
	<?php if ($show_display_name) { ?>
		<th class="header"><?php echo _('Display name'); ?></th>
	<?php }
		 if ($show_notes) { ?>
		<th class="header"><?php echo _('Notes'); ?></th>
	<?php } ?>
	</tr>
<?php
	$i = 0;
	$prev_host = false;
	$comments = Comment_Model::count_comments_by_object(true);
	foreach ($service_result as $service) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<?php if ($prev_host != $service->host_name) { ?>
		<td class="bl icon <?php echo strtolower(Current_status_Model::status_text($service->host_state)); ?>"><em><?php echo Current_status_Model::status_text($service->host_state); ?></em></td>
		<td><?php echo html::anchor('extinfo/details/?type=host&host='.urlencode($service->host_name), $service->host_name);
			if (nacoma::link()===true) {
				echo '&nbsp;'.nacoma::link('configuration/configure/?type=host&name='.urlencode($service->host_name), 'icons/16x16/nacoma.png', _('Configure this host')).' &nbsp;';
			} ?>
		</td>
		<?php } else { ?>
		<td colspan="2" class="white" style="background-color:#ffffff;border:0px; border-right: 1px solid #cdcdcd"></td>
		<?php } ?>
		<td class="icon <?php echo strtolower(Current_status_Model::status_text($service->current_state, 'service')); ?>"><em><?php echo Current_status_Model::status_text($service->current_state, 'service'); ?></em></td>
		<td class="item_select_service"><?php echo form::checkbox(array('name' => 'object_select[]'), $service->host_name.';'.$service->service_description); ?></td>
		<td><span style="float: left">
			<?php echo html::anchor('/extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), $service->service_description) ?></span>
			<?php	if ($comments !== false && array_key_exists($service->host_name.';'.$service->service_description, $comments)) { ?>
					<span style="float: right">
						<?php echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description).'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf(_('This service has %s comment(s) associated with it'), $comments[$service->host_name.';'.$service->service_description]),
								'title' => sprintf(_('This service has %s comment(s) associated with it'), $comments[$service->host_name.';'.$service->service_description]))), array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $service->host_name.';'.$service->service_description)); ?>
					</span>
					<?php } ?>
			</span>
			<span style="float: right">
			<?php
			$properties = 0;
			if ($service->problem_has_been_acknowledged) {
				$properties++;
				echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => _('Acknowledged'), 'title' => _('Acknowledged')).' &nbsp;'), array('style' => 'border: 0px'));
			}
			if (empty($service->notifications_enabled)) {
				$properties += 2;
				echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => _('Notification enabled'), 'title' => _('Notification disabled')).' &nbsp;'), array('style' => 'border: 0px'));
			}
			if (!$service->active_checks_enabled && !$show_passive_as_active) {
				$properties += 4;
				echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => _('Active checks enabled'), 'title' => _('Active checks disabled')).' &nbsp;'), array('style' => 'border: 0px'));
			}
			if (isset($service->service_is_flapping) && $service->service_is_flapping) {
				$properties += 32;
				echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => _('Flapping'), 'title' => _('Flapping')).' &nbsp;'), array('style' => 'border: 0px'));
			}
			if ($service->scheduled_downtime_depth > 0) {
				$properties += 8;
				echo html::anchor('extinfo/details/?type=service&host='.urlencode($service->host_name).'&service='.urlencode($service->service_description), html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => _('Scheduled downtime'), 'title' => _('Scheduled downtime')).' &nbsp;'), array('style' => 'border: 0px'));
			}
			if ($service->current_state == Current_status_Model::SERVICE_CRITICAL || $service->current_state == Current_status_Model::SERVICE_UNKNOWN || $service->current_state == Current_status_Model::SERVICE_WARNING ) {
				$properties += 16;
			}
			?></span><span class="obj_prop_service" style="display:none"><?php echo $properties ?></span>
		</td>
		<td style="text-align: left">
			<?php
				if (nacoma::link()===true)
					echo nacoma::link('configuration/configure/?type=service&name='.urlencode($service->host_name).'&service='.urlencode($service->service_description), 'icons/16x16/nacoma.png', _('Configure this service')).' &nbsp;';
				if (Kohana::config('config.pnp4nagios_path')!==false) {
					if (pnp::has_graph($service->host_name, urlencode($service->service_description)))
						echo '<a href="' . url::site() . 'pnp/?host='.urlencode($service->host_name).'&srv='.urlencode($service->service_description).'" style="border: 0px">'.html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => _('Show performance graph'), 'title' => _('Show performance graph'), 'class' => 'pnp_graph_icon')).'</a> &nbsp;';
				}
				if (!empty($service->action_url)) {
					echo '<a href="'.nagstat::process_macros($service->action_url, $service).'" style="border: 0px" target="'.$action_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => _('Perform extra host actions'),'title' => _('Perform extra host actions')));
					echo '</a> &nbsp;';
				}
				if (!empty($service->notes_url)) {
					echo '<a href="'.nagstat::process_macros($service->notes_url, $service).'" style="border: 0px" target="'.$notes_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => _('View extra host notes'),'title' => _('View extra host notes')));
					echo '</a> &nbsp;';
				}
			?>
		</td>
		<td><?php echo $service->last_check ? date($date_format_str,$service->last_check) : _('N/A')?></td>
		<td><?php echo $service->output ?></td>
	<?php if ($show_display_name) { ?>
		<td><?php echo $service->display_name ?></td>
	<?php }
		 if ($show_notes) { ?>
		<td <?php if (!empty($service->notes)) { ?>style="white-space: normal" class="notescontainer"<?php } ?> title="<?php echo $service->notes ?>"><?php echo !empty($notes_chars) ? text::limit_chars($service->notes, $notes_chars, '...') : $service->notes ?></td>
	<?php } ?>
	</tr>
<?php	$i++;
	$prev_host = $service->host_name;
	} ?>
</table><br />
<?php
	$options = array(
		'' => _('Select action'),
		'SCHEDULE_SVC_DOWNTIME' => _('Schedule downtime'),
		'DEL_SVC_DOWNTIME' => _('Cancel Scheduled downtime'),
		'ACKNOWLEDGE_SVC_PROBLEM' => _('Acknowledge'),
		'REMOVE_SVC_ACKNOWLEDGEMENT' => _('Remove problem acknowledgement'),
		'DISABLE_SVC_NOTIFICATIONS' => _('Disable service notifications'),
		'ENABLE_SVC_NOTIFICATIONS' => _('Enable service notifications'),
		'DISABLE_SVC_CHECK' => _('Disable active checks'),
		'ENABLE_SVC_CHECK' => _('Enable active checks'),
		'SCHEDULE_SVC_CHECK' => _('Reschedule service checks'),
		'ADD_SVC_COMMENT' => _('Add service comment')
		);

	if (nacoma::allowed()) {
		$options['NACOMA_DEL_SERVICE'] = _('Delete selected service(s)');
	}

	echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select_service', 'id' => 'multi_action_select_service'), $options);
?>
	<?php echo form::submit(array('id' => 'multi_object_submit_service', 'class' => 'item_select_service', 'value' => _('Submit'))); ?>
	<br /><span id="multi_object_submit_progress_service" class="item_select_service"></span>
	<?php echo form::hidden('obj_type', 'service'); ?>
	<?php echo form::close(); ?>
<br />
<br />
<?php
}
# show servicegroup data if available
if (isset($servicegroup_result) ) {
	if (isset($servicegroup_pagination)) {?><div id="servicegroup_pagination"><?php echo $servicegroup_pagination ?></div><?php } ?>
<table>
<caption><?php echo _('Servicegroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo _('Servicegroup'); ?></th>
		<th class="header"><?php echo _('Alias'); ?></th>
		<th class="headerNone"><?php echo _('Actions'); ?></th>
	</tr>
<?php	$i = 0; foreach ($servicegroup_result as $servicegroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/?type=servicegroup&host='.urlencode($servicegroup->servicegroup_name), $servicegroup->servicegroup_name) ?></td>
		<td><?php echo html::anchor('status/servicegroup/?group='.urlencode($servicegroup->servicegroup_name).'&style=detail', $servicegroup->alias) ?></td>
		<td style="text-align: left">
		<?php
			echo html::anchor('status/servicegroup/?group='.urlencode($servicegroup->servicegroup_name).'&style=detail', html::image($this->add_path('icons/16x16/service-details.gif')), array('style' => 'border: 0px')).' &nbsp;';
			echo html::anchor('extinfo/details/?type=servicegroup&host='.urlencode($servicegroup->servicegroup_name), html::image($this->add_path('icons/16x16/extended-information.gif')), array('style' => 'border: 0px'));
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
<caption><?php echo _('Hostgroup results for').': &quot;'.$query.'&quot'; ?></caption>
	<tr>
		<th class="header"><?php echo _('Hostgroup'); ?></th>
		<th class="header"><?php echo _('Alias'); ?></th>
		<th class="headerNone"><?php echo _('Actions'); ?></th>
	</tr>
<?php	$i = 0; foreach ($hostgroup_result as $hostgroup) { ?>
	<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
		<td class="bl"><?php echo html::anchor('extinfo/details/?type=hostgroup&host='.urlencode($hostgroup->hostgroup_name), $hostgroup->hostgroup_name) ?></td>
		<td><?php echo html::anchor('status/hostgroup/?group='.urlencode($hostgroup->hostgroup_name).'&style=detail', $hostgroup->alias) ?></td>
		<td style="text-align: left">
		<?php
			echo html::anchor('status/hostgroup/?group='.urlencode($hostgroup->hostgroup_name).'&style=detail', html::image($this->add_path('icons/16x16/service-details.gif')), array('style' => 'border: 0px')).' &nbsp;';
			echo html::anchor('extinfo/details/?type=hostgroup&host='.urlencode($hostgroup->hostgroup_name), html::image($this->add_path('icons/16x16/extended-information.gif')), array('style' => 'border: 0px'));
		?>
		</td>
	</tr>
<?php $i++;	} ?>
</table><?php
}

if (isset($comment_result)) {
	echo form::open('extinfo/show_comments');

	if (isset($comment_pagination)) { ?><br /><div id="comment_pagination"><?php echo $comment_pagination ?></div><?php } ?>
	<table id="comment_search_table">
	<caption><?php echo _('Comment results for').': &quot;'.$query.'&quot'; ?>
		<?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_comment_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a>
	</caption>
		<tr>
			<th class="item_selectcomment headerNone">
				<?php echo form::checkbox(array('name' => 'selectall_comments', 'class' => 'select_all_items'), ''); ?>
			</th>
			<th class="header"><?php echo _('Host'); ?></th>
			<th class="header"><?php echo _('Service'); ?></th>
			<th class="headerNone"><?php echo _('Entry time'); ?></th>
			<th class="headerNone"><?php echo _('Author'); ?></th>
			<th class="headerNone"><?php echo _('Comment'); ?></th>
			<th class="headerNone"><?php echo _('ID'); ?></th>
			<th class="headerNone"><?php echo _('Persistent'); ?></th>
			<th class="headerNone"><?php echo _('Type'); ?></th>
			<th class="headerNone"><?php echo _('Expires'); ?></th>
		</tr>
	<?php
		foreach ($comment_result as $row) {
			#echo Kohana::debug($row);
			switch ($row->entry_type) {
				case Comment_Model::USER_COMMENT:
					$entry_type = _('User');
					break;
				case Comment_Model::DOWNTIME_COMMENT:
					$entry_type = _('Scheduled Downtime');
					break;
				case Comment_Model::FLAPPING_COMMENT:
					$entry_type = _('Flap Detection');
					break;
				case Comment_Model::ACKNOWLEDGEMENT_COMMENT:
					$entry_type = _('Acknowledgement');
					break;
				default:
					$entry_type =  '?';
			}
			$i = 0; ?>
		<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd' ?>">
			<td class="item_selectcomment"><?php echo form::checkbox(array('name' => 'del_comment_'.(isset($row->service_description) ? 'service' : 'host').'[]', 'class' => 'deletecommentbox', 'title' => _('Click to select/unselect all')), $row->comment_id); ?></td>
			<td><?php echo html::anchor('extinfo/details/?type=host&host='.urlencode($row->host_name), $row->host_name) ?></td>
			<td><?php echo !empty($row->service_description) ? html::anchor('extinfo/details/service/'.$row->host_name.'?service='.urlencode($row->service_description), $row->service_description) : '' ?></td>
			<td style="white-space: normal"><?php echo !empty($row->entry_time) ? date($date_format_str, $row->entry_time) : '' ?></td>
			<td style="white-space: normal"><?php echo $row->author_name ?></td>
			<td style="white-space: normal"><?php echo $row->comment_data ?></td>
			<td style="white-space: normal"><?php echo $row->comment_id ?></td>
			<td style="white-space: normal"><?php
			if ($row->persistent === false) {
				echo _('N/A');
			} else {
				echo $row->persistent ? _('Yes') : _('No');
			}
			?></td>
			<td style="white-space: normal"><?php echo $entry_type ?></td>
			<td style="white-space: normal"><?php echo $row->expires ? date($date_format_str, $row->expire_time) : _('N/A') ?></td>
		</tr>
	<?php $i++;	} ?>
	</table>
	<?php
		echo '<div class="item_selectcomment">';
		echo form::hidden('redirect_page', Router::$controller.'/'.Router::$method.'?'.$_SERVER['QUERY_STRING']);
		echo form::submit(array('name' => 'del_submit_comment'), _('Delete Selected'));
		echo '</div>';
		echo form::close();
}
?><br />
