<?php defined('SYSPATH') OR die('No direct access allowed.');
$style = isset($style) ? $style : false;
$link_to_nacoma = nacoma::link()===true;
$show_passive_as_active = config::get('checks.show_passive_as_active', '*');
$notes_chars = config::get('config.show_notes_chars', '*');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');?>
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

<div class="widget left w98" id="status_service">
<?php echo (isset($pagination)) ? $pagination : ''; ?>

<?php echo form::open('command/multi_action'); ?><br />
<table style="margin-bottom: 2px" id="service_table">
<caption><?php echo $sub_title ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_service_items" style="font-weight: normal"><?php echo $this->translate->_('Select multiple items') ?></a></caption>
		<tr>
			<th><em><?php echo $this->translate->_('Status'); ?></em></th>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'h.host_name';
				$n = 0;
				foreach($header_links as $row) {
					$n++;
					if (isset($row['url_desc'])) {
						if ($n == 4)
							echo '<th class="no-sort">'.$this->translate->_('Actions').'</th>';
						echo ($n == 3 ? '<th class="item_select_service"><input type="checkbox" class="select_all_items_service" title="'.$this->translate->_('Click to select/unselect all').'"></th>' : '');
						echo '<th class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
							'" onclick="location.href=\'' . url::site() .((isset($row['url_desc']) && $order == 'ASC') ? $row['url_desc'] : ((isset($row['url_asc']) && $order == 'DESC') ? $row['url_asc'] : '')).'&items_per_page='.$items_per_page.'&page='.$page.'&style='.$style.'&group_type='.$group_type.'\'">';

						echo ($n == 2 ? '<em>'.$row['title'].'</em>' : $row['title']);
						echo '</th>';
					}
				}
			?>
		</tr>
