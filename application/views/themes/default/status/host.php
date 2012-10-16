<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $nacoma_link = nacoma::link()===true;
$notes_chars = config::get('config.show_notes_chars', '*');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*'); ?>
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

<div class="widget left w98" id="status_host">
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<?php echo form::open('command/multi_action'); ?><br />
	<table id="host_table" style="margin-bottom: 10px">
	<caption style="margin-top: 0px"><?php echo $sub_title ?>: <?php echo html::image($this->add_path('icons/16x16/check-boxes.png'),array('style' => 'margin-bottom: -3px'));?> <a href="#" id="select_multiple_items" style="font-weight: normal"><?php echo _('Select Multiple Items') ?></a><br /></caption>

			<tr>
				<?php
					$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
					$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'host_name';
					$n = 0;
					foreach($header_links as $row) {
						$n++;
						if (isset($row['url_desc'])) {
							echo ($n == 2 ? '<th class="item_select"><input type="checkbox" class="select_all_items" title="'._('Click to select/unselect all').'"></th>' : '')."\n";
							echo ($n == 3 ? '<th class="no-sort">'._('Actions').'</th>' : '')."\n";
							echo '<th '.($row['title'] == 'Host' ? 'colspan="2"' : '').' class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
								'" onclick="location.href=\'' . url::site() .((isset($row['url_desc']) && $order == 'ASC') ? str_replace('&','&amp;',$row['url_desc']) : ((isset($row['url_asc']) && $order == 'DESC') ? str_replace('&','&amp;',$row['url_asc']) : '')).'&items_per_page='.$items_per_page.'&page='.$page.'\'">'."\n";
							echo ($n == 1 ? '<em>'.$row['title'].'</em>' : $row['title']);
							echo '</th>'."\n";
						}
					}
				?>
			</tr>


		<?php
