<?php defined('SYSPATH') OR die('No direct access allowed.');
$style = isset($style) ? $style : false;
$link_to_nacoma = nacoma::link()===true;
$notes_chars = config::get('config.show_notes_chars', '*');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');?>
<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<div class="widget left w32" id="page_links">
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
	</div>
	<div class="clear"> </div>
	<hr />

		<?php
			if (!empty($widgets)) {
				foreach ($widgets as $widget) {
					echo $widget;
				}
			}
		?>

		<?php
			if (isset($filters) && !empty($filters)) {
				echo $filters;
			}
		?>

  <div class="clear"></div>

</div>

<div class="widget left w98" id="status_service">
<?php echo (isset($pagination)) ? $pagination : ''; ?>

<?php echo form::open('command/multi_action'); ?>
<table style="margin-bottom: 2px" id="service_table">
<caption><?php echo $sub_title ?>: <?php echo '<span class="icon-16 x16-check-boxes"></span>';?> <a href="#" id="select_multiple_service_items" style="font-weight: normal"><?php echo _('Select multiple items') ?></a></caption>
		<tr>
			<th><em><?php echo _('Status'); ?></em></th>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'host_name';
				$n = 0;
				foreach($header_links as $row) {
					$n++;
					if (isset($row['url_desc'])) {
						if ($n == 4)
							echo '<th class="no-sort">'._('Actions').'</th>';
						echo ($n == 3 ? '<th class="item_select_service"><input type="checkbox" class="select_all_items_service" title="'._('Click to select/unselect all').'"></th>' : '');
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
		$row = (object) $row;
		$a++;
		if ($curr_host != $row->host_name)
			$c++;
	?>
	<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
		
		<?php
			$is_authed_for_command = (Command_Controller::_is_authorized_for_command(array('host_name' => $row->host_name)) === true);
			$service_status_model = Current_status_Model::status_text($row->host_state, $row->host_has_been_checked);
			$is_current_host = ($curr_host != $row->host_name);
		?>

		 
		<?php

			$tmp_td = '<td class="icon';

			// Designate conditional class

			if ($is_authed_for_command) $tmp_td .= ' obj_properties ';
			$tmp_td .= strtolower($service_status_model).' '.(($is_current_host) ? ($c == 1 && $a != 1 ? ' bt' : '') : 'white').'"';
			
			// Designate conditional colspan

			if ($is_current_host)
				$tmp_td .= 'colspan="1"';

			$tmp_td .= 'id="host|'.$row->host_name.'" title="'.$service_status_model.'">';
			echo $tmp_td;
			echo '<span class="icon-16 x16-shield-'.$service_status_model.'"></span>';
			echo '</td>';

		?>

			
		
		<?php if ($curr_host != $row->host_name) { ?>
		<td class="service_hostname w80<?php echo ($c == 1 && $a != 1) ? ' bt' : '';?>" style="white-space: normal; border-right: 1px solid #dcdcdc;">
				<span style="float: left"><?php echo html::anchor('extinfo/details/?host='.urlencode($row->host_name), html::specialchars($row->host_name), array('title' => $row->host_address)) ?></span>
				<span style="float: right">
					<?php
						$host_props = 0;
						if ($row->host_acknowledged) {
							echo html::anchor('extinfo/details/?host='.urlencode($row->host_name), 
								'<span class="icon-16 x16-acknowledged" title="'._('Acknowledged').'"></span>',
								array('style' => 'border: 0px'));
							$host_props++;
						}
						if (empty($row->host_notifications_enabled)) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), 
								'<span class="icon-16 x16-notify-disabled" title="'._('Notification disabled').'"></span>',
								array('style' => 'border: 0px'));
							$host_props += 2;
						}
						if (!$row->host_active_checks_enabled && !$show_passive_as_active) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name),
								'<span class="icon-16 x16-active-checks-disabled" title="'._('Active checks enabled').'"></span>',
								array('style' => 'border: 0px'));
							$host_props += 4;
						}
						if (isset($row->host_is_flapping) && $row->host_is_flapping) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name), 
								html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => _('Flapping'), 'title' => _('Flapping'))), 
								array('style' => 'border: 0px'));
							$host_props += 32;
						}
						if ($row->host_scheduled_downtime_depth > 0) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name),
								'<span class="icon-16 x16-scheduled-downtime" title="'._('Scheduled downtime').'"></span>',
								array('style' => 'border: 0px'));
							$host_props += 8;
						}
						$num_host_comments = count($row->host_comments);
						if ($num_host_comments > 0) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->host_name).'#comments',
								'<span class="icon-16 x16-add-comment" title="'.sprintf(_('This host has %s comment(s) associated with it'), $num_host_comments).'"></span>',
								array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $row->host_name));
						}
						if ($row->host_state == Current_status_Model::HOST_DOWN || $row->host_state == Current_status_Model::HOST_UNREACHABLE) {
							$host_props += 16;
						}
						if ($link_to_nacoma) {
							echo nacoma::link('configuration/configure/?type=host&name='.urlencode($row->host_name), 'icons/16x16/nacoma.png', _('Configure this host'));
						}

						if (!empty($row->host_icon_image)) {
							echo html::anchor('extinfo/details/?host='.urlencode($row->host_name),
								html::image(Kohana::config('config.logos_path').$row->host_icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->host_icon_image_alt, 'title' => $row->host_icon_image_alt)),
								array('style' => 'border: 0px'));
						} ?>
					<span class="obj_prop _<?php echo $row->host_name ?>" style="display:none"><?php echo $host_props ?></span>
				</span>
		</td>
		<?php } else { $c = 0;?>
			<td class="service_hostname white" style="white-space: normal; border-right: 1px solid #dcdcdc;">&nbsp;</td>
		<?php } ?>

		<td class="icon <?php if (Command_Controller::_is_authorized_for_command(array('host_name' => $row->host_name, 'service' => $row->description)) === true) { ?>svc_obj_properties <?php } echo strtolower(Current_status_Model::status_text($row->state, $row->has_been_checked, 'service')); ?>" id="<?php echo 'service|'.$row->host_name.'|'.(str_replace(' ', '_', $row->description).'|'.$row->description) ?>" title="<?php echo Current_status_Model::status_text($row->state, $row->has_been_checked, 'service'); ?>"><em><?php echo Current_status_Model::status_text($row->state, $row->has_been_checked, 'service'); ?></em></td>
		

		<td class="item_select_service"><?php echo form::checkbox(array('name' => 'object_select[]'), $row->host_name.';'.$row->description); ?></td>
		<td style="white-space: normal">
			<span style="float: left">
				<?php echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description), html::specialchars($row->description)) ?></span>
				<?php
				$num_comments = count($row->comments);
				if ($num_comments>0) { ?>
					<span style="float: right">
						<?php echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description).'#comments',
								'<span class="icon-16 x16-add-comment" title="'.sprintf(_('This service has %s comment(s) associated with it'), $num_comments).'"></span>',
								array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $row->host_name.';'.$row->description)); ?>
					</span>
					<?php } ?>
			<span style="float: right">
			<?php
				$properties = 0;
				if ($row->acknowledged) {
					$properties++;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description), 
						'<span class="icon-16 x16-acknowledged" title="'._('Acknowledged').'"></span>',
						array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (empty($row->notifications_enabled)) {
					$properties += 2;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description), 
						'<span class="icon-16 x16-notify-disabled" title="'._('Notification disabled').'"></span>',
						array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (!$row->active_checks_enabled) {
					$properties += 4;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description),
						'<span class="icon-16 x16-active-checks-disabled" title="'._('Active checks enabled').'"></span>',
						array('style' => 'border: 0px')).'&nbsp; ';
				}
				if (isset($row->service_is_flapping) && $row->service_is_flapping) {
					$properties += 32;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description),
						html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => _('Flapping'), 'title' => _('Flapping'))), 
						array('style' => 'border: 0px')).'&nbsp; ';
				}
				if ($row->scheduled_downtime_depth > 0) {
					$properties += 8;
					echo html::anchor('extinfo/details/service?host='.urlencode($row->host_name).'&service='.urlencode($row->description),
						'<span class="icon-16 x16-scheduled-downtime" title="'._('Scheduled downtime').'"></span>',
						array('style' => 'border: 0px')).'&nbsp; ';
				}
				if ($row->state == Current_status_Model::SERVICE_CRITICAL || $row->state == Current_status_Model::SERVICE_UNKNOWN || $row->state == Current_status_Model::SERVICE_WARNING ) {
					$properties += 16;
				}
			?>
			</span><span class="obj_prop_service _<?php echo preg_replace('/[^a-zA-Z0-9-_]/', '_', $row->host_name.'__'.$row->description) ?>" style="display:none"><?php echo $properties ?></span>
		</td>
		<td>
			<?php
				
				if ($link_to_nacoma)
					echo nacoma::link('configuration/configure/?type=service&name='.urlencode($row->host_name).'&service='.urlencode($row->description), 'icons/16x16/nacoma.png', _('Configure this service')).' &nbsp;';
				
				echo $row->pnpgraph_present ? html::anchor('pnp/?host='.urlencode($row->host_name).'&srv='.urlencode($row->description), 
					'<span class="icon-16 x16-pnp" title="'._('Show performance graph').'"></span>', 
					array('style' => 'border: 0px')) : '';
				
				if (!empty($row->action_url)) {
					echo '<a href="'.nagstat::process_macros($row->action_url, $row).'" style="border: 0px" target="'.$action_url_target.'">'.
						'<span class="icon-16 x16-host-actions" title="'._('Perform extra host actions').'"></span>'.
						'</a>';
				}
				if (!empty($row->notes_url)) {
					echo '<a href="'.nagstat::process_macros($row->notes_url, $row).'" style="border: 0px" target="'.$notes_url_target.'">'.
						'<span class="icon-16 x16-host-notes" title="'._('View extra host notes').'"></span>'.
					'</a>';
				}

			?>
		</td>
		<td><?php echo $row->last_check ? date($date_format_str,$row->last_check) : _('N/A') ?></td>
		<td><?php echo $row->last_state_change > 0 ? time::to_string($row->duration) : _('N/A') ?></td>
		
		<!--<td>FUU</td>-->
		<td style="text-align: center;"><?php echo $row->current_attempt;?>/<?php echo $row->max_check_attempts ?></td>
		
		<td style="white-space: normal">
		<?php
			if ($row->state == Current_status_Model::HOST_PENDING && isset($pending_output)) {
				echo $row->should_be_scheduled ? sprintf($pending_output, date($date_format_str, $row->next_check)) : _('Service is not scheduled to be checked...');
			} else {
				$output = $row->plugin_output;
				$output = str_replace('','', $output);
				echo str_replace('\n','<br />', htmlspecialchars($output));
				if (config::get('config.service_long_output_enabled', '*')) {
					if ($row->long_output) {
						echo '<br />' . str_replace('\n','</br />', htmlspecialchars($row->long_output));
					}
				}
			}
			?>
		</td>

<?php	if ($show_display_name) { ?>
		<td style="white-space: normal"><?php echo $row->display_name ?></td>
<?php	}

		if ($show_notes) { ?>
		<td style="white-space: normal"<?php if (!empty($row->notes)) { ?>class="notescontainer" title="<?php echo htmlspecialchars($row->notes) ?>"><?php echo htmlspecialchars(!empty($notes_chars) ? text::limit_chars($row->notes, $notes_chars, '...') : $row->notes); } ?></td>
<?php 	} ?>
	</tr>

	<?php
			$curr_host = $row->host_name;
		} ?>

<?php } else {
			echo '<tr><td colspan=9>';
			if (isset($filters) && !empty($filters)) {
				echo _('No services found matching this filter.');
			} else {
				echo _('No services found for this host.');
			}
			echo '</td></tr>';
		} ?>
		</table>
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
	echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select_service auto', 'id' => 'multi_action_select_service'), $options);
?>
	<?php echo form::submit(array('id' => 'multi_object_submit_service', 'class' => 'item_select_service', 'value' => _('Submit'))); ?>
	<span id="multi_object_submit_progress_service" class="item_select_service"></span>
	<?php echo form::hidden('obj_type', 'service'); ?>
	<?php echo form::close(); ?>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>