<?php
	$curr_host = false;
	$a = 0;
	$c=0;
	$auth = Nagios_auth_Model::instance();
	if (!empty($result)) {
		foreach ($result as $row) {
		$a++;
		if ($curr_host != $row->host_name)
			$c++;
	?>
	<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
		<td class="icon <?php if ($this->cmd_ok && $this->cmd_host_ok && $auth->is_authorized_for_host($row->host_name)) { ?>obj_properties <?php } ?> <?php echo strtolower(Current_status_Model::status_text($row->host_state)).' '.(($curr_host != $row->host_name) ? ($c == 1 && $a != 1 ? ' bt' : '') : 'white') ?>" <?php echo ($curr_host != $row->host_name) ? '' : 'colspan="1"' ?> id="<?php echo 'host|'.$row->host_name ?>" title="<?php echo Current_status_Model::status_text($row->host_state); ?>"><em><?php echo Current_status_Model::status_text($row->host_state); ?></em></td>
		<?php if ($curr_host != $row->host_name) { ?>
		<td class="service_hostname w80<?php echo ($c == 1 && $a != 1) ? ' bt' : '';?>" style="white-space: normal; border-right: 1px solid #dcdcdc;">
				<span style="float: left"><?php echo html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::specialchars($row->host_name), array('title' => $row->address)) ?></span>
				<span style="float: right">
					<?php
						$host_props = 0;
						if ($row->hostproblem_is_acknowledged) {
							echo html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px')).'&nbsp; ';
							$host_props++;
						}
						if (empty($row->host_notifications_enabled)) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $this->translate->_('Notification disabled'), 'title' => $this->translate->_('Notification disabled'))), array('style' => 'border: 0px')).'&nbsp; ';
							$host_props += 2;
						}
						if (!$row->host_active_checks_enabled && !$show_passive_as_active) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks disabled'))), array('style' => 'border: 0px')).'&nbsp; ';
							$host_props += 4;
						}
						if (isset($row->host_is_flapping) && $row->host_is_flapping) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'))), array('style' => 'border: 0px')).'&nbsp; ';
							$host_props += 32;
						}
						if ($row->hostscheduled_downtime_depth > 0) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px')).'&nbsp; ';
							$host_props += 8;
						}
						if ($host_comments !== false && array_key_exists($row->host_name, $host_comments)) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name).'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments[$row->host_name]),
								'title' => sprintf($this->translate->_('This host has %s comment(s) associated with it'), $host_comments[$row->host_name]))), array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $row->host_name)).'&nbsp; ';
						}
						if ($row->host_state == Current_status_Model::HOST_DOWN || $row->host_state == Current_status_Model::HOST_UNREACHABLE) {
							$host_props += 16;
						}
						if ($link_to_nacoma) {
							echo nacoma::link('configuration/configure/?type=host&name='.urlencode($row->host_name), 'icons/16x16/nacoma.png', $this->translate->_('Configure this host')).' &nbsp;';
						}

						if (!empty($row->host_icon_image)) {
							echo html::anchor('extinfo/details/?host='.urlencode($row->host_name),html::image('application/media/images/logos/'.$row->host_icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->host_icon_image_alt, 'title' => $row->host_icon_image_alt)),array('style' => 'border: 0px'));
						} ?>
					<span class="obj_prop _<?php echo $row->host_name ?>" style="display:none"><?php echo $host_props ?></span>
				</span>
		</td>
		<?php } else { $c = 0;?>
			<td class="service_hostname white" style="white-space: normal; border-right: 1px solid #dcdcdc;">&nbsp;</td>
		<?php } ?>
		<td class="icon <?php if ($this->cmd_ok && $this->cmd_svc_ok) { ?>svc_obj_properties <?php } echo strtolower(Current_status_Model::status_text($row->current_state, 'service')); ?>" id="<?php echo 'service|'.$row->host_name.'|'.(str_replace(' ', '_', $row->service_description).'|'.$row->service_description) ?>" title="<?php echo Current_status_Model::status_text($row->current_state, 'service'); ?>"><em><?php echo Current_status_Model::status_text($row->current_state, 'service'); ?></em></td>
		<td class="item_select_service"><?php echo form::checkbox(array('name' => 'object_select[]'), $row->host_name.';'.$row->service_description); ?></td>
		<td style="white-space: normal">
			<span style="float: left">
				<?php echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::specialchars($row->service_description)) ?></span>
				<?php	if ($comments !== false && array_key_exists($row->host_name.';'.$row->service_description, $comments)) { ?>
					<span style="float: right">
						<?php echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description).'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf($this->translate->_('This service has %s comment(s) associated with it'), $comments[$row->host_name.';'.$row->service_description]),
								'title' => sprintf($this->translate->_('This service has %s comment(s) associated with it'), $comments[$row->host_name.';'.$row->service_description]))), array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $row->host_name.';'.$row->service_description)); ?>
					</span>
					<?php } ?>
			<span style="float: right">
			<?php
				$properties = 0;
				if ($row->problem_has_been_acknowledged) {
					$properties++;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => $this->translate->_('Acknowledged'), 'title' => $this->translate->_('Acknowledged'))), array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (empty($row->notifications_enabled)) {
					$properties += 2;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => $this->translate->_('Notification enabled'), 'title' => $this->translate->_('Notification disabled'))), array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (!$row->active_checks_enabled && !$show_passive_as_active) {
					$properties += 4;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => $this->translate->_('Active checks enabled'), 'title' => $this->translate->_('Active checks disabled'))), array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (isset($row->service_is_flapping) && $row->service_is_flapping) {
					$properties += 32;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => $this->translate->_('Flapping'), 'title' => $this->translate->_('Flapping'))), array('style' => 'border: 0px')).'&nbsp; ';
				}
				if ($row->scheduled_downtime_depth > 0) {
					$properties += 8;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->service_description), html::image($this->add_path('icons/16x16//scheduled-downtime.png'),array('alt' => $this->translate->_('Scheduled downtime'), 'title' => $this->translate->_('Scheduled downtime'))), array('style' => 'border: 0px')).'&nbsp; ';
				}
				if ($row->current_state == Current_status_Model::SERVICE_CRITICAL || $row->current_state == Current_status_Model::SERVICE_UNKNOWN || $row->current_state == Current_status_Model::SERVICE_WARNING ) {
					$properties += 16;
				}
			?>
			</span><span class="obj_prop_service _<?php echo preg_replace('/[^a-zA-Z0-9-_]/', '_', $row->host_name.'__'.$row->service_description) ?>" style="display:none"><?php echo $properties ?></span>
		</td>
		<td>
			<?php
				if ($link_to_nacoma)
					echo nacoma::link('configuration/configure/?type=service&name='.urlencode($row->host_name).'&service='.urlencode($row->service_description), 'icons/16x16/nacoma.png', $this->translate->_('Configure this service')).' &nbsp;';
				if (Kohana::config('config.pnp4nagios_path')!==false) {
					if (pnp::has_graph($row->host_name, urlencode($row->service_description)))
						echo html::anchor('pnp/?host='.urlencode($row->host_name).'&srv='.urlencode($row->service_description), html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => $this->translate->_('Show performance graph'), 'title' => $this->translate->_('Show performance graph'), 'class' => 'pnp_graph_icon')), array('style' => 'border: 0px')).' &nbsp;';
				}
				if (!empty($row->action_url)) {
					echo '<a href="'.nagstat::process_macros($row->action_url, $row).'" style="border: 0px" target="'.$action_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-actions.png'),array('alt' => $this->translate->_('Perform extra host actions'),'title' => $this->translate->_('Perform extra host actions')));
					echo '</a> &nbsp;';
				}
				if (!empty($row->notes_url)) {
					echo '<a href="'.nagstat::process_macros($row->notes_url, $row).'" style="border: 0px" target="'.$notes_url_target.'">';
					echo html::image($this->add_path('icons/16x16/host-notes.png'),array('alt' => $this->translate->_('View extra host notes'),'title' => $this->translate->_('View extra host notes')));
					echo '</a> &nbsp;';
				}
			?>
		</td>
		<td style="width: 110px"><?php echo $row->last_check ? date($date_format_str,$row->last_check) : $na_str ?></td>