# Do not, under ANY circumstances, remove the if-clause below.
# Doing so results in a Kohana error if no hosts are found. That
# is a VERY, VERY BAD THING, so please pretty please leave it where
# it is (yes, I'm talking to you, My).
if (empty($result)) {
	$result = array();
}
$a = 0;
foreach ($result as $row) {
	$row = (object) $row;
	$a++;
		?>
			<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
				<td class="icon bl <?php if (Command_Controller::_is_authorized_for_command(array('host_name' => $row->name)) === true) { ?>obj_properties <?php } echo strtolower(Current_status_Model::status_text($row->state, $row->has_been_checked, 'host')); ?>" id="<?php echo 'host|'.$row->name ?>" title="<?php echo Current_status_Model::status_text($row->state, $row->has_been_checked); ?>"><em><?php echo Current_status_Model::status_text($row->state, $row->has_been_checked); ?></em></td>
				<td class="item_select"><?php echo form::checkbox(array('name' => 'object_select[]'), $row->name); ?></td>
				<td>
					<div style="float: left"><?php echo html::anchor('extinfo/details/?host='.urlencode($row->name), html::specialchars($row->name), array('title' => $row->address)); ?></div>
					<div style="float: right">
					<?php
						$properties = 0;
						if ($row->acknowledged) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name), html::image($this->add_path('icons/16x16/acknowledged.png'),array('alt' => _('Acknowledged'), 'title' => _('Acknowledged'))), array('style' => 'border: 0px'));
							$properties++;
						}
						if (empty($row->notifications_enabled)) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name), html::image($this->add_path('icons/16x16/notify-disabled.png'),array('alt' => _('Notification disabled'), 'title' => _('Notification disabled'))), array('style' => 'border: 0px'));
							$properties += 2;
						}
						if (!$row->active_checks_enabled) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name), html::image($this->add_path('icons/16x16/active-checks-disabled.png'),array('alt' => _('Active checks disabled'), 'title' => _('Active checks disabled'))), array('style' => 'border: 0px'));
							$properties += 4;
						}
						if (isset($row->is_flapping) && $row->is_flapping) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name), html::image($this->add_path('icons/16x16/flapping.gif'),array('alt' => _('Flapping'), 'title' => _('Flapping'))), array('style' => 'border: 0px'));
							$properties += 32;
						}
						if ($row->scheduled_downtime_depth > 0) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name), html::image($this->add_path('icons/16x16/scheduled-downtime.png'),array('alt' => _('Scheduled downtime'), 'title' => _('Scheduled downtime'))), array('style' => 'border: 0px'));
							$properties += 8;
						}
						$num_comments = count($row->comments);
						if ( $num_comments > 0 ) {
							echo '&nbsp;'.html::anchor('extinfo/details/?host='.urlencode($row->name).'#comments',
								html::image($this->add_path('icons/16x16/add-comment.png'),
								array('alt' => sprintf(_('This host has %s comment(s) associated with it'), $num_comments),
								'title' => sprintf(_('This host has %s comment(s) associated with it'), $num_comments))), array('style' => 'border: 0px', 'class' => 'host_comment', 'data-obj_name' => $row->name));
						}
						if ($row->state == Current_status_Model::HOST_DOWN || $row->state == Current_status_Model::HOST_UNREACHABLE) {
							$properties += 16;
						}
					?><span class="obj_prop _<?php echo str_replace(".", '_', $row->name) ?>" style="display:none"><?php echo $properties ?></span>
					</div>
				</td>
				<td class="icon">
				<?php if (!empty($row->icon_image)) {
					echo html::anchor('extinfo/details/?host='.urlencode($row->name),html::image(Kohana::config('config.logos_path').$row->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->icon_image_alt, 'title' => $row->icon_image_alt)),array('style' => 'border: 0px'));
				} ?>
				</td>
				<td style="width: 105px">
					<?php
						echo html::anchor('status/service/?name='.urlencode($row->name), html::image($this->add_path('icons/16x16/service-details.gif'), array('alt' => _('View service details for this host'), 'title' => _('View service details for this host'))), array('style' => 'border: 0px')).' &nbsp;';
						if ($nacoma_link)
							// @todo: figure out how nacoma want's its links and wrap
							// $row->name in urlencode()
							echo nacoma::link('configuration/configure/?type=host&name='.urlencode($row->name), 'icons/16x16/nacoma.png', _('Configure this host')).' &nbsp;';
							echo $row->pnpgraph_present ? html::anchor('pnp/?host='.urlencode($row->name).'&srv=_HOST_', html::image($this->add_path('icons/16x16/pnp.png'), array('alt' => _('Show performance graph'), 'title' => _('Show performance graph'), 'class' => 'pnp_graph_icon')), array('style' => 'border: 0px')).' &nbsp;' : '';
						if (!empty($row->action_url)) {
							echo '<a href="'.nagstat::process_macros($row->action_url, $row).'" style="border: 0px" target="'.$action_url_target.'">';
							echo html::image($this->add_path('icons/16x16/host-actions.png'), array('alt' => _('Perform extra host actions'), 'title' => _('Perform extra host actions')));
							echo '</a> &nbsp;';
						}
						if (!empty($row->notes_url)) {
							echo '<a href="'.nagstat::process_macros($row->notes_url, $row).'" style="border: 0px" target="'.$notes_url_target.'">';
							echo html::image($this->add_path('icons/16x16/host-notes.png'), array('alt' => _('View extra host notes'), 'title' => _('View extra host notes')));
							echo '</a>';
						}
					?>
				</td>
				<td style="white-space: normal; width: 110px"><?php echo $row->last_check ? date($date_format_str,$row->last_check) : _('N/A') ?></td>
				<td style="width: 110px"><?php echo $row->last_state_change > 0 ? time::to_string($row->duration) : _('N/A') ?></td>
				<td style="white-space: normal">
					<?php
					if ($row->state == Current_status_Model::HOST_PENDING)
						echo $row->should_be_scheduled ? sprintf($pending_output, date($date_format_str, $row->next_check)) : _('Host is not scheduled to be checked...');
					else {
						$output = $row->plugin_output;
						echo htmlspecialchars(str_replace('','', $output));
					}
					?>
				</td>
			<?php	if ($show_display_name) { ?>
				<td style="white-space: normal"><?php echo $row->display_name ?></td>
			<?php 	}

					if ($show_notes) { ?>
				<td style="white-space: normal"<?php if (!empty($row->notes)) { ?>class="notescontainer"<?php } ?> title="<?php echo $row->notes ?>"><?php echo !empty($notes_chars) ? text::limit_chars($row->notes, $notes_chars, '...') : $row->notes ?></td>
			<?php 	} ?>
			</tr>
			<?php	} ?>

	</table>
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
	echo form::dropdown(array('name' => 'multi_action', 'class' => 'item_select auto', 'id' => 'multi_action_select'), $options);
?>
	<?php echo form::submit(array('id' => 'multi_object_submit', 'class' => 'item_select', 'value' => _('Submit'))); ?>
	<br /><span id="multi_object_submit_progress" class="item_select"></span>
	<?php echo form::hidden('obj_type', 'host'); ?>
	<?php echo form::close(); ?>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<br /><br />
</div>