<?php	if (isset($is_svc_details) && $is_svc_details !== false) {
			# make sure we print service duration and not host since we have a special query result here, i.e displaying servicegroup result ?>
		<td style="width: 110px"><?php echo $row->service_duration != $row->service_cur_time ? time::to_string($row->service_duration) : $na_str ?></td>
<?php	} else { ?>
		<td style="width: 110px"><?php echo $row->duration != $row->cur_time ? time::to_string($row->duration) : $na_str ?></td>
<?php	} ?>
		<td style="text-align: center; width: 60px"><?php echo $row->current_attempt;?>/<?php echo $row->max_check_attempts ?></td>
		<td style="white-space: normal">
		<?php
			if ($row->current_state == Current_status_Model::HOST_PENDING && isset($pending_output)) {
				echo $row->should_be_scheduled ? sprintf($pending_output, date($date_format_str, $row->next_check)) : $nocheck_output;
			} else {
				$output = $row->output;
				$output = str_replace('','', $output);
				echo str_replace('\n','<br />', $output);
				if (config::get('config.service_long_output_enabled', '*')) {
					if ($row->long_output) {
						echo '<br />' . str_replace('\n','</br />', $row->long_output);
					}
				}
			}
			?>
		</td>

<?php	if ($show_display_name) { ?>
		<td style="white-space: normal"><?php echo $row->display_name ?></td>
<?php	}

		if ($show_notes) { ?>
		<td style="white-space: normal"<?php if (!empty($row->notes)) { ?>class="notescontainer" title="<?php echo $row->notes ?>"><?php echo !empty($notes_chars) ? text::limit_chars($row->notes, $notes_chars, '...') : $row->notes; } ?></td>
<?php 	} ?>
	</tr>

	<?php
			$curr_host = $row->host_name;
		} ?>

<?php } else {
			echo '<tr><td colspan=9>';
			if (isset($filters) && !empty($filters)) {
				echo $this->translate->_('No services found matching this filter.');
			} else {
				echo $this->translate->_('No services found for this host.');
			}
			echo '</td></tr>';
		} ?>
		</table>
<?php
	$options = array(
		'' => $this->translate->_('Select action'),
		'SCHEDULE_SVC_DOWNTIME' => $this->translate->_('Schedule downtime'),
		'DEL_SVC_DOWNTIME' => $this->translate->_('Cancel Scheduled downtime'),
		'ACKNOWLEDGE_SVC_PROBLEM' => $this->translate->_('Acknowledge'),
		'REMOVE_SVC_ACKNOWLEDGEMENT' => $this->translate->_('Remove problem acknowledgement'),
		'DISABLE_SVC_NOTIFICATIONS' => $this->translate->_('Disable service notifications'),
		'ENABLE_SVC_NOTIFICATIONS' => $this->translate->_('Enable service notifications'),
		'DISABLE_SVC_CHECK' => $this->translate->_('Disable active checks'),
		'ENABLE_SVC_CHECK' => $this->translate->_('Enable active checks'),
		'SCHEDULE_SVC_CHECK' => $this->translate->_('Reschedule service checks'),
		'ADD_SVC_COMMENT' => $this->translate->_('Add service comment')
		);

	if (nacoma::allowed()) {
		$options['NACOMA_DEL_SERVICE'] = $this->translate->_('Delete selected service(s)');
	}
	echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select_service', 'id' => 'multi_action_select_service'), $options);
?>
	<?php echo form::submit(array('id' => 'multi_object_submit_service', 'class' => 'item_select_service', 'value' => $this->translate->_('Submit'))); ?>
	<br /><span id="multi_object_submit_progress_service" class="item_select_service"></span>
	<?php echo form::hidden('obj_type', 'service'); ?>
	<?php echo form::close(); ?>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
<br /><br />
</div>
